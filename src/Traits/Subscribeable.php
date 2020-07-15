<?php

namespace Sbkl\LaravelExpoPushNotifications\Traits;

use Sbkl\LaravelExpoPushNotifications\Facades\Expo;
use Sbkl\LaravelExpoPushNotifications\Models\Subscription;

trait Subscribeable
{
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'subscribers');
    }
    
    public function subscribe($channel, $token)
    {
        Expo::subscribe($this, $channel, $token);
    }

    public function unsubscribe($channel, $token)
    {
        Expo::unsubscribe($channel, $token);
    }
}
