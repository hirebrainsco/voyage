<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\Configuration;

/**
 * Class DropTableAction
 * @package Voyage\MigrationActions
 */
class DropTableAction extends TableAction
{
    /**
     * @return string
     */
    protected function getTableName()
    {
        return Configuration::getInstance()->getTempTablePrefix() . $this->tableName;
    }

    /**
     * @return string
     */
    public function getApply()
    {
        return $this->getDrop();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getRollback()
    {
        return $this->getCreate();
    }
}