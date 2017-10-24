<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Configuration\Ignore;
use Voyage\Core\Migrations;

/**
 * Class Initializer
 * @package Voyage\Routines
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
        $this->configuration = Configuration::getInstance();
    }

    /**
     * Run initializer.
     * @param bool $performCleanup
     */
    public function run($performCleanup = true)
    {
        $this->checkIntegrity();

        // Initialize in filesystem
        $fileSystemRoutines = new FileSystemRoutines($this->sender);

        if ($performCleanup) {
            $fileSystemRoutines->clean(); // Remove .voyage directory and all configs if it exists.
        }

        $fileSystemRoutines->createDirectories(); // Create voyage directories.
        $fileSystemRoutines->createConfigFiles(); // Create Voyage directory and configuration files.
        unset($fileSystemRoutines);

        // Initialize in database
        $databaseRoutines = new DatabaseRoutines($this->sender);
        $databaseRoutines->clean(); // Remove voyage migrations table.
        $databaseRoutines->createTable(); // Create voyage migrations table.
        unset($databaseRoutines);

        $this->showSuccessMessage($performCleanup);
    }

    /**
     * @param bool $performCleanup
     */
    private function showSuccessMessage($performCleanup = true)
    {
        $ignoreConfig = new Ignore();
        $ignoreConfigPath = $ignoreConfig->getFilePath();
        unset($ignoreConfig);

        $message = PHP_EOL;
        $message .= '<options=bold>Next Steps:</>' . PHP_EOL;

        // Check if migration files exist
        $message .= " 1) Now you should add replacement variables to your environment file which is located at: <comment>" . $this->sender->getEnvironment()->getPathToEnvironmentConfig() . "</comment>" . PHP_EOL;

        if ($performCleanup || !$this->migrationFilesExist()) {
            $message .= " 2) Add/edit list of the tables which should be ignored in: <comment>" . $ignoreConfigPath . "</comment>" . PHP_EOL;
            $message .= " 3) Run \"voyage make\" command to create your first migration with current database state." . PHP_EOL;
        } else {
            $message .= " 2) Run \"voyage list\" to view list of not applied migrations and then run \"voyage apply\" to apply them to the current environment." . PHP_EOL;
        }

        $this->sender->info('Initialization successfully completed.');
        $this->sender->writeln($message);
    }

    /**
     * @return bool
     */
    private function migrationFilesExist()
    {
        $migrations = new Migrations($this->sender);
        $migrationsList = $migrations->getAllMigrationFiles();
        unset($migrations);

        return !empty($migrationsList);
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