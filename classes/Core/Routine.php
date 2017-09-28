<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

abstract class Routine
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var EnvironmentControllerInterface
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
     * @return EnvironmentControllerInterface
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Routines constructor.
     * @param EnvironmentControllerInterface $sender
     */
    public function __construct(EnvironmentControllerInterface $sender)
    {
        $this->configuration = Configuration::getInstance();
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