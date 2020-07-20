<?php

namespace Sbkl\LaravelExpoPushNotifications\Tests\Unit;

use Sbkl\LaravelExpoPushNotifications\Models\User;
use Sbkl\LaravelExpoPushNotifications\Models\Channel;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Sbkl\LaravelExpoPushNotifications\Tests\LaravelTestCase;

class ChannelTest extends LaravelTestCase
{
    use ArraySubsetAsserts;

    /** @test */
    public function a_user_can_get_their_subscriptions()
    {
        $user1 = factory(User::class)->create();

        $user2 = factory(User::class)->create();

        $user3 = factory(User::class)->create();

        $channel = Channel::create([
            'name' => 'Channel1',
        ]);

        $user1->subscribe($channel, $this->token);

        $user2->subscribe($channel, $this->token);

        $user3->subscribe($channel, $this->token);

        $this->assertArraySubset($user1->toArray(), $channel->subscribers[0]->toArray());

        $this->assertArraySubset($user2->toArray(), $channel->subscribers[1]->toArray());

        $this->assertArraySubset($user3->toArray(), $channel->subscribers[2]->toArray());
    }
}
