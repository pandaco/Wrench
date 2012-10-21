<?php

namespace Wrench\Application;

use \Wrench\Connection;

/**
 * Wrench Server Application
 */
abstract class Application
{
    /**
     * Optional: handle a connection
     */
    // abstract public function onConnect($connection);

    /**
     * Optional handle a disconnection
     *
     * @param
     */
    // abstract public function onDisconnect($connection);

    /**
     * Handle data received from a client
     *
     * @param Payload $payload A payload object, that supports __toString()
     * @param Connection $connection
     */
    abstract public function onData($payload, Connection $connection);
}