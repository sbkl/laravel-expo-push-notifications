<?php

namespace Sbkl\LaravelExpoPushNotifications\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use Sbkl\LaravelExpoPushNotifications\ExpoServiceProvider;
use Sbkl\LaravelExpoPushNotifications\Facades\Expo;

class LaravelTestCase extends TestCase
{
    public $token;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        $this->artisan('migrate');

        $this->withFactories(__DIR__.'/../database/factories');

        $this->token = 'ExponentPushToken[BFpQPZNYMgBPvWelPNGIbG]';
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
        include_once __DIR__.'/../database/migrations/create_expo_channels_table.php.stub';
        include_once __DIR__.'/../database/migrations/create_expo_subscriptions_table.php.stub';
        include_once __DIR__.'/../database/migrations/create_expo_notifications_table.php.stub';
        (new \CreateExpoChannelsTable)->up();
        (new \CreateExpoSubscriptionsTable)->up();
        (new \CreateExpoNotificationsTable)->up();
    }
}
