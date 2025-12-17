<?php

namespace Kashifleo\MultiDBBridge\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void connect(\Kashifleo\MultiDBBridge\Contracts\TenantConnectionContract $tenant)
 * @method static void disconnect()
 * @method static \Kashifleo\MultiDBBridge\Contracts\TenantConnectionContract|null current()
 * @method static bool isConnected()
 * 
 * @see \Kashifleo\MultiDBBridge\TenantConnectionManager
 */
class DbBridge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'dbbridge';
    }
}
