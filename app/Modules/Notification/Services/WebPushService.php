<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /**
     * Send a push payload to every browser subscription of the given users.
     * Failures never bubble up: a broken push service must not break the
     * admin action that created the notification.
     */
    public function sendToUsers(array $userIds, array $payload): void
    {
        $publicKey = config('webpush.public_key');
        $privateKey = config('webpush.private_key');

        if (! $publicKey || ! $privateKey || empty($userIds)) {
            return;
        }

        $subscriptions = PushSubscription::query()
            ->whereIn('user_id', $userIds)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $message = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('webpush.subject'),
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ]);

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                        'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                    ]),
                    $message
                );
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    continue;
                }

                $status = $report->getResponse()?->getStatusCode();

                // 404 / 410 mean the browser subscription is gone for good.
                if (in_array($status, [404, 410], true)) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                } else {
                    Log::warning('Web push delivery failed', [
                        'endpoint' => $report->getEndpoint(),
                        'status' => $status,
                        'reason' => $report->getReason(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Web push send error: '.$e->getMessage());
        }
    }
}
