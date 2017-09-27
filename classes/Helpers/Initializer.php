<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;

/**
 * Class Initializer
 * @package Voyage\Helpers
 */
class Initializer
{
    /**
     * @var EnvironmentControllerInterface
     */
    private $sender;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * Initializer constructor.
     * @param EnvironmentControllerInterface $sender
     */
    public function __construct(EnvironmentControllerInterface $sender)
    {
        $this->sender = $sender;
        $this->configuration = new Configuration();
    }

    /**
     * Run initializer.
     */
    public function run()
    {
        $this->checkIntegrity();

        // Initialize in filesystem
        $fileSystemRoutines = new FileSystemRoutines($this->sender);
        $fileSystemRoutines->clean(); // Remove .voyage directory and all configs if it exists.
        $fileSystemRoutines->createConfigFiles(); // Create Voyage directory and configuration files.
        unset($fileSystemRoutines);

        // Initialize in database
        $databaseRoutines = new DatabaseRoutines($this->sender);
        $databaseRoutines->clean(); // Remove voyage migrations table.
        $databaseRoutines->createTable(); // Create voyage migrations table.
        unset($databaseRoutines);
    }

    /**
     * @throws \Exception
     */
    private function checkIntegrity()
    {
        if (!$this->sender->getDatabaseConnection() || !$this->sender->getDatabaseConnection()->isConnected()) {
            throw new \Exception('Not connected to database!');
        }
    }
}