<?php

namespace Sbkl\LaravelExpoPushNotifications;

use ExponentPhpSDK\ExpoRepository;
use Sbkl\LaravelExpoPushNotifications\Models\Channel;
use Sbkl\LaravelExpoPushNotifications\Models\Subscription;

class ExpoDatabaseDriver implements ExpoRepository
{
    public function store($channel, $token): bool
    {
        Subscription::create([
            'channel_id' => $channel->id,
            'token' => $token,
        ]);
        return true;
    }

    public function retrieve($channelId)
    {
        $channel = Channel::find($channelId);
        return $channel->subscriptions()->pluck('token')->toArray();
    }

    public function forget($channelId, $token = null): bool
    {
        $channel = Channel::find($channelId);
        if ($token) {
            $subscription = $channel->subscriptions()->where('token', $token)->first();
            $subscription->delete();
        }
        return true;
    }
}
