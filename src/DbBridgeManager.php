<?php

namespace Kashifleo\MultiDBBridge;

use Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract;
use Kashifleo\MultiDBBridge\Database\DbBridgeConnectionManager;
use Kashifleo\MultiDBBridge\Support\DbBridgeContext;

class DbBridgeManager
{
    protected DbBridgeConnectionManager $connectionManager;
    protected DbBridgeContext $context;

    public function __construct(
        DbBridgeConnectionManager $connectionManager,
        DbBridgeContext $context
    ) {
        $this->connectionManager = $connectionManager;
        $this->context = $context;
    }

    /**
     * Connect to a tenant.
     *
     * @param DbBridgeConnectionContract $tenant
     * @return void
     */
    public function connect(DbBridgeConnectionContract $tenant): void
    {
        $this->connectionManager->configureConnection($tenant);
        $this->context->set($tenant);
    }

    /**
     * Disconnect from the current tenant.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->connectionManager->removeConnection();
        $this->context->clear();
    }

    /**
     * Get the current tenant.
     *
     * @return DbBridgeConnectionContract|null
     */
    public function current(): ?DbBridgeConnectionContract
    {
        return $this->context->get();
    }

    /**
     * Check if a tenant is connected.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->context->has();
    }
    /**
     * Create the database for the given tenant.
     *
     * @param DbBridgeConnectionContract $tenant
     * @return bool
     */
    public function createDatabase(DbBridgeConnectionContract $tenant): bool
    {
        $driver = $tenant->getDatabaseDriver();
        $databaseName = $tenant->getDatabaseName();
        $centralConnection = config('dbbridge.central_connection', 'mysql');

        switch ($driver) {
            case 'sqlite':
                return touch($databaseName);

            case 'mysql':
            case 'mariadb':
                $charset = config("database.connections.{$driver}.charset", 'utf8mb4');
                $collation = config("database.connections.{$driver}.collation", 'utf8mb4_unicode_ci');

                return \Illuminate\Support\Facades\DB::connection($centralConnection)->statement(
                    "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET $charset COLLATE $collation"
                );

            case 'pgsql':
                $charset = config("database.connections.pgsql.charset", 'utf8');
                return \Illuminate\Support\Facades\DB::connection($centralConnection)->statement(
                    "CREATE DATABASE \"{$databaseName}\" WITH ENCODING '{$charset}'"
                );

            case 'sqlsrv':
                return \Illuminate\Support\Facades\DB::connection($centralConnection)->statement(
                    "CREATE DATABASE [{$databaseName}]"
                );

            default:
                throw new \Exception("Unsupported database driver: {$driver}");
        }
    }

    /**
     * Drop the database for the given tenant.
     *
     * @param DbBridgeConnectionContract $tenant
     * @return bool
     */
    public function dropDatabase(DbBridgeConnectionContract $tenant): bool
    {
        $databaseName = $tenant->getDatabaseName();
        $centralConnection = config('dbbridge.central_connection', 'mysql');

        return \Illuminate\Support\Facades\DB::connection($centralConnection)->statement(
            "DROP DATABASE IF EXISTS `{$databaseName}`"
        );
    }

    /**
     * Run migrations for the given tenant.
     *
     * @param DbBridgeConnectionContract $tenant
     * @return int
     */
    public function migrate(DbBridgeConnectionContract $tenant): int
    {
        return \Illuminate\Support\Facades\Artisan::call('dbbridge:migrate', [
            '--id' => $tenant->getKey(),
        ]);
    }

    /**
     * Generate a database name for the tenant based on a standard pattern.
     * Pattern: {prefix}{id}_{slug}_{year}
     *
     * @param object $tenant The tenant model (must have id and name properties)
     * @return string
     */
    public function generateDatabaseName(object $tenant): string
    {
        $prefix = config('dbbridge.tenant_database_prefix', 'tenant_');

        $name = $tenant->name ?? 'tenant';
        $normalizedName = strlen($name) > 20 ? substr($name, 0, 20) : $name;
        $slug = \Illuminate\Support\Str::slug($normalizedName, '_');
        $year = now()->year;

        return "{$prefix}{$tenant->id}_{$slug}_{$year}";
    }
}
