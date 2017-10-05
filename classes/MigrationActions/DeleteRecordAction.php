<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\Migration;

class DeleteRecordAction extends InsertRecordAction
{
    public function getApply()
    {
        return parent::getRollback();
    }

    public function getRollback()
    {
        return parent::getApply();
    }

    public function __construct(DatabaseConnection $connection, $tableName, array $row, Migration $migration, $isNewTable = false)
    {
        parent::__construct($connection, $tableName, $row, $migration, $isNewTable);
        $this->isNewTable = false;
    }
}