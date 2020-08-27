<?php

namespace Sbkl\LaravelExpoPushNotifications\Tests\Unit;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Sbkl\LaravelExpoPushNotifications\Facades\Expo;
use Sbkl\LaravelExpoPushNotifications\Models\Channel;
use Sbkl\LaravelExpoPushNotifications\Models\Notification;
use Sbkl\LaravelExpoPushNotifications\Models\User;
use Sbkl\LaravelExpoPushNotifications\Tests\LaravelTestCase;

class UserTest extends LaravelTestCase
{
    use ArraySubsetAsserts;

    /** @test */
    public function a_user_can_subscribe_to_a_channel()
    {
        $user = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        $user->subscribe($channel, $this->token);

        $this->assertDatabaseHas('expo_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);
    }

    /** @test */
    public function a_user_can_unsubscribe_to_a_channel()
    {
        $user = factory(User::class)->create();

        $channelName = 'Test';

        $channel = Channel::create([
            'name' => $channelName,
        ]);

        $user->subscribe($channel, $this->token);

        $this->assertDatabaseHas('expo_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);

        $user->unsubscribe($channel, $this->token);

        $this->assertDatabaseMissing('expo_subscriptions', [
            'channel_id' => $channel->id,
            'token' => $this->token,
        ]);
    }

    /** @test */
    public function a_user_can_get_their_subscriptions()
    {
        $user = factory(User::class)->create();

        $channel1 = Channel::create([
            'name' => 'Channel1',
        ]);

        $channel2 = Channel::create([
            'name' => 'Channel2',
        ]);

        $subscription1 = $user->subscribe($channel1, $this->token);

        $subscription2 = $user->subscribe($channel1, 'ExponentPushToken[123456789]');

        $subscription3 = $user->subscribe($channel2, $this->token);

        $this->assertEquals(get_class($subscription1), get_class($user->expoSubscriptions[0]));

        $this->assertEquals(get_class($subscription2), get_class($user->expoSubscriptions[1]));

        $this->assertEquals(get_class($subscription3), get_class($user->expoSubscriptions[2]));

        $this->assertEquals($subscription1->toArray(), $user->expoSubscriptions[0]->toArray());

        $this->assertEquals($subscription2->toArray(), $user->expoSubscriptions[1]->toArray());

        $this->assertEquals($subscription3->toArray(), $user->expoSubscriptions[2]->toArray());
    }

    /** @test */
    public function a_user_can_get_their_notifications()
    {
        $user = factory(User::class)->create();

        $channel1 = Channel::create([
            'name' => 'Channel1',
        ]);

        $channel2 = Channel::create([
            'name' => 'Channel2',
        ]);

        $user->subscribe($channel1, $this->token);

        $user->subscribe($channel1, 'ExponentPushToken[123456789]');

        $user->subscribe($channel2, $this->token);

        $notification1 = [
            'title' => 'User test notifications 1',
            'body' => 'This is coming from the package 1',
            'data' => json_encode([
                'someData' => 'goes here',
            ]),
        ];

        Expo::notify(['Channel1', 'Channel2'], $notification1);

        $notification2 = [
            'title' => 'User test notifications 2',
            'body' => 'This is coming from the package 2',
            'data' => json_encode([
                'someData' => 'goes here',
            ]),
        ];

        Expo::notify(['Channel1', 'Channel2'], $notification2);

        $notifications = Notification::all();

        $user_notifications = $user->expoNotifications;

        $this->assertEquals($notifications->count(), $user_notifications->count());

        $this->assertEquals(get_class($notifications[0]), get_class($user_notifications[0]));

        $this->assertEquals(get_class($notifications[1]), get_class($user_notifications[1]));

        $this->assertArraySubset($notifications[0]->toArray(), $user_notifications[0]->toArray());

        $this->assertArraySubset($notifications[1]->toArray(), $user_notifications[1]->toArray());
    }

    /** @test */
    public function it_can_read_a_notification()
    {
        $user = factory(User::class)->create();

        $user2 = factory(User::class)->create();

        $channel = Channel::create([
            'name' => 'Channel1',
        ]);

        $user->subscribe($channel, $this->token);

        $user2->subscribe($channel, 'ExponentPushToken[123456789]');

        $notification1 = [
            'title' => 'User test notifications 1',
            'body' => 'This is coming from the package 1',
            'data' => json_encode([
                'someData' => 'goes here',
            ]),
        ];

        $notification2 = [
            'title' => 'User test notifications 1',
            'body' => 'This is coming from the package 1',
            'data' => json_encode([
                'someData' => 'goes here',
            ]),
        ];

        Expo::notify(['Channel1'], $notification1);

        Expo::notify(['Channel1'], $notification2);

        $databaseNotification = Notification::first();

        $this->assertNull($user->expoNotifications()->first()->pivot->read_at);

        $this->assertFalse($user->expoNotificationRead($databaseNotification->id));

        $this->assertTrue($user->expoNotificationUnread($databaseNotification->id));

        $this->assertFalse($databaseNotification->read($user->id));

        $this->assertFalse($databaseNotification->read($user->id));

        $this->assertTrue($databaseNotification->unread($user->id));

        $user->markNotificationAsRead($databaseNotification->id);

        $this->assertNotNull($user->expoNotifications()->first()->pivot->read_at);

        $this->assertTrue($user->expoNotificationRead($databaseNotification->id));

        $this->assertFalse($user->expoNotificationUnread($databaseNotification->id));

        $this->assertTrue($databaseNotification->read($user->id));

        $this->assertFalse($databaseNotification->unread($user->id));

        $user->markNotificationAsUnread($databaseNotification->id);

        $this->assertNull($user->expoNotifications()->first()->pivot->read_at);

        $databaseNotification->markAsRead($user->id);

        $this->assertNotNull($user->expoNotifications()->first()->pivot->read_at);

        $databaseNotification->markAsUnread($user->id);

        $this->assertNull($user->expoNotifications()->first()->pivot->read_at);
    }
}
