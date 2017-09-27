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
class DatabaseConnection
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var bool
     */
    private $connected = false;

    /**
     * @var DatabaseSettings
     */
    private $settings;

    /**
     * @return bool
     */
    public function isConnected()
    {
        if (!is_object($this->pdo)) {
            return false;
        }

        return $this->connected;
    }

    /**
     * DatabaseConnection constructor.
     * @param DatabaseSettings $settings
     */
    public function __construct(DatabaseSettings $settings)
    {
        $this->settings = $settings;
        $this->connect();
    }

    public function exec($sql)
    {
        return $this->pdo->exec($sql);
    }

    /**
     * Connect to database server and select database.
     */
    private function connect()
    {
        $this->pdo = new \PDO($this->getDsn(), $this->settings->getUsername(), $this->settings->getPassword());
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->query('USE ' . $this->settings->getDatabaseName());

        $this->connected = true;
    }

    /**
     * Get data source name.
     * @return string
     */
    private function getDsn()
    {
        return sprintf('mysql:host=%s;port=%s;database=%s',
            $this->settings->getHost(),
            $this->settings->getPort(),
            $this->settings->getDatabaseName());
    }
}