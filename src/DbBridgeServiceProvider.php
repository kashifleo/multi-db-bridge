<?php

namespace Kashifleo\MultiDBBridge;

use Illuminate\Support\ServiceProvider;
use Kashifleo\MultiDBBridge\Database\DbBridgeConnectionManager;
use Kashifleo\MultiDBBridge\Support\DbBridgeContext;

class DbBridgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/dbbridge.php', 'dbbridge');

        $this->app->singleton(DbBridgeContext::class, function ($app) {
            return new DbBridgeContext();
        });

        $this->app->singleton(DbBridgeConnectionManager::class, function ($app) {
            return new DbBridgeConnectionManager();
        });

        $this->app->singleton('dbbridge', function ($app) {
            return new DbBridgeManager(
                $app->make(DbBridgeConnectionManager::class),
                $app->make(DbBridgeContext::class)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/dbbridge.php' => config_path('dbbridge.php'),
            ], 'dbbridge-config');
        }

        $this->commands([
            Console\Commands\DbBridgeMakeMigrationCommand::class,
            Console\Commands\DbBridgeMigrateCommand::class,
        ]);
    }
}
