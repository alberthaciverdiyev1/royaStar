<?php

namespace App\Modules\Notification\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Models\NotificationSetting;
use App\Modules\Notification\Requests\UpdateNotificationSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    private const TYPES = ['lesson_video', 'quiz', 'exam'];

    /**
     * Notifications sent so far, with how many students received and read them.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 100);

        $query = Notification::query()
            ->withCount([
                'recipients as recipients_count',
                'recipients as read_count' => fn ($q) => $q->whereNotNull('notification_user.read_at'),
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if (in_array($request->query('type'), self::TYPES, true)) {
            $query->where('type', $request->query('type'));
        }

        $paginator = $query->paginate($perPage);

        return apiPaginated($paginator, transform: fn (Notification $notification) => [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'url' => $notification->url,
            'recipients_count' => (int) $notification->recipients_count,
            'read_count' => (int) $notification->read_count,
            'created_at' => $notification->created_at?->toIso8601String(),
        ]);
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();

        return apiResponse();
    }

    /**
     * Which actions are allowed to send notifications.
     */
    public function settings(): JsonResponse
    {
        return apiResponse(data: $this->settingsPayload(NotificationSetting::current()));
    }

    public function updateSettings(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        $settings = NotificationSetting::current();
        $settings->fill($request->validated());
        $settings->save();

        return apiResponse(
            data: $this->settingsPayload($settings),
            message: 'crud.updated',
        );
    }

    private function settingsPayload(NotificationSetting $settings): array
    {
        return [
            'notify_lesson_video' => $settings->notify_lesson_video,
            'notify_quiz' => $settings->notify_quiz,
            'notify_exam' => $settings->notify_exam,
        ];
    }
}
