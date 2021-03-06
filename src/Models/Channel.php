<?php

namespace Sbkl\LaravelExpoPushNotifications\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = [];

    protected $table = 'expo_channels';

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'expo_subscriptions');
    }
}
