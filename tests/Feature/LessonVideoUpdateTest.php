<?php

use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\Video;
use App\Modules\Topic\Models\Topic;
use App\Modules\User\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);

    $this->admin = User::factory()->create(['type' => 'admin']);
    $this->admin->assignRole('admin');

    $this->topic = Topic::create(['name' => 'Test Mövzu', 'difficulty_level' => 1]);

    $this->lesson = Lesson::create([
        'topic_id' => $this->topic->id,
        'name' => 'Test Dərs',
    ]);

    Video::create(['lesson_id' => $this->lesson->id, 'youtube_url' => 'https://youtu.be/aaa', 'name' => 'Video 1']);
    Video::create(['lesson_id' => $this->lesson->id, 'youtube_url' => 'https://youtu.be/bbb', 'name' => 'Video 2']);
});

it('deletes all videos when an empty videos array is sent', function () {
    expect($this->lesson->videos()->count())->toBe(2);

    $response = $this->actingAs($this->admin)->putJson(
        "/api/admin/topics/{$this->topic->id}/lessons/{$this->lesson->id}",
        ['name' => 'Test Dərs', 'videos' => []]
    );

    $response->assertStatus(200);
    expect($this->lesson->videos()->count())->toBe(0);
});

it('replaces videos when a partial videos array is sent', function () {
    $response = $this->actingAs($this->admin)->putJson(
        "/api/admin/topics/{$this->topic->id}/lessons/{$this->lesson->id}",
        ['name' => 'Test Dərs', 'videos' => [['youtube_url' => 'https://youtu.be/ccc', 'name' => 'Yeni']]]
    );

    $response->assertStatus(200);
    expect($this->lesson->videos()->count())->toBe(1);
    expect($this->lesson->videos()->first()->youtube_url)->toBe('https://youtu.be/ccc');
});

it('keeps videos untouched when the videos key is not sent', function () {
    $response = $this->actingAs($this->admin)->putJson(
        "/api/admin/topics/{$this->topic->id}/lessons/{$this->lesson->id}",
        ['name' => 'Yeni Ad']
    );

    $response->assertStatus(200);
    expect($this->lesson->videos()->count())->toBe(2);
    expect($this->lesson->fresh()->name)->toBe('Yeni Ad');
});
