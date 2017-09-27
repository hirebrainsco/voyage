<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Core\Command;
use Voyage\Core\DatabaseConnection;
use Voyage\Core\DatabaseSettings;
use Voyage\Helpers\DatabaseSettingsPrompt;
use Voyage\Helpers\PlatformConfigurations;

/**
 * Class Init
 * @package Voyage\Commands
 */
class Init extends Command
{
    /**
     * @var DatabaseSettings
     */
    private $databaseSettings;

    /**
     * Init constructor.
     */
    public function __construct()
    {
        $this->databaseSettings = new DatabaseSettings();

        $this->setName('init');
        $this->setDescription('Initialize voyage in the current working directory');

        parent::__construct();
        $this->addCommandOptions();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->displayAppName();

        $this->checkPlatformConfigMode();
        $this->checkIfAlreadyInitialized();
        $this->retrieveDatabaseSettings();
        $this->connectToDatabase();
    }

    /**
     * Check if Voyage has been already initialized.
     */
    private function checkIfAlreadyInitialized()
    {
        if ($this->getConfiguration()->isVoyageDirExist()) {
            if (true !== $this->getInput()->getOption('force')) {
                $this->writeln("Fatal error: Voyage has been already initialized in the current directory. Use --force or -f option to overwrite current Voyage data and settings.");
                exit(1);
            } else {
                if (!$this->isQuiet()) {
                    $this->writeln("<info>Voyage has been already initialized in the current directory. Overwriting existing data and settings.</info>");
                }
            }
        }
    }

    /**
     * Check and initialize database connection.
     */
    private function retrieveDatabaseSettings()
    {
        $dbSettingsPrompt = new DatabaseSettingsPrompt($this, $this->getInput(), $this->getOutput(), $this->databaseSettings);
        $dbSettingsPrompt->prompt();
        unset($dbSettingsPrompt);
    }

    /*
     * Try to connect to database.
     */
    private function connectToDatabase()
    {
        try {
            $connection = new DatabaseConnection($this->databaseSettings);
        } catch (\Exception $exception) {
            $this->writeln('Fatal error: ' . $exception->getMessage());
        }
    }

    /**
     * Check value of --config | -c option.
     */
    private function checkPlatformConfigMode()
    {
        $configurationName = $this->getInput()->getOption('config');

        if ($configurationName == PlatformConfigurations::None || $configurationName == PlatformConfigurations::AutoDetect) {
            return;
        }

        $configurations = new PlatformConfigurations();
        if (true !== $configurations->exists($configurationName)) {
            $this->writeln("Fatal error: Configuration '" . $configurationName . "' is not supported.");
            exit(1);
        }
    }

    /**
     * Add options for 'init' command.
     */
    private function addCommandOptions()
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Continue execution and overwrite existing .voyage configuration and clean existing migrations.');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Specify a platform, in this case Voyage will detect database connection settings automatically. For example: --config=wordpress.', 'auto');
        $this->addOption('host', '', InputOption::VALUE_REQUIRED, 'Database host (and port, port is optional, for example: localhost:3306)');
        $this->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Database username');
        $this->addOption('pass', 'p', InputOption::VALUE_REQUIRED, 'Database password');
        $this->addOption('db', 'd', InputOption::VALUE_REQUIRED, 'Database name');
    }
}