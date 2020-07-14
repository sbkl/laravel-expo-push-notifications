<?php

namespace Sbkl\LaravelExpoPushNotifications\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Sbkl\LaravelExpoPushNotifications\Facades\Expo;
use Sbkl\LaravelExpoPushNotifications\ExpoServiceProvider;

class LaravelTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->withFactories(__DIR__ . '/../database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            ExpoServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Expo' => Expo::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        include_once __DIR__ . '/../database/migrations/create_expo_channels_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_expo_subscriptions_table.php.stub';
        (new \CreateExpoChannelsTable)->up();
        (new \CreateExpoSubscriptionsTable)->up();
    }
}
