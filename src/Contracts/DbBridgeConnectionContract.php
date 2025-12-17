<?php

namespace Kashifleo\MultiDBBridge\Contracts;

interface DbBridgeConnectionContract
{
    /**
     * Get the database connection driver.
     *
     * @return string
     */
    public function getDatabaseDriver(): string;

    /**
     * Get the database host.
     *
     * @return string
     */
    public function getDatabaseHost(): string;

    /**
     * Get the database port.
     *
     * @return int
     */
    public function getDatabasePort(): int;

    /**
     * Get the database name.
     *
     * @return string
     */
    public function getDatabaseName(): string;

    /**
     * Get the database username.
     *
     * @return string
     */
    public function getDatabaseUsername(): string;

    /**
     * Get the database password.
     *
     * @return string
     */
    public function getDatabasePassword(): string;

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey();
}
