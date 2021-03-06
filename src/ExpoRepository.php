<?php

namespace Sbkl\LaravelExpoPushNotifications;

use Sbkl\LaravelExpoPushNotifications\Models\Channel;

interface ExpoRepository
{
    /**
     * Stores an Expo token with a given identifier.
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function store($user, $channel, $token);

    /**
     * Retrieve an Expo token with a given identifier.
     *
     * @param string $key
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function retrieve(Channel $channel);

    /**
     * Removes an Expo token with a given identifier.
     *
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function forget($channel, $token = null): bool;
}
