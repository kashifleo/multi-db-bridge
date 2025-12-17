<?php

if (!function_exists('tenant')) {
    /**
     * Get the tenant facade or current tenant.
     *
     * @return \Kashifleo\MultiDBBridge\Contracts\DbBridgeConnectionContract|null
     */
    function tenant()
    {
        return \Kashifleo\MultiDBBridge\Facades\DbBridge::current();
    }
}
