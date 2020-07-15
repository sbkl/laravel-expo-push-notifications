<?php

namespace Sbkl\LaravelExpoPushNotifications;

interface ExpoRepository
{
    /**
     * Stores an Expo token with a given identifier
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function store($user, $channel, $token): bool;

    /**
     * Retrieve an Expo token with a given identifier
     *
     * @param string $key
     *
     * @return array|string|null
     */
    public function retrieve($channel);

    /**
     * Removes an Expo token with a given identifier
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function forget($channel, $token = null): bool;
}
