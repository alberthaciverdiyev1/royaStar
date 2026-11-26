<?php

use App\Modules\Exam\Actions\StoreExamAction;
use App\Modules\Grade\Models\Grade;
use App\Modules\Lesson\Actions\Video\StoreVideoAction;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\Video;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Models\PushSubscription;
use App\Modules\Quiz\Actions\StoreQuizAction;
use App\Modules\Student\Models\Student;
use App\Modules\Topic\Models\Topic;
use App\Modules\User\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);

    $this->admin = User::factory()->create(['type' => 'admin']);
    $this->admin->assignRole('admin');

    $this->grade = Grade::create(['name' => '9-cu sinif']);
    $this->otherGrade = Grade::create(['name' => '10-cu sinif']);

    $this->topic = Topic::create(['name' => 'Test Mövzu', 'difficulty_level' => 1]);
    $this->grade->topics()->attach($this->topic->id);

    $this->lesson = Lesson::create([
        'topic_id' => $this->topic->id,
        'name' => 'Test Dərs',
    ]);

    $this->student = notificationTestStudent($this->grade);
    $this->otherStudent = notificationTestStudent($this->otherGrade);
});

function notificationTestStudent(Grade $grade): User
{
    $user = User::factory()->create(['type' => 'student']);

    Student::create([
        'user_id' => $user->id,
        'grade_id' => $grade->id,
    ]);

    return $user;
}

it('notifies only students of the topic grade when a video is added', function () {
    app(StoreVideoAction::class)->execute([
        'lesson_id' => $this->lesson->id,
        'youtube_url' => 'https://youtu.be/aaaaaaaaaaa',
        'name' => 'Yeni video',
    ]);

    expect(Notification::count())->toBe(1);

    $notification = Notification::first();
    expect($notification->type)->toBe('lesson_video');

    $recipients = $notification->recipients()->pluck('users.id')->all();
    expect($recipients)->toBe([$this->student->id]);
});

it('does not notify again for videos that already existed on lesson update', function () {
    Video::create([
        'lesson_id' => $this->lesson->id,
        'youtube_url' => 'https://youtu.be/aaaaaaaaaaa',
        'name' => 'Köhnə video',
    ]);

    $this->actingAs($this->admin)->putJson(
        "/api/admin/topics/{$this->topic->id}/lessons/{$this->lesson->id}",
        [
            'name' => 'Test Dərs',
            'videos' => [
                ['youtube_url' => 'https://youtu.be/aaaaaaaaaaa', 'name' => 'Köhnə video'],
            ],
        ]
    )->assertStatus(200);

    expect(Notification::count())->toBe(0);

    $this->actingAs($this->admin)->putJson(
        "/api/admin/topics/{$this->topic->id}/lessons/{$this->lesson->id}",
        [
            'name' => 'Test Dərs',
            'videos' => [
                ['youtube_url' => 'https://youtu.be/aaaaaaaaaaa', 'name' => 'Köhnə video'],
                ['youtube_url' => 'https://youtu.be/bbbbbbbbbbb', 'name' => 'Təzə video'],
            ],
        ]
    )->assertStatus(200);

    expect(Notification::count())->toBe(1);
    expect(Notification::first()->data['video_id'])->not->toBeNull();
});

it('notifies the lesson topic grade when a quiz is created', function () {
    app(StoreQuizAction::class)->execute([
        'name' => 'Yeni quiz',
        'type' => 'topic_based',
        'lesson_id' => $this->lesson->id,
    ]);

    expect(Notification::count())->toBe(1);

    $notification = Notification::first();
    expect($notification->type)->toBe('quiz');
    expect($notification->recipients()->pluck('users.id')->all())->toBe([$this->student->id]);
});

it('notifies the exam grade when an exam is created', function () {
    app(StoreExamAction::class)->execute([
        'name' => 'Yeni imtahan',
        'grade_id' => $this->grade->id,
        'duration_minutes' => 60,
        'passing_score' => 50,
        'type' => 'general',
    ]);

    expect(Notification::count())->toBe(1);

    $notification = Notification::first();
    expect($notification->type)->toBe('exam');
    expect($notification->recipients()->pluck('users.id')->all())->toBe([$this->student->id]);
});

it('marks a notification as read for the current student', function () {
    app(StoreVideoAction::class)->execute([
        'lesson_id' => $this->lesson->id,
        'youtube_url' => 'https://youtu.be/aaaaaaaaaaa',
        'name' => 'Yeni video',
    ]);

    $notification = Notification::first();

    $this->actingAs($this->student)
        ->postJson("/notifications/{$notification->id}/read")
        ->assertOk()
        ->assertJson(['unread' => 0]);

    $this->assertDatabaseHas('notification_user', [
        'notification_id' => $notification->id,
        'user_id' => $this->student->id,
    ]);

    expect(
        Notification::query()
            ->join('notification_user', 'notifications.id', '=', 'notification_user.notification_id')
            ->where('notification_user.user_id', $this->student->id)
            ->whereNull('notification_user.read_at')
            ->count()
    )->toBe(0);
});

it('does not let a student read another student notification', function () {
    app(StoreVideoAction::class)->execute([
        'lesson_id' => $this->lesson->id,
        'youtube_url' => 'https://youtu.be/aaaaaaaaaaa',
        'name' => 'Yeni video',
    ]);

    $notification = Notification::first();

    $this->actingAs($this->otherStudent)
        ->postJson("/notifications/{$notification->id}/read")
        ->assertOk();

    expect(
        Notification::query()
            ->join('notification_user', 'notifications.id', '=', 'notification_user.notification_id')
            ->where('notification_user.user_id', $this->student->id)
            ->whereNull('notification_user.read_at')
            ->count()
    )->toBe(1);
});

it('renders the notification centre and stores push subscriptions', function () {
    app(StoreVideoAction::class)->execute([
        'lesson_id' => $this->lesson->id,
        'youtube_url' => 'https://youtu.be/aaaaaaaaaaa',
        'name' => 'Yeni video',
    ]);

    $this->actingAs($this->student)
        ->get('/notifications')
        ->assertOk()
        ->assertSee('Bildirişlər')
        ->assertSee('Yeni video dərs əlavə edildi')
        ->assertSee('data-notif-badge', false);

    $this->actingAs($this->student)
        ->postJson('/push/subscribe', [
            'endpoint' => 'https://push.example.com/subscription/1',
            'keys' => [
                'p256dh' => 'test-public-key',
                'auth' => 'test-auth-token',
            ],
            'content_encoding' => 'aes128gcm',
        ])
        ->assertOk();

    expect(PushSubscription::where('user_id', $this->student->id)->count())->toBe(1);
});
