<?php

namespace Kashifleo\MultiDBBridge\Support;

use Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract;

class DbBridgeContext
{
    /**
     * The current tenant instance.
     *
     * @var DbBridgeConnectionContract|null
     */
    protected ?DbBridgeConnectionContract $tenant = null;

    /**
     * Set the current tenant.
     *
     * @param DbBridgeConnectionContract $tenant
     * @return void
     */
    public function set(DbBridgeConnectionContract $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the current tenant.
     *
     * @return DbBridgeConnectionContract|null
     */
    public function get(): ?DbBridgeConnectionContract
    {
        return $this->tenant;
    }

    /**
     * Clear the current tenant.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->tenant = null;
    }

    /**
     * Check if a tenant is currently set.
     *
     * @return bool
     */
    public function has(): bool
    {
        return !is_null($this->tenant);
    }
}
