<?php

namespace Sbkl\LaravelExpoPushNotifications;

use Illuminate\Database\Eloquent\Collection;
use Sbkl\LaravelExpoPushNotifications\ExpoRepository;
use Sbkl\LaravelExpoPushNotifications\Exceptions\ExpoRegistrarException;

class ExpoRegistrar
{
    /**
     * Repository that manages the storage and retrieval
     *
     * @var ExpoRepository
     */
    private $repository;

    /**
     * ExpoRegistrar constructor.
     *
     * @param ExpoRepository $repository
     */
    public function __construct(ExpoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Registers the given token for the given interest
     *
     * @param $interest
     * @param $token
     *
     * @throws ExpoRegistrarException
     *
     * @return Sbkl\LaravelExpoPushNotifications\Models\Subscription
     */
    public function registerInterest($user, $channel, $token)
    {
        if ($token && !$this->isValidExpoPushToken($token)) {
            throw ExpoRegistrarException::invalidToken();
        }

        $subscription = $this->repository->store($user, $channel, $token);

        if (!$subscription) {
            throw ExpoRegistrarException::couldNotRegisterInterest();
        }

        return $subscription;
    }

    /**
     * Removes token of a given interest
     *
     * @param $interest
     * @param $token
     *
     * @throws ExpoRegistrarException
     *
     * @return bool
     */
    public function removeInterest($interest, $token = null)
    {
        if (!$this->repository->forget($interest, $token)) {
            throw ExpoRegistrarException::couldNotRemoveInterest();
        }

        return true;
    }

    /**
     * Gets the tokens of the interests
     *
     * @param array $interests
     *
     * @throws ExpoRegistrarException
     *
     * @return array
     */
    public function getInterests(Collection $channels): array
    {
        $tokens = [];

        $recipientIds = [];

        $channels->each(function ($channel) use (&$tokens, &$recipientIds) {

            $subscriptions = $this->repository->retrieve($channel);

            $subscriptions->each(function ($subscription) use (&$tokens, &$recipientIds) {

                $recipientIds[] = $subscription->user_id;

                if (is_string($subscription->token)) {

                    $tokens[] = $subscription->token;
                }
            });
        });

        if (empty($tokens) && empty($recipientIds)) {

            throw ExpoRegistrarException::emptyInterests();
        }

        return [collect($tokens)->unique()->toArray(), collect($recipientIds)->unique()->toArray()];
    }

    /**
     * Determines if a token is a valid Expo push token
     *
     * @param string $token
     *
     * @return bool
     */
    private function isValidExpoPushToken(string $token)
    {
        return  substr($token, 0, 18) ===  "ExponentPushToken[" && substr($token, -1) === ']';
    }
}
