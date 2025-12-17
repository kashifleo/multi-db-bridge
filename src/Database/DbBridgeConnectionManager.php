<?php

namespace Kashifleo\MultiDBBridge\Database;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract;

class DbBridgeConnectionManager
{
    /**
     * Configure a specific tenant connection.
     *
     * @param DbBridgeConnectionContract $tenant
     * @return void
     */
    public function configureConnection(DbBridgeConnectionContract $tenant): void
    {
        $connectionName = config('dbbridge.tenant_connection', 'tenant');

        // Create the connection configuration
        $config = [
            'driver' => $tenant->getDatabaseDriver(),
            'host' => $tenant->getDatabaseHost(),
            'port' => $tenant->getDatabasePort(),
            'database' => $tenant->getDatabaseName(),
            'username' => $tenant->getDatabaseUsername(),
            'password' => $tenant->getDatabasePassword(),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        // Set the configuration for the tenant connection
        Config::set("database.connections.{$connectionName}", $config);

        // Purge and reconnect
        $this->purgeAndReconnect($connectionName);
    }

    /**
     * Remove the tenant connection.
     *
     * @return void
     */
    public function removeConnection(): void
    {
        $connectionName = config('dbbridge.tenant_connection', 'tenant');

        // Purge existing connection
        DB::purge($connectionName);

        // Remove configuration
        Config::set("database.connections.{$connectionName}", null);
    }

    /**
     * Purge and reconnect the database connection.
     *
     * @param string $connectionName
     * @return void
     */
    private function purgeAndReconnect(string $connectionName): void
    {
        DB::purge($connectionName);
        DB::reconnect($connectionName);
    }
}
