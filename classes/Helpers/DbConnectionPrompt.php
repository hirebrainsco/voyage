<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Symfony\Component\Console\Input\InputInterface;
use Voyage\Core\DatabaseConnection;

/**
 * Class DbConnectionPrompt
 * @package Voyage\Helpers
 */
class DbConnectionPrompt
{
    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * DbConnectionPrompt constructor.
     * @param InputInterface $input
     * @param DatabaseConnection $databaseConnection
     */
    public function __construct(InputInterface $input, DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;

        $this->fillFromInput($input);
        $this->promptMissingData();
    }

    /**
     * @param InputInterface $input
     */
    private function fillFromInput(InputInterface $input)
    {
        $hostAndPort = $this->getHostAndPort($input->getOption('host'));
        print_r($hostAndPort);
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

        return [$host, DatabaseConnection::defaultPort];
    }
}