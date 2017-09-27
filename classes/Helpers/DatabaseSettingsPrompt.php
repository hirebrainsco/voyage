<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Symfony\Component\Console\Question\Question;
use Voyage\Core\DatabaseSettings;
use Voyage\Core\EnvironmentControllerInterface;

/**
 * Class DbConnectionPrompt
 * @package Voyage\Helpers
 */
class DatabaseSettingsPrompt
{
    /**
     * @var EnvironmentControllerInterface
     */
    private $sender;

    /**
     * DbConnectionPrompt constructor.
     * @param EnvironmentControllerInterface $sender
     */
    public function __construct(EnvironmentControllerInterface $sender)
    {
        $this->sender = $sender;
    }

    /**
     * Get database access info.
     */
    public function prompt()
    {
        $this->fillFromConfig();
        $this->fillFromInput();
        $this->promptMissingData();
    }

    /**
     * Fill data from input options.
     */
    private function fillFromInput()
    {
        if (!is_null($this->sender->getInput()->getOption('host'))) {
            $hostAndPort = $this->getHostAndPort($this->sender->getInput()->getOption('host'));
            $this->sender->getDatabaseSettings()->setHost($hostAndPort[0]);
            $this->sender->getDatabaseSettings()->setPort($hostAndPort[1]);
        }

        if (!is_null($this->sender->getInput()->getOption('user'))) {
            $this->sender->getDatabaseSettings()->setUsername($this->sender->getInput()->getOption('user'));
        }

        if (!is_null($this->sender->getInput()->getOption('pass'))) {
            $this->sender->getDatabaseSettings()->setPassword($this->sender->getInput()->getOption('pass'));
        }

        if (!is_null($this->sender->getInput()->getOption('db'))) {
            $this->sender->getDatabaseSettings()->setDatabaseName($this->sender->getInput()->getOption('db'));
        }
    }

    /**
     * Auto-detect configuration parameters if --config parameter has been passed.
     */
    private function fillFromConfig()
    {
        $configurationName = $this->sender->getInput()->getOption('config');
        if ($configurationName == PlatformConfigurations::None) {
            return;
        }

        $configurations = new PlatformConfigurations();
        $platformName = $configurations->read($this->sender->getDatabaseSettings(), $configurationName);
        unset($configurations);

        if (!empty($platformName)) {
            $this->sender->report('Detected platform: ' . $platformName . '. Successfully read configuration file.');
        }
    }

    /**
     * Ask for missing data.
     */
    private function promptMissingData()
    {
        $helper = $this->sender->getHelper('question');

        // Database host
        if ($this->sender->getDatabaseSettings()->getHost() == '' || $this->sender->getDatabaseSettings()->getPort() < 0) {
            $question = new Question('Database host and port (press ENTER for localhost:3306) ', 'localhost:3306');
            $answer = $helper->ask($this->input, $this->output, $question);
            $hostAndPort = $this->getHostAndPort($answer);

            $this->sender->getDatabaseSettings()->setHost($hostAndPort[0]);
            $this->sender->getDatabaseSettings()->setPort($hostAndPort[1]);
            unset($question);
        }

        // Username
        if ($this->sender->getDatabaseSettings()->getUsername() == '') {
            $question = new Question('Database username: ');
            $question->setValidator(function ($answer) {
                $answer = trim($answer);
                if (empty($answer)) {
                    throw new \RuntimeException('Wrong database username has been entered, please check your input and try again.');
                }

                return $answer;
            });

            $username = $helper->ask($this->input, $this->output, $question);
            $this->sender->getDatabaseSettings()->setUsername($username);
            unset($question);
        }

        // Password
        if ($this->sender->getDatabaseSettings()->getPassword() == '') {
            $question = new Question('Database password (press ENTER if password is empty): ');
            $question->setHidden(true);
            $question->setNormalizer(function ($answer) {
                return trim($answer);
            });

            $password = $helper->ask($this->input, $this->output, $question);
            $this->sender->getDatabaseSettings()->setPassword($password);
            unset($question);
        }

        // Database name
        if ($this->sender->getDatabaseSettings()->getDatabaseName() == '') {
            $question = new Question('Database name: ');
            $question->setValidator(function ($answer) {
                $answer = trim($answer);
                if (empty($answer)) {
                    throw new \RuntimeException('Database name cannot be empty, please check your input and try again.');
                }

                return $answer;
            });

            $databaseName = $helper->ask($this->input, $this->output, $question);
            $this->sender->getDatabaseSettings()->setDatabaseName($databaseName);
            unset($question);
        }
    }

    /**
     * Parse host and port passed in string like host:port.
     * @param $host
     * @return array
     */
    private function getHostAndPort($host)
    {
        if (strpos($host, ':') !== false) {
            $result = explode(':', $host);
            return [$result[0], intval($result[1])];
        }

        return [$host, DatabaseSettings::DefaultPort];
    }
}