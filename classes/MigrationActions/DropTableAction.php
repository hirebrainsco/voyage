<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\Configuration;
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
        return $this->prepareTableNameForExport($code);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getRollback()
    {
        $sql = 'SHOW CREATE TABLE `' . Configuration::getInstance()->getTempTablePrefix() . $this->tableName . '`';
        $row = $this->connection->fetch($sql);

        if (empty($row) || !isset($row['Create Table'])) {
            throw new \Exception('Failed to get CREATE TABLE for table: ' . $this->tableName);
        }

        $code = str_replace(Configuration::getInstance()->getTempTablePrefix(), '', $row['Create Table']);
        return $this->prepareTableNameForExport($code) . ';' . PHP_EOL;
    }
}