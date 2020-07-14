<?php

namespace Sbkl\LaravelExpoPushNotifications;

use ExponentPhpSDK\ExpoRegistrar;
use ExponentPhpSDK\Expo as ExponentPhpSDKExpo;
use Sbkl\LaravelExpoPushNotifications\ExpoDatabaseDriver;

class Expo extends ExponentPhpSDKExpo
{
    public $expo_url = 'hello world';
    /**
     * Creates an instance of this class with the normal setup
     * It uses the ExpoFileDriver as the repository.
     *
     * @return Expo
     */
    public static function databaseSetup()
    {
        return new self(new ExpoRegistrar(new ExpoDatabaseDriver()));
    }
}
