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
use Voyage\Core\PlatformConfiguration;

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
     * @var PlatformConfiguration
     */
    private $detectedPlatform;

    /**
     * @return PlatformConfiguration
     */
    public function getDetectedPlatform()
    {
        return $this->detectedPlatform;
    }

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
        $settings = $this->sender->getDatabaseSettings();
        $input = $this->sender->getInput();

        if (!is_null($input->getOption('host'))) {
            $hostAndPort = $this->getHostAndPort($input->getOption('host'));
            $settings->setHost($hostAndPort[0]);
            $settings->setPort($hostAndPort[1]);
        }

        if (!is_null($input->getOption('user'))) {
            $settings->setUsername($input->getOption('user'));
        }

        if (!is_null($input->getOption('pass'))) {
            $settings->setPassword($input->getOption('pass'));
        }

        if (!is_null($input->getOption('db'))) {
            $settings->setDatabaseName($input->getOption('db'));
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
        $platformInstance = $configurations->read($this->sender->getDatabaseSettings(), $configurationName);
        unset($configurations);

        if (is_object($platformInstance)) {
            $platformName = $platformInstance->getName();

            if (!empty($platformName)) {
                $this->sender->report('Detected platform: ' . $platformName . '. Successfully read configuration file.');
                $this->detectedPlatform = $platformInstance;
            }
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
            $answer = $helper->ask($this->sender->getInput(), $this->sender->getOutput(), $question);
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

            $username = $helper->ask($this->sender->getInput(), $this->sender->getOutput(), $question);
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

            $password = $helper->ask($this->sender->getInput(), $this->sender->getOutput(), $question);
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

            $databaseName = $helper->ask($this->sender->getInput(), $this->sender->getOutput(), $question);
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