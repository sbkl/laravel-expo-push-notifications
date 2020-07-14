<?php

namespace Sbkl\LaravelExpoPushNotifications\Tests\Feature;

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
            'name' => $channelName
        ]);
        $this->assertDatabaseHas('expo_channels', [
            'name' => $channelName,
            'deactivated_at' => null,
        ]);
    }
    /** @test */
    public function it_can_subscribe_to_a_channel()
    {
        $channelName = 'Test';
        $token = 'ExponentPushToken[1111111111111111111]';
        $channel = Channel::create([
            'name' => $channelName
        ]);
        Expo::subscribe($channel, $token);
        $this->assertDatabaseHas('expo_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $token,
        ]);
    }
    /** @test */
    public function it_can_unsubscribe_from_a_channel()
    {
        $channelName = 'Test';
        $token = 'ExponentPushToken[1111111111111111111]';
        $channel = Channel::create([
            'name' => $channelName
        ]);
        Expo::subscribe($channel, $token);
        $this->assertDatabaseHas('expo_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $token,
        ]);
        Expo::unsubscribe($channel->id, $token);
        $this->assertDatabaseMissing('expo_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $token,
        ]);
    }
    /** @test */
    public function it_can_send_a_notification()
    {
        $channelName = 'Test';
        $token = 'ExponentPushToken[EMUuS2PAx9QpD1e6E9p38f]';
        $channel = Channel::create([
            'name' => $channelName
        ]);
        Expo::subscribe($channel, $token);

        $notification = [
            'title' => 'Laravel Push Notifications',
            'body' => 'This is coming from the package'
        ];

        $response = Expo::notify([$channel->id], $notification);

        $this->assertEquals('ok', $response[0]['status']);
    }
    /** @test */
    public function it_can_send_multiple_notifications()
    {
        $channelName = 'Test';
        $token = 'ExponentPushToken[EMUuS2PAx9QpD1e6E9p38f]';
        $channel = Channel::create([
            'name' => $channelName
        ]);
        Expo::subscribe($channel, $token);
        Expo::subscribe($channel, $token);

        $notification = [
            'title' => 'Laravel Push Notifications Multiple',
            'body' => 'This is coming from the package'
        ];

        $response = Expo::notify([$channel->id], $notification);

        $this->assertEquals('ok', $response[0]['status']);
        $this->assertEquals('ok', $response[1]['status']);
        $this->assertEquals(2, count($response));
    }
}
