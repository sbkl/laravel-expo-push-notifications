<?php

namespace Sbkl\LaravelExpoPushNotifications\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sbkl\LaravelExpoPushNotifications\Expo;

class ExpoTest extends TestCase
{
    /** @test */
    public function it_is_instantiating_expo()
    {
        $expo = Expo::databaseSetup();
        $hello = $expo->subscribe('hello', 'ExponentPushToken[1111111111111111111]');
        echo $hello;
        $this->assertTrue(true);
    }
}
