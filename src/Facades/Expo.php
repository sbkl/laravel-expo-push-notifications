<?php

namespace Sbkl\LaravelExpoPushNotifications\Facades;

use Illuminate\Support\Facades\Facade;

class Expo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'expo';
    }
}
