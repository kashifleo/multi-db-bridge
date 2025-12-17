<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Central Database Connection
    |--------------------------------------------------------------------------
    |
    | The name of the database connection to use for the central database.
    | This connection will be used to resolve tenants.
    |
    */
    'central_connection' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Prefix
    |--------------------------------------------------------------------------
    |
    | Optional prefix for tenant database names.
    |
    */
    'tenant_database_prefix' => 'tenant_',

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Connection
    |--------------------------------------------------------------------------
    |
    | The name of the database connection to use for the tenant database.
    | This connection will be dynamically configured at runtime.
    |
    */
    'tenant_connection' => 'tenant',

    /*
    |--------------------------------------------------------------------------
    | Tenant Migrations Path
    |--------------------------------------------------------------------------
    |
    | The path where tenant migrations are located.
    |
    */
    'tenant_migrations_path' => 'database/migrations/tenants',

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of the tenant model.
    | This model must implement TenantConnectionContract.
    |
    */
    'tenant_model' => null, // Set your App\Models\Tenant::class here
];
