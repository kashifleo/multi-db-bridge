<?php

namespace Kashifleo\MultiDBBridge\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract;
use Kashifleo\MultiDBBridge\Facades\DbBridge;
use Kashifleo\MultiDBBridge\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DbBridgeConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTables();
    }

    #[Test]
    public function it_can_configure_tenant_connection()
    {
        $tenant = new DbBridgeTestModel([
            'id' => 1,
            'db_driver' => 'sqlite',
            'db_name' => ':memory:', // Use memory for tenant DB too
        ]);

        DbBridge::connect($tenant);

        $this->assertTrue(DbBridge::isConnected());
        $this->assertEquals($tenant, DbBridge::current());

        $config = Config::get('database.connections.tenant');
        $this->assertEquals('sqlite', $config['driver']);
        $this->assertEquals(':memory:', $config['database']);
    }

    #[Test]
    public function it_can_disconnect_tenant()
    {
        $tenant = new DbBridgeTestModel([
            'id' => 1,
            'db_driver' => 'sqlite',
            'db_name' => ':memory:',
        ]);

        DbBridge::connect($tenant);
        $this->assertTrue(DbBridge::isConnected());

        DbBridge::disconnect();
        $this->assertFalse(DbBridge::isConnected());
        $this->assertNull(Config::get('database.connections.tenant'));
    }

    #[Test]
    public function it_supports_simultaneous_connections()
    {
        // Setup Central Data
        DB::connection('central_db')->table('tenants')->insert([
            'name' => 'Test Tenant',
            'db_driver' => 'sqlite',
            'db_name' => 'tenant1',
            'status' => 'active'
        ]);

        // Setup Tenant Model
        $tenant = new DbBridgeTestModel([
            'id' => 1,
            'db_driver' => 'sqlite',
            'db_name' => ':memory:',
        ]);

        // Connect
        DbBridge::connect($tenant);

        // Create a table in tenant DB
        Schema::connection('tenant')->create('orders', function ($table) {
            $table->id();
            $table->string('item');
            $table->timestamps();
        });

        // Insert into tenant DB
        DB::connection('tenant')->table('orders')->insert(['item' => 'Laptop']);

        // Assert Central Query
        $centralTenant = DB::connection('central_db')->table('tenants')->first();
        $this->assertEquals('Test Tenant', $centralTenant->name);

        // Assert Tenant Query
        $tenantOrder = DB::connection('tenant')->table('orders')->first();
        $this->assertEquals('Laptop', $tenantOrder->item);
    }
}

class DbBridgeTestModel extends Model implements DbBridgeConnectionContract
{
    protected $guarded = [];

    public function getDatabaseDriver(): string
    {
        return $this->db_driver;
    }
    public function getDatabaseHost(): string
    {
        return $this->db_host ?? '127.0.0.1';
    }
    public function getDatabasePort(): int
    {
        return $this->db_port ?? 3306;
    }
    public function getDatabaseName(): string
    {
        return $this->db_name;
    }
    public function getDatabaseUsername(): string
    {
        return $this->db_username ?? 'root';
    }
    public function getDatabasePassword(): string
    {
        return $this->db_password ?? '';
    }
}
