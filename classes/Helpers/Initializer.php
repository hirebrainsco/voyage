<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Commands\Init;
use Voyage\Core\Configuration;
use Voyage\Core\DatabaseConnection;
use Voyage\Core\DatabaseConnectionWithIoInterface;

/**
 * Class Initializer
 * @package Voyage\Helpers
 */
class Initializer
{
    /**
     * @var DatabaseConnectionWithIoInterface
     */
    private $sender;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * Initializer constructor.
     * @param DatabaseConnectionWithIoInterface $sender
     */
    public function __construct(DatabaseConnectionWithIoInterface $sender)
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
        $fsRoutines = new FileSystemRoutines($this->sender);
        $fsRoutines->clean(); // Remove .voyage directory and all configs if it exists.
        unset($fsRoutines, $dbRoutines);

        // Initialize in database
        $dbRoutines = new DatabaseRoutines($this->sender, $this->sender->getDatabaseConnection());
        $dbRoutines->clean(); // Remove voyage migrations table
        $dbRoutines->createTable();
        unset($dbRoutines);

//        $this->createVoyageDirectory();
//        $this->generateConfig();
//        $this->createGitIgnore();
//        $this->createDatabaseTable();
//        $this->makeFirstDump();
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