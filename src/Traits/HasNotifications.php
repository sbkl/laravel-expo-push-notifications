<?php

namespace Sbkl\LaravelExpoPushNotifications\Traits;

use Sbkl\LaravelExpoPushNotifications\Models\Notification;

trait HasNotifications
{
    /**
     * Get all of the model's notifications.
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'model');
    }
}
