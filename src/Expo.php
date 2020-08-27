<?php

namespace Sbkl\LaravelExpoPushNotifications;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Sbkl\LaravelExpoPushNotifications\Models\Channel;
use Sbkl\LaravelExpoPushNotifications\Models\Notification;
use Sbkl\LaravelExpoPushNotifications\Exceptions\ExpoException;
use Sbkl\LaravelExpoPushNotifications\Exceptions\UnexpectedResponseException;

class Expo
{
    /**
     * The Expo Api Url that will receive the requests
     */
    protected $end_point;

    /**
     * cURL handler
     *
     * @var null|resource
     */
    private $ch = null;

    /**
     * The registrar instance that manages the tokens
     *
     * @var ExpoRegistrar
     */
    private $registrar;

    /**
     * Expo constructor.
     *
     * @param ExpoRegistrar $expoRegistrar
     */
    public function __construct(ExpoRegistrar $expoRegistrar)
    {
        $this->registrar = $expoRegistrar;
        $this->end_point = config('expo.endpoint');
    }

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

    /**
     * Subscribes a given interest to the Expo Push Notifications.
     *
     * @param $subscriber
     * @param $interest
     * @param $token
     *
     * @return Sbkl\LaravelExpoPushNotifications\Models\Subscription
     */
    public function subscribe($subscriber, $channel, $token = null)
    {
        $subscription = $this->registrar->registerInterest($subscriber, $channel, $token);

        return $subscription;
    }

    /**
     * Unsubscribes a given interest from the Expo Push Notifications.
     *
     * @param $interest
     * @param $token
     *
     * @return bool
     */
    public function unsubscribe($interest, $token = null)
    {
        return $this->registrar->removeInterest($interest, $token);
    }

    /**
     * Send a notification via the Expo Push Notifications Api.
     *
     * @param array $interests
     * @param array $data
     * @param bool $debug
     *
     * @throws ExpoException
     * @throws UnexpectedResponseException
     *
     * @return array|bool
     */
    public function notify(array $channelNames, array $notification, $debug = false)
    {
        if (count($channelNames) == 0) {
            throw new ExpoException('Channels array must not be empty.');
        }

        if (!isset($notification['title']) && !isset($notification['body'])) {
            throw ExpoException::emptyNotification();
        }

        if (isset($notification['model']) && !$notification['model'] instanceof Model) {
            throw ExpoException::wrongModelInstance();
        }

        // Create the notification
        $databaseNotification = Notification::create(array_merge(
            ['id' => Str::uuid()->toString()],
            isset($notification['model']) ? ['model_type' => get_class($notification['model'])] : [],
            isset($notification['model']) ? ['model_id' => $notification['model']->id] : [],
            isset($notification['title']) ? ['title' => $notification['title']] : [],
            isset($notification['body']) ? ['body' => $notification['body']] : [],
            isset($notification['data']) ? ['data' => json_decode($notification['data'])] : [],
        ));

        Channel::whereIn('name', $channelNames)->whereHas('subscriptions')->chunk(1, function ($channels) use ($databaseNotification, $notification, $debug) {

            $postData = [];

            // Gets the expo tokens and recipients
            [$tokens, $recipientIds] = $this->registrar->getInterests($channels);

            $databaseNotification->recipients()->attach($recipientIds);

            if (!empty($tokens)) {
                foreach ($tokens as $token) {
                    $postData[] = $notification + ['to' => $token];
                }

                $ch = $this->prepareCurl();

                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

                $response = $this->executeCurl($ch);

                // If the notification failed completely, throw an exception with the details
                if ($debug && $this->failedCompletely($response, $tokens)) {
                    throw ExpoException::failedCompletelyException($response);
                }

                return $response;
            }
        });
    }

    /**
     * Determines if the request we sent has failed completely
     *
     * @param array $response
     * @param array $recipients
     *
     * @return bool
     */
    private function failedCompletely(array $response, array $recipients)
    {
        $numberOfRecipients = count($recipients);
        $numberOfFailures = 0;

        foreach ($response as $item) {
            if ($item['status'] === 'error') {
                $numberOfFailures++;
            }
        }

        return $numberOfFailures === $numberOfRecipients;
    }

    /**
     * Sets the request url and headers
     *
     * @throws ExpoException
     *
     * @return null|resource
     */
    private function prepareCurl()
    {
        $ch = $this->getCurl();

        // Set cURL opts
        curl_setopt($ch, CURLOPT_URL, $this->end_point);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'content-type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $ch;
    }

    /**
     * Get the cURL resource
     *
     * @throws ExpoException
     *
     * @return null|resource
     */
    public function getCurl()
    {
        // Create or reuse existing cURL handle
        $this->ch = $this->ch ?? curl_init();

        // Throw exception if the cURL handle failed
        if (!$this->ch) {
            throw new ExpoException('Could not initialise cURL!');
        }

        return $this->ch;
    }

    /**
     * Executes cURL and captures the response
     *
     * @param $ch
     *
     * @throws UnexpectedResponseException
     *
     * @return array
     */
    private function executeCurl($ch)
    {
        $response = [
            'body' => curl_exec($ch),
            'status_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE)
        ];

        $responseData = json_decode($response['body'], true)['data'] ?? null;

        if (!is_array($responseData)) {
            throw new UnexpectedResponseException();
        }

        return $responseData;
    }
}
