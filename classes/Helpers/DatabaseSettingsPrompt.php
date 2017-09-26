<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Voyage\Core\DatabaseSettings;

/**
 * Class DbConnectionPrompt
 * @package Voyage\Helpers
 */
class DatabaseSettingsPrompt
{
    /**
     * @var Command
     */
    private $sender;

    /**
     * @var DatabaseSettings
     */
    private $databaseSettings;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * DbConnectionPrompt constructor.
     * @param Command $sender
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param DatabaseSettings $databaseSettings
     */
    public function __construct(Command $sender, InputInterface $input, OutputInterface $output, DatabaseSettings $databaseSettings)
    {
        $this->sender = $sender;
        $this->input = $input;
        $this->output = $output;
        $this->databaseSettings = $databaseSettings;
    }

    public function prompt()
    {
        $this->fillFromInput();
        $this->fillFromConfig();
        $this->promptMissingData();
    }

    /**
     * Fill data from input options.
     */
    private function fillFromInput()
    {
        $hostAndPort = $this->getHostAndPort($this->input->getOption('host'));
        $this->databaseSettings->setHost($hostAndPort[0]);
        $this->databaseSettings->setPort($hostAndPort[1]);
        $this->databaseSettings->setUsername($this->input->getOption('user'));
        $this->databaseSettings->setPassword($this->input->getOption('pass'));
        $this->databaseSettings->setDatabaseName($this->input->getOption('db'));
    }

    /**
     * Auto-detect configuration parameters if --config parameter has been passed.
     */
    private function fillFromConfig()
    {
        $configValue = $this->input->getOption('config');
        if ($configValue == 'none') {
            return;
        }
    }

    /**
     * Ask for missing data.
     */
    private function promptMissingData()
    {
        $helper = $this->sender->getHelper('question');

        // Database host
        if (empty($this->input->getOption('host'))) {
            $question = new Question('Database host and port (press ENTER for localhost:3306) ', 'localhost:3306');
            $answer = $helper->ask($this->input, $this->output, $question);
            $hostAndPort = $this->getHostAndPort($answer);

            $this->databaseSettings->setHost($hostAndPort[0]);
            $this->databaseSettings->setPort($hostAndPort[1]);
            unset($question);
        }

        // Username
        if (empty($this->input->getOption('user'))) {
            $question = new Question('Database username: ');
            $question->setValidator(function ($answer) {
                $answer = trim($answer);
                if (empty($answer)) {
                    throw new \RuntimeException('Wrong database username has been entered, please check your input and try again.');
                }

                return $answer;
            });

            $username = $helper->ask($this->input, $this->output, $question);
            $this->databaseSettings->setUsername($username);
            unset($question);
        }

        // Password
        if (empty($this->input->getOption('pass'))) {
            $question = new Question('Database password (press ENTER if password is empty): ');
            $question->setHidden(true);
            $question->setNormalizer(function ($answer) {
                return trim($answer);
            });

            $password = $helper->ask($this->input, $this->output, $question);
            $this->databaseSettings->setPassword($password);
            unset($question);
        }

        // Database name
        if (empty($this->input->getOption('db'))) {
            $question = new Question('Database name: ');
            $question->setValidator(function ($answer) {
                $answer = trim($answer);
                if (empty($answer)) {
                    throw new \RuntimeException('Database name cannot be empty, please check your input and try again.');
                }

                return $answer;
            });

            $databaseName = $helper->ask($this->input, $this->output, $question);
            $this->databaseSettings->setDatabaseName($databaseName);
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

        if (empty($host)) {
            $host = DatabaseSettings::DefaultHost;
        }

        return [$host, DatabaseSettings::DefaultPort];
    }
}