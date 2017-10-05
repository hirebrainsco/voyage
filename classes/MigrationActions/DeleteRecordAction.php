<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\Migration;

/**
 * Class DeleteRecordAction
 * @package Voyage\MigrationActions
 */
class DeleteRecordAction extends InsertRecordAction
{
    /**
     * @return string|void
     */
    public function getApply()
    {
        return parent::getRollback();
    }

    /**
     * @return string|void
     */
    public function getRollback()
    {
        return parent::getApply();
    }

    /**
     * DeleteRecordAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     * @param array $row
     * @param Migration $migration
     * @param bool $isNewTable
     */
    public function __construct(DatabaseConnection $connection, $tableName, array $row, Migration $migration, $isNewTable = false)
    {
        parent::__construct($connection, $tableName, $row, $migration, $isNewTable);
        $this->isNewTable = false;
    }
}