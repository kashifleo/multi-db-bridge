<?php

namespace Kashifleo\MultiDBBridge\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Kashifleo\MultiDBBridge\Database\DbBridgeConnectionManager;
use Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract;
use RuntimeException;

class DbBridgeMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbbridge:migrate 
                            {--id= : Migrate a specific tenant database by ID} 
                            {--all : Migrate all tenant databases}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations for tenant databases';

    /**
     * The migrator instance.
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;

    /**
     * The tenant connection manager instance.
     *
     * @var \Kashifleo\MultiDBBridge\Database\DbBridgeConnectionManager
     */
    protected $connectionManager;

    /**
     * Create a new command instance.
     *
     * @param  \Kashifleo\MultiDBBridge\Database\DbBridgeConnectionManager  $connectionManager
     * @return void
     */
    public function __construct(DbBridgeConnectionManager $connectionManager)
    {
        parent::__construct();

        $this->connectionManager = $connectionManager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantModelClass = config('dbbridge.tenant_model');

        if (!$tenantModelClass || !class_exists($tenantModelClass)) {
            $this->error("Tenant model not configured or does not exist.");
            return 1;
        }

        $id = $this->option('id');
        $all = $this->option('all');

        if (!$id && !$all) {
            $this->error("Please specify either --id or --all.");
            return 1;
        }

        $tenants = collect();

        if ($all) {
            $tenants = $tenantModelClass::all();
        } elseif ($id) {
            $tenant = $tenantModelClass::find($id);
            if ($tenant) {
                $tenants->push($tenant);
            } else {
                $this->error("Tenant with ID {$id} not found.");
                return 1;
            }
        }

        if ($tenants->isEmpty()) {
            $this->info("No tenants to migrate.");
            return 0;
        }

        $this->info("Starting migration for " . $tenants->count() . " tenant(s)...");

        $path = $this->getMigrationPath();
        $connectionName = config('dbbridge.tenant_connection', 'tenant');

        // Resolve Migrator
        $migrator = app('migrator');

        // Set output for migrator to display progress
        $migrator->setOutput($this->output);

        $bar = $this->output->createProgressBar($tenants->count());
        $bar->start();

        foreach ($tenants as $tenant) {
            if (!$tenant instanceof DbBridgeConnectionContract) {
                $this->error("Tenant model must implement DbBridgeConnectionContract.");
                continue;
            }

            try {
                // Configure connection for this tenant
                $this->connectionManager->configureConnection($tenant);

                $this->info("\nMigrating Tenant ID: " . $tenant->getKey() . " (DB: " . $tenant->getDatabaseName() . ")");

                // Run migrations using the Migrator service
                // We must use the tenant connection
                $migrator->setConnection($connectionName);

                if (!$migrator->repositoryExists()) {
                    $migrator->getRepository()->createRepository();
                }

                $migrator->run([$path]);

            } catch (\Exception $e) {
                $this->error("\nFailed to migrate tenant ID {$tenant->getKey()}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();

        // Clean up connection
        $this->connectionManager->removeConnection();

        $this->info("\nTenant migrations completed.");

        return 0;
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        $relativePath = config('dbbridge.tenant_migrations_path', 'database/migrations/tenants');

        return base_path($relativePath);
    }
}
