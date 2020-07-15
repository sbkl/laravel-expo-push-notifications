<?php

namespace Sbkl\LaravelExpoPushNotifications;

use Sbkl\LaravelExpoPushNotifications\ExpoRepository;
use Sbkl\LaravelExpoPushNotifications\Models\Channel;
use Sbkl\LaravelExpoPushNotifications\Models\Subscription;

class ExpoDatabaseDriver implements ExpoRepository
{
    public function store($subscriber, $channel, $token): bool
    {
        Subscription::create([
            'subscribers_type' => get_class($subscriber),
            'subscribers_id' => $subscriber->id,
            'channel_id' => $channel->id,
            'token' => $token,
        ]);

        return true;
    }

    public function retrieve(Channel $channel)
    {
        return $channel->subscriptions->pluck('token')->toArray();
    }

    public function forget($channel, $token = null): bool
    {
        if ($token) {
            $subscription = $channel->subscriptions()->where('token', $token)->first();
            $subscription->delete();
        }

        return true;
    }
}
