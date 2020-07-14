<?php

namespace Sbkl\LaravelExpoPushNotifications;

use ExponentPhpSDK\Expo as ExponentPhpSDKExpo;
use ExponentPhpSDK\ExpoRegistrar;

class Expo extends ExponentPhpSDKExpo
{
    /**
     * Creates an instance of this class with the database setup
     * It uses the ExpoDatabaseDriver as the repository.
     *
     * @return Expo
     */
    public static function databaseSetup()
    {
        return new self(new ExpoRegistrar(new ExpoDatabaseDriver()));
    }
}
