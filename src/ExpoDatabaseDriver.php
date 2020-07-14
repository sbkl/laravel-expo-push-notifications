<?php

namespace Sbkl\LaravelExpoPushNotifications;

use ExponentPhpSDK\ExpoRepository;

class ExpoDatabaseDriver implements ExpoRepository
{
    public function store($key, $value): bool
    {
        return true;
    }

    public function retrieve(string $key): bool
    {
        return true;
    }

    public function forget(string $key, string $value = null): bool
    {
        return true;
    }
}
