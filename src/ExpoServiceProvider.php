<?php

namespace Sbkl\LaravelExpoPushNotifications;

use Illuminate\Support\ServiceProvider;

class ExpoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            if (!class_exists('CreateExpoChannelsTable')) {
                $this->publishes([
                    __DIR__ . '../database/migrations/create_expo_channels_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . 'create_expo_channels_table.php')
                ], 'migrations');
            }
            if (!class_exists('CreateExpoSubscriptionsTable')) {
                $this->publishes([
                    __DIR__ . '../database/migrations/create_expo_subscriptions_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . 'create_expo_channels_table.php')
                ], 'migrations');
            }
        }
    }

    public function register()
    {
        $this->app->bind('expo', function () {
            return Expo::databaseSetup();
        });
    }
}
