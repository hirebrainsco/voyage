<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Commands\Init;
use Voyage\Core\Configuration;
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

        $fsRoutines = new FileSystemRoutines($this->sender);

        // Remove .voyage directory and all configs if it exists.
        $fsRoutines->clean();

        $this->createDirAndConfig();
        $this->createDatabaseTable();
        $this->makeFirstDump();
    }

    protected function createDirAndConfig()
    {
        $this->createVoyageDirectory();
        $this->generateConfig();
        $this->createGitIgnore();
    }

    protected function createVoyageDirectory()
    {

    }

    protected function createGitIgnore()
    {

    }

    protected function generateConfig()
    {

    }

    protected function createDatabaseTable()
    {

    }

    protected function makeFirstDump()
    {

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