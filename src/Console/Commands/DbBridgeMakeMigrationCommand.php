<?php

namespace Kashifleo\MultiDBBridge\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Str;
use Illuminate\Support\Composer;

class DbBridgeMakeMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbbridge:make-migration 
                            {name : The name of the migration}
                            {--create= : The table to be created}
                            {--table= : The table to migrate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file for tenant database';

    /**
     * The migration creator instance.
     *
     * @var \Illuminate\Database\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Resolve dependencies
        $creator = app('migration.creator');
        $composer = app('composer');

        // Get the name of the migration
        $name = Str::snake(trim($this->argument('name')));

        // Get the path for tenant migrations
        $path = $this->getMigrationPath();

        // Get options
        $table = $this->option('table');
        $create = $this->option('create');

        // If no table is provided but create is, set table to create
        if (!$table && is_string($create)) {
            $table = $create;
        }

        // Create directory if it doesn't exist
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // Create the migration
        $file = $creator->create($name, $path, $table, $create);

        $this->info("Created Migration: " . basename($file));

        $composer->dumpAutoloads();
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
