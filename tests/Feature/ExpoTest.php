<?php

namespace Sbkl\LaravelExpoPushNotifications\Tests\Feature;

use Sbkl\LaravelExpoPushNotifications\Exceptions\ExpoException;
use Sbkl\LaravelExpoPushNotifications\Models\User;
use Sbkl\LaravelExpoPushNotifications\Facades\Expo;
use Sbkl\LaravelExpoPushNotifications\Models\Channel;
use Sbkl\LaravelExpoPushNotifications\Models\Notification;
use Sbkl\LaravelExpoPushNotifications\Tests\LaravelTestCase;

class ExpoTest extends LaravelTestCase
{

    /** @test */
    public function it_can_create_a_channel()
    {
        $channelName = 'Test';

        Channel::create([
            'name' => $channelName,
        ]);

        $this->assertDatabaseHas('expo_channels', [
            'name' => $channelName,
            'deactivated_at' => null,
        ]);
    }

    /** @test */
    public function it_can_subscribe_to_a_channel()
    {
        $user = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Expo::subscribe($user, $channel, $this->token);

        $this->assertDatabaseHas('expo_subscriptions', [
            'user_id' => $user->id,
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);
    }

    /** @test */
    public function it_can_unsubscribe_from_a_channel()
    {
        $user = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Expo::subscribe($user, $channel, $this->token);

        $this->assertDatabaseHas('expo_subscriptions', [
            'user_id' => $user->id,
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);

        Expo::unsubscribe($channel, $this->token);

        $this->assertDatabaseMissing('expo_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);
    }
    /** @test */
    public function a_notification_must_have_a_title_or_a_body()
    {
        $user = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Expo::subscribe($user, $channel, 'ExponentPushToken[1234567890000]');

        $channels = Channel::whereIn('name', [$channelName])->whereHas('subscriptions')->get();

        $notification = [];

        try {
            Expo::notify($channels, $notification);
        } catch (ExpoException $error) {
            $this->assertEquals('Sbkl\LaravelExpoPushNotifications\Exceptions\ExpoException', get_class($error));
        }
    }
    /** @test */
    public function it_can_create_recipients()
    {
        $user1 = factory(User::class)->create();

        $user2 = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Expo::subscribe($user1, $channel, $this->token);

        Expo::subscribe($user2, $channel, $this->token);

        $channels = Channel::whereIn('name', [$channelName])->whereHas('subscriptions')->get();

        $notification = [
            'title' => 'Laravel Push Notifications',
            'body' => 'This is coming from the package',
            'data' => json_encode([
                'someData' => 'goes here'
            ]),
        ];

        $response = Expo::notify($channels, $notification);

        $this->assertDatabaseHas('expo_notifications', [
            'type' => null,
            'title' => $notification['title'],
            'body' => $notification['body'],
            'data' => '{"someData":"goes here"}',
        ]);

        $databaseNotification = Notification::first();

        $this->assertDatabaseHas('expo_notification_user', [
            'expo_notification_id' => $databaseNotification->id,
            'user_id' => (string) $user1->id,
            'read_at' => null
        ]);

        $this->assertDatabaseHas('expo_notification_user', [
            'expo_notification_id' => $databaseNotification->id,
            'user_id' => (string) $user1->id,
            'read_at' => null
        ]);

        $this->assertEquals('ok', $response[0]['status']);

        // $this->assertEquals('DeviceNotRegistered', $response[0]['details']['error']);
    }

    /** @test */
    public function it_can_send_a_notification()
    {
        $user = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Expo::subscribe($user, $channel, $this->token);

        $channels = Channel::whereIn('name', [$channelName])->whereHas('subscriptions')->get();

        $notification = [
            'title' => 'Laravel Push Notifications',
            'body' => 'This is coming from the package',
        ];

        $response = Expo::notify($channels, $notification);

        $this->assertEquals('ok', $response[0]['status']);
    }

    /** @test */
    public function it_can_send_multiple_notifications()
    {
        $user1 = factory(User::class)->create();

        $user2 = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Expo::subscribe($user1, $channel, $this->token);

        // Expo::subscribe($user, $channel, $token);
        $user2->subscribe($channel, 'ExponentPushToken[123456789]');

        $channels = Channel::whereIn('name', [$channelName])->whereHas('subscriptions')->get();

        $notification = [
            'title' => 'Laravel Push Notifications Multiple',
            'body' => 'This is coming from the package',
        ];

        $response = Expo::notify($channels, $notification);

        $this->assertEquals('ok', $response[0]['status']);

        $this->assertEquals('error', $response[1]['status']);

        $this->assertEquals(2, count($response));
    }
}
