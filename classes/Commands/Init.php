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
use Symfony\Component\Console\Question\Question;
use Voyage\Core\Command;
use Voyage\Core\DatabaseConnection;
use Voyage\Core\Environment;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\DatabaseSettings;
use Voyage\Routines\DatabaseSettingsPrompt;
use Voyage\Routines\Initializer;
use Voyage\Routines\PlatformConfigurations;

/**
 * Class Init
 * @package Voyage\Commands
 */
class Init extends Command implements EnvironmentControllerInterface
{
    /**
     * Init constructor.
     */
    public function __construct()
    {
        $this->setEnvironment(new Environment());
        $this->getEnvironment()->setDatabaseSettings(new DatabaseSettings());

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
        $this->initEnvironment();
        $this->performInit();
    }

    private function initEnvironment()
    {
        $helper = $this->getHelper('question');
        $question = new Question('Environment name (for example: development): ');
        $question->setValidator(function ($answer) {
            $answer = str_replace(['/', '\\', '..', '~', '@', '!', '|', '$', '<', '>'], '', trim($answer));
            if (empty($answer)) {
                throw new \RuntimeException('Environment name cannot be empty.');
            }

            return $answer;
        });

        $environmentName = trim($helper->ask($this->getInput(), $this->getOutput(), $question));
        if (empty($environmentName)) {
            $this->fatalError('Environment name is empty!');
        }

        $this->getEnvironment()->setName($environmentName);
    }

    private function performInit()
    {
        try {
            $initializer = new Initializer($this);
            $initializer->run();
            unset($initializer);
        } catch (\Exception $exception) {
            $this->fatalError($exception->getMessage());
        }
    }

    /**
     * Check if Voyage has been already initialized.
     */
    private function checkIfAlreadyInitialized()
    {
        if ($this->getConfiguration()->isVoyageDirExist()) {
            if (true !== $this->getInput()->getOption('force')) {
                $this->fatalError('Voyage has been already initialized in the current directory. Use --force or -f option to overwrite current Voyage data and settings.');
            } else {
                $this->writeln('<options=reverse>Voyage has been already initialized in the current directory. Existing data and settings will be overwritten because --force parameter is present (it\'s not late yet to press Ctrl+C to stop execution).</>');
            }
        }
    }

    /**
     * Check and initialize database connection.
     */
    private function retrieveDatabaseSettings()
    {
        $dbSettingsPrompt = new DatabaseSettingsPrompt($this);
        $dbSettingsPrompt->prompt();

        if (is_object($dbSettingsPrompt->getDetectedPlatform())) {
            $this->getEnvironment()->setPlatformConfiguration($dbSettingsPrompt->getDetectedPlatform());
        }

        unset($dbSettingsPrompt);
    }

    /*
     * Try to connect to database.
     */
    private function connectToDatabase()
    {
        try {
            $this->getEnvironment()->setDatabaseConnection(new DatabaseConnection($this->getDatabaseSettings()));
        } catch (\Exception $exception) {
            $this->fatalError($exception->getMessage());
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
            $this->fatalError("Configuration '" . $configurationName . "' is not supported.");
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