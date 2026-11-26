<?php

namespace App\Modules\Notification\ViewComposers;

use App\Modules\Notification\Models\Notification;
use Illuminate\View\View;

class NotificationBellComposer
{
    /**
     * Shares the unread badge count and the latest notifications with the
     * global layout, which renders the header bell.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user) {
            $view->with('unreadNotificationCount', 0)
                ->with('latestNotifications', collect());

            return;
        }

        $base = Notification::query()
            ->join('notification_user', 'notifications.id', '=', 'notification_user.notification_id')
            ->where('notification_user.user_id', $user->id);

        $unread = (clone $base)->whereNull('notification_user.read_at')->count();

        $latest = $base
            ->select('notifications.*', 'notification_user.read_at')
            ->orderByDesc('notifications.created_at')
            ->orderByDesc('notifications.id')
            ->limit(6)
            ->get();

        $view->with('unreadNotificationCount', $unread)
            ->with('latestNotifications', $latest);
    }
}
