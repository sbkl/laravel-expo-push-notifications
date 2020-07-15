<?php

namespace Sbkl\LaravelExpoPushNotifications;

use Illuminate\Support\ServiceProvider;

class ExpoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->publishes([
                __DIR__ . '/../config/expo.php' => config_path('expo.php'),
            ], 'config');

            if (!class_exists('CreateExpoChannelsTable') && !class_exists('CreateExpoSubscriptionsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_expo_channels_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_expo_channels_table.php'),
                    __DIR__ . '/../database/migrations/create_expo_subscriptions_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_expo_subscriptions_table.php')
                ], 'migrations');
            }
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/expo.php',
            'expo'
        );
        $this->app->bind('expo', function () {
            return Expo::databaseSetup();
        });
    }
}
