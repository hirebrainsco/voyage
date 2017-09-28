<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Helpers\ConfigFiles\Ignore;

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
        $fileSystemRoutines->createDirectories(); // Create voyage directories.
        $fileSystemRoutines->createConfigFiles(); // Create Voyage directory and configuration files.
        unset($fileSystemRoutines);

        // Initialize in database
        $databaseRoutines = new DatabaseRoutines($this->sender);
        $databaseRoutines->clean(); // Remove voyage migrations table.
        $databaseRoutines->createTable(); // Create voyage migrations table.
        unset($databaseRoutines);

        $this->showSuccessMessage();
    }

    private function showSuccessMessage()
    {
        $ignoreConfig = new Ignore();
        $ignoreConfigPath = $ignoreConfig->getFilePath();
        unset($ignoreConfig);

        $message = PHP_EOL;
        $message .= '<options=bold>Next Steps:</>' . PHP_EOL;
        $message .= " 1) Now you should add replacement variables to your environment file which is located at: <comment>" . $this->sender->getEnvironment()->getPathToEnvironmentConfig() . "</comment>" . PHP_EOL;
        $message .= " 2) Add/edit list of the tables which should be ignored in: <comment>" . $ignoreConfigPath . "</comment>" . PHP_EOL;
        $message .= " 3) Run \"voyage make\" command to create your first migration with current database state." . PHP_EOL;

        $this->sender->info('Initialization successfully completed.');
        $this->sender->writeln($message);
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