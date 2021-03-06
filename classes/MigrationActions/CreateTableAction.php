<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

/**
 * Class CreateTableAction
 * @package Voyage\MigrationActions
 */
class CreateTableAction extends TableAction
{
    /**
     * @return string
     * @throws \Exception
     */
    public function getApply()
    {
        return $this->getCreate();
    }

    /**
     * @return string
     */
    public function getRollback()
    {
        return $this->getDrop();
    }
}