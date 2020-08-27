<?php

namespace Sbkl\LaravelExpoPushNotifications;

use Sbkl\LaravelExpoPushNotifications\Models\Channel;
use Sbkl\LaravelExpoPushNotifications\Models\Subscription;

class ExpoDatabaseDriver implements ExpoRepository
{
    public function store($subscriber, $channel, $token)
    {
        return Subscription::create([
            'user_id' => (string) $subscriber->id,
            'channel_id' => (string) $channel->id,
            'token' => $token,
            'deactivated_at' => null,
        ]);
    }

    public function retrieve(Channel $channel)
    {
        return $channel->subscriptions()->select(['user_id', 'token'])->get();
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
