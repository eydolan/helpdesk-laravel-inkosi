<?php

namespace App\Support\Notifications;

use Illuminate\Support\Facades\Cache;

trait Debounce
{
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $via = [];
        foreach ($this->viaDebounce($notifiable) as $channel => $implements) {
            if ($implements) {
                $wait = 0;
                $waits = $this->viaDebounceWait($notifiable);
                if (array_key_exists($channel, $waits)) {
                    $wait = $waits[$channel];
                }

                $lockKey = $this->getDebounceCacheKey($notifiable, $channel);
                
                // Check if a notification was recently sent for THIS specific notification
                // (same user, same comment, same channel)
                $recentNotification = Cache::get($lockKey);
                
                if (!$recentNotification) {
                    // No recent notification - send it and mark it
                    Cache::put($lockKey, true, $wait);
                    $via[] = $channel;
                    \Log::debug('Sending notification (no debounce)', [
                        'lock_key' => $lockKey,
                        'channel' => $channel,
                    ]);
                } else {
                    // Recent notification exists (within debounce window)
                    // This means the SAME notification was already sent recently
                    // Skip to prevent duplicate notifications for the same comment
                    \Log::debug('Skipping duplicate notification due to debounce', [
                        'lock_key' => $lockKey,
                        'channel' => $channel,
                        'wait_seconds' => $wait,
                    ]);
                    // Don't add to $via - this prevents duplicate notifications
                    // NOTE: This only blocks if it's the EXACT same notification (same comment ID)
                }
            } else {
                $via[] = $channel;
            }
        }

        return $via;
    }
}
