<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;

/**
 * Class DropTableAction
 * @package Voyage\MigrationActions
 */
class DropTableAction extends MigrationAction
{
    /**
     * DropTableAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     */
    public function __construct(DatabaseConnection $connection, $tableName)
    {
        parent::__construct($connection, $tableName);
    }

    /**
     * @return string
     */
    public function getApply()
    {
        $code = 'DROP TABLE IF EXISTS `' . $this->tableName . '`;';
        return $code;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getRollback()
    {
        $sql = 'SHOW CREATE TABLE `' . $this->tableName . '`';
        $row = $this->connection->fetch($sql);

        if (empty($row) || !isset($row['Create Table'])) {
            throw new \Exception('Failed to get CREATE TABLE for table: ' . $this->tableName);
        }

        return $row['Create Table'];
    }
}