<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

/**
 * Class DatabaseConnection
 * @package Voyage\Core
 */
class DatabaseSettings
{
    /**
     * Default MySQL server port.
     */
    const DefaultPort = 3306;

    /**
     * Default MySQL host.
     */
    const DefaultHost = 'localhost';

    /**
     * @var string
     */
    private $host = '';
    /**
     * @var int
     */
    private $port = DatabaseSettings::DefaultPort;
    /**
     * @var string
     */
    private $username = '';
    /**
     * @var string
     */
    private $password = '';
    /**
     * @var string
     */
    private $databaseName = '';

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        if (empty($host)) {
            $host = DatabaseSettings::DefaultHost;
        } else {
            if (strpos($host, ':') !== false) {
                $result = explode(':', $host);

                $host = $result[0];
                $this->setPort($result[1]);
            }
        }

        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $port = intval($port);
        if ($port < 0) {
            $port = DatabaseSettings::DefaultPort;
        }

        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return sprintf('mysql:host=%s;port=%s;database=%s',
            $this->getHost(),
            $this->getPort(),
            $this->getDatabaseName());
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param string $databaseName
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * Copy values from another object.
     * @param DatabaseSettings $source
     */
    public function copy(DatabaseSettings $source)
    {
        $this->setHost($source->getHost());
        $this->setPort($source->getPort());
        $this->setDatabaseName($source->getDatabaseName());
        $this->setUsername($source->getUsername());
        $this->setPassword($source->getPassword());
    }

    /**
     * Get database access info as an array.
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getDatabaseName(),
            'user' => $this->getUsername(),
            'pass' => $this->getPassword(),
            'host' => $this->getHost(),
            'port' => $this->getPort()
        ];
    }
}