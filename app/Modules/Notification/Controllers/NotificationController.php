<?php

namespace App\Modules\Notification\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $filter = $request->query('filter') === 'unread' ? 'unread' : 'all';

        $query = $this->userNotificationsQuery($userId);

        if ($filter === 'unread') {
            $query->whereNull('notification_user.read_at');
        }

        return view('pages.notifications', [
            'notifications' => $query->paginate(20)->withQueryString(),
            'filter' => $filter,
            'unreadCount' => $this->unreadCount($userId),
        ]);
    }

    /**
     * Latest notifications for the header dropdown, refreshed without a reload.
     */
    public function feed(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $items = $this->userNotificationsQuery($userId)
            ->limit(8)
            ->get()
            ->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'body' => $notification->body,
                'url' => $notification->url,
                'read' => $notification->read_at !== null,
                'time' => $notification->created_at?->diffForHumans(),
                'time_iso' => $notification->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'unread' => $this->unreadCount($userId),
            'items' => $items,
        ]);
    }

    public function read(Request $request, Notification $notification)
    {
        $userId = $request->user()->id;

        DB::table('notification_user')
            ->where('notification_id', $notification->id)
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['unread' => $this->unreadCount($userId)]);
        }

        return back();
    }

    public function readAll(Request $request)
    {
        $userId = $request->user()->id;

        DB::table('notification_user')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['unread' => 0]);
        }

        return back();
    }

    /**
     * Store the browser's push subscription for the logged-in student.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|string|max:2048',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'content_encoding' => 'nullable|string|max:20',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id' => $request->user()->id,
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['content_encoding'] ?? 'aes128gcm',
            ]
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|string|max:2048',
        ]);

        PushSubscription::where('endpoint', $data['endpoint'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['success' => true]);
    }

    private function userNotificationsQuery(int $userId)
    {
        return Notification::query()
            ->join('notification_user', 'notifications.id', '=', 'notification_user.notification_id')
            ->where('notification_user.user_id', $userId)
            ->select('notifications.*', 'notification_user.read_at')
            ->orderByDesc('notifications.created_at')
            ->orderByDesc('notifications.id');
    }

    private function unreadCount(int $userId): int
    {
        return Notification::query()
            ->join('notification_user', 'notifications.id', '=', 'notification_user.notification_id')
            ->where('notification_user.user_id', $userId)
            ->whereNull('notification_user.read_at')
            ->count();
    }
}
