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

    public function setVariables()
    {
        $sql = "SET SQL_MODE='ALLOW_INVALID_DATES'";
        $this->pdo->exec($sql);
    }

    /**
     * Execute SQL query.
     * @param $sql
     * @param array $sqlVars
     * @return int
     */
    public function exec($sql, $sqlVars = [])
    {
        if (empty($sqlVars)) {
            return $this->pdo->exec($sql);
        }

        $stmt = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
        return $stmt->execute($sqlVars);
    }

    /**
     * Run SQL query
     * @param $sql
     * @return \PDOStatement
     */
    public function query($sql)
    {
        return $this->pdo->query($sql);
    }

    public function quote($str)
    {
        return $this->pdo->quote($str);
    }

    /**
     * @param $sql
     * @return mixed
     */
    public function fetch($sql)
    {
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Connect to database server and select database.
     */
    private function connect()
    {
        try {
//            error_reporting(0);
            $this->pdo = new \PDO($this->getDsn(), $this->settings->getUsername(), $this->settings->getPassword());
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->query('USE `' . $this->settings->getDatabaseName() . '`');
            $this->connected = true;
        } catch (\Exception $e) {
            throw $e;
        }
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

    /**
     * @return DatabaseSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}