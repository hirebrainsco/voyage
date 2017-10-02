<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;

/**
 * Class CreateTableAction
 * @package Voyage\MigrationActions
 */
class CreateTableAction extends MigrationAction
{
    /**
     * CreateTableAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     */
    public function __construct(DatabaseConnection $connection, $tableName)
    {
        parent::__construct($connection, $tableName);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getApply()
    {
        $sql = 'SHOW CREATE TABLE `' . $this->tableName . '`';
        $row = $this->connection->fetch($sql);

        if (empty($row) || !isset($row['Create Table'])) {
            throw new \Exception('Failed to get CREATE TABLE for table: ' . $this->tableName);
        }

        return $this->prepareTableNameForExport($row['Create Table']) . ';' . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getRollback()
    {
        $code = 'DROP TABLE IF EXISTS `' . $this->tableName . '`;';
        return $this->prepareTableNameForExport($code);
    }
}