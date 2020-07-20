<?php

namespace Sbkl\LaravelExpoPushNotifications\Traits;

use Sbkl\LaravelExpoPushNotifications\Facades\Expo;
use Sbkl\LaravelExpoPushNotifications\Models\Notification;
use Sbkl\LaravelExpoPushNotifications\Models\Subscription;

trait Expoable
{
    /**
     * Get the notification entities that the users has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expoSubscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the notification entities that the users has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function expoNotifications()
    {
        return $this->belongsToMany(Notification::class, 'expo_notification_user', 'user_id', 'expo_notification_id')->withPivot('read_at');
    }

    public function subscribe($channel, $token)
    {
        return Expo::subscribe($this, $channel, $token);
    }

    public function unsubscribe($channel, $token)
    {
        return Expo::unsubscribe($channel, $token);
    }

    public function markNotificationAsRead($notificationId)
    {
        $this->expoNotifications()->updateExistingPivot($notificationId, [
            'read_at' => now()
        ]);
    }

    public function markNotificationAsUnread($notificationId)
    {
        $this->expoNotifications()->updateExistingPivot($notificationId, [
            'read_at' => null
        ]);
    }

    public function expoNotificationRead($notificationId)
    {
        return $this->expoNotifications()->where('id', $notificationId)->first()->pivot->read_at !== null;
    }

    public function expoNotificationUnread($notificationId)
    {
        return $this->expoNotifications()->where('id', $notificationId)->first()->pivot->read_at === null;
    }
}
