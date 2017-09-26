<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Symfony\Component\Console\Input\InputInterface;
use Voyage\Core\DatabaseSettings;

/**
 * Class DbConnectionPrompt
 * @package Voyage\Helpers
 */
class DatabaseSettingsPrompt
{
    /**
     * @var DatabaseSettings
     */
    private $databaseSettings;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * DbConnectionPrompt constructor.
     * @param InputInterface $input
     * @param DatabaseSettings $databaseSettings
     */
    public function __construct(InputInterface $input, DatabaseSettings $databaseSettings)
    {
        $this->databaseSettings = $databaseSettings;
        $this->input = $input;
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
        // TODO
    }

    /**
     * Ask for missing data.
     */
    private function promptMissingData()
    {

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