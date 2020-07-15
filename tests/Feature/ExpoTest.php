<?php

namespace Sbkl\LaravelExpoPushNotifications\Tests\Feature;

use App\User;
use Sbkl\LaravelExpoPushNotifications\Facades\Expo;
use Sbkl\LaravelExpoPushNotifications\Models\Channel;
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
    public function it_can_send_a_notification()
    {
        $user = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Expo::subscribe($user, $channel, $this->token);

        $notification = [
            'title' => 'Laravel Push Notifications',
            'body' => 'This is coming from the package',
        ];

        $response = Expo::notify([$channel], $notification);

        $this->assertEquals('ok', $response[0]['status']);
    }

    /** @test */
    public function it_can_send_multiple_notifications()
    {
        $user = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        Expo::subscribe($user, $channel, $this->token);

        // Expo::subscribe($user, $channel, $token);
        $user->subscribe($channel, $this->token);

        $notification = [
            'title' => 'Laravel Push Notifications Multiple',
            'body' => 'This is coming from the package',
        ];

        $response = Expo::notify([$channel], $notification);

        $this->assertEquals('ok', $response[0]['status']);

        $this->assertEquals('ok', $response[1]['status']);

        $this->assertEquals(2, count($response));
    }
}
