<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;

/**
 * Class MigrationAction
 * @package Voyage\Core
 */
abstract class MigrationAction
{
    /**
     * @var string
     */
    protected $tableName = '';

    /**
     * @var DatabaseConnection
     */
    protected $connection;

    /**
     * MigrationAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     */
    public function __construct(DatabaseConnection $connection, $tableName)
    {
        $this->tableName = $tableName;
        $this->connection = $connection;
    }

    public function getApply()
    {
        throw new \Exception("getApply() method not implemented!");
    }

    public function getRollback()
    {
        throw new \Exception("getRollback() method not implemented!");
    }

    /**
     * @param $code
     * @param string $prefix
     * @return string
     */
    protected function prepareTableNameForExport($code, $prefix = '')
    {
        return str_replace($prefix . '`' . $this->tableName . '`', $prefix . '`{{:' . $this->tableName . ':}}`', $code);
    }
}