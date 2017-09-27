<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

class DatabaseConnection
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var DatabaseSettings
     */
    private $settings;

    public function __construct(DatabaseSettings $settings)
    {
        $this->settings = $settings;
        $this->connect();
    }

    private function connect()
    {
        $this->pdo = new \PDO($this->getDsn(), $this->settings->getUsername(), $this->settings->getPassword());
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->query('USE ' . $this->settings->getDatabaseName());
    }

    private function getDsn()
    {
        return sprintf('mysql:host=%s;port=%s;database=%s',
            $this->settings->getHost(),
            $this->settings->getPort(),
            $this->settings->getDatabaseName());
    }
}