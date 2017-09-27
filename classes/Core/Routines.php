<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

abstract class Routines
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var InputOutputInterface
     */
    private $sender;

    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return InputOutputInterface
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Routines constructor.
     * @param DatabaseConnectionWithIoInterface $sender
     */
    public function __construct(DatabaseConnectionWithIoInterface $sender)
    {
        $this->configuration = new Configuration();
        $this->sender = $sender;
        $this->databaseConnection = $sender->getDatabaseConnection();
    }

    /**
     * @return DatabaseConnection
     */
    public function getDatabaseConnection()
    {
        return $this->databaseConnection;
    }
}