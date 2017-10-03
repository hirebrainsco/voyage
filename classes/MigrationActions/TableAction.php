<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\Configuration;

/**
 * Class TableAction
 * @package Voyage\MigrationActions
 */
abstract class TableAction extends MigrationAction
{
    protected function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    protected function getDrop()
    {
        $code = 'DROP TABLE IF EXISTS `' . $this->getTableName() . '`;';
        $code = str_replace(Configuration::getInstance()->getTempTablePrefix(), '', $code);

        return $this->prepareTableNameForExport($code);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getCreate()
    {
        $sql = 'SHOW CREATE TABLE `' . $this->getTableName() . '`';
        $row = $this->connection->fetch($sql);

        if (empty($row) || !isset($row['Create Table'])) {
            throw new \Exception('Failed to get CREATE TABLE for table: ' . $this->tableName);
        }

        $code = str_replace(Configuration::getInstance()->getTempTablePrefix(), '', $row['Create Table']);
        return $this->prepareTableNameForExport($code) . ';' . PHP_EOL;
    }
}