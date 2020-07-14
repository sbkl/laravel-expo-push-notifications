<?php

namespace Sbkl\LaravelExpoPushNotifications\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $guarded = [];

    protected $table = 'expo_subscriptions';

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
