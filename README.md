# Multi DB Manager for Laravel

DbBridge is a Laravel package for managing central and tenant databases simultaneously using explicit, secure tenant connections.

## Features

- **Central-Managed Tenancy**: Single central database storing tenant credentials.
- **Explicit Connection**: No auto-magic middleware. You control when to connect.
- **Simultaneous Access**: Query `User::on('central')` and `Order::on('tenant')` at the same time.
- **Secure**: Credentials stored in DB, not separate `.env` files.
- **Job Support**: Full support for queues and jobs with automatic tenant context preservation.

## Installation

```bash
composer require kashifleo/multi-db-bridge
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Kashifleo\MultiDBBridge\DbBridgeServiceProvider"
```

This will create `config/dbbridge.php`:

```php
return [
    'central_connection' => 'mysql', // Your main connection
    'tenant_connection' => 'tenant', // The dynamic connection name
    'tenant_model' => App\Models\Tenant::class, // Your Tenant Model
    'tenant_migrations_path' => 'database/migrations/tenants', // Path to tenant migrations
    'tenant_database_prefix' => 'tenant_', // Prefix for tenant databases
];
```

## Usage

### Tenant Model

Your App's Tenant model must implement `Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract;

class Tenant extends Model implements DbBridgeConnectionContract
{
    public function getDatabaseDriver(): string { return $this->db_driver; }
    public function getDatabaseHost(): string { return $this->db_host; }
    public function getDatabasePort(): int { return $this->db_port; }
    public function getDatabaseName(): string { return $this->db_name; }
    public function getDatabaseUsername(): string { return $this->db_username; }
    public function getDatabasePassword(): string { return $this->db_password; }
}
```

### Connecting to a Tenant

```php
use Kashifleo\MultiDBBridge\Facades\DbBridge;
use App\Models\Tenant;

$tenant = Tenant::find(1);

// Connect explicitly
DbBridge::connect($tenant);

// Check connection
if (DbBridge::isConnected()) {
    $current = DbBridge::current();
}

// Disconnect
DbBridge::disconnect();
```

### Simultaneous Database Usage

```php
// Query Central DB (default connection)
$users = \App\Models\User::on('mysql')->get(); // or default

// Query Tenant DB
$orders = \App\Models\Order::on('tenant')->get();
```

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract;

class Tenant extends Model implements DbBridgeConnectionContract
{
    // Tenant connection name defined
    protected $connection  = 'tenant';

    public function getDatabaseDriver(): string { return $this->db_driver; }
    public function getDatabaseHost(): string { return $this->db_host; }
    public function getDatabasePort(): int { return $this->db_port; }
    public function getDatabaseName(): string { return $this->db_name; }
    public function getDatabaseUsername(): string { return $this->db_username; }
    public function getDatabasePassword(): string { return $this->db_password; }
}
```

```php
// Query Tenant DB
$orders = \App\Models\Order::find(1);
```

### Database Management

You can programmatically manage tenant databases using the `DbBridge` facade. This is useful for onboarding flows.

```php
use Kashifleo\MultiDBBridge\Facades\DbBridge;

// Create the tenant's database
// This uses the 'tenant_database_prefix' from config if you use it in your model
DbBridge::createDatabase($tenant);

// Generate a standard database name
// Pattern: {prefix}{id}_{slug}_{year}
$dbName = DbBridge::generateDatabaseName($tenant);
$tenant->update(['db_database' => $dbName]);

// Run migrations for the tenant
DbBridge::migrate($tenant);

// Drop the tenant's database (careful!)
DbBridge::dropDatabase($tenant);
```

### Queue Support

To use tenant connections inside Queued Jobs, you should pass the Tenant model to the Job's constructor and explicitly connect within the `handle` method. This ensures clarity and control.

```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kashifleo\MultiDBBridge\Facades\DbBridge;
use App\Models\Tenant as TenantModel;

class ProcessTenantOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    public function __construct(TenantModel $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {
        // Explicitly connect to the tenant
        DbBridge::connect($this->tenant);

        // Perform actions on the tenant database
        // ...

        // Optional: Disconnect if needed, though the worker will likely reset for next job
        // DbBridge::disconnect();
    }
}
```

## Tenant Database Migrations

This package provides a robust way to manage tenant database migrations separate from your central migrations.

### Configuration

Ensure your `config/dbbridge.php` has the migrations path configured:

```php
'tenant_migrations_path' => 'database/migrations/tenants',
```

### Creating Tenant Migrations

Use the `dbbridge:make-migration` command to create a migration file specifically for tenant databases. These files will be placed in the configured tenant migrations path.

```bash
# Create a new table
php artisan dbbridge:make-migration create_orders_table --create=orders

# Add a column to an existing table
php artisan dbbridge:make-migration add_status_to_orders_table --table=orders
```

### Running Tenant Migrations

Use the `dbbridge:migrate` command to run migrations on tenant databases.

**Migrate a Single Tenant:**

```bash
php artisan dbbridge:migrate --id=1
```

**Migrate All Tenants:**

```bash
php artisan dbbridge:migrate --all
```

The command dynamically connects to each tenant's database using the credentials stored in your central database and runs the migrations found in the `tenant_migrations_path`.

## License

MIT
