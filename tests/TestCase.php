<?php

namespace Kashifleo\MultiDBBridge\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Kashifleo\MultiDBBridge\DbBridgeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            DbBridgeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup a dummy central connection (using same sqlite for simplicity in tests or different)
        $app['config']->set('database.connections.central_db', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('dbbridge.central_connection', 'central_db');
    }

    protected function createTables()
    {
        // Create tenants table in central DB
        Schema::connection('central_db')->create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('db_driver');
            $table->string('db_host')->nullable();
            $table->string('db_port')->nullable();
            $table->string('db_name');
            $table->string('db_username')->nullable();
            $table->string('db_password')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }
}
