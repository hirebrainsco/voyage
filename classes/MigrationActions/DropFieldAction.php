<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

/**
 * Class DropFieldAction
 * @package Voyage\MigrationActions
 */
class DropFieldAction extends FieldAction
{
    /**
     * @return string
     */
    public function getApply()
    {
        return $this->getDrop();
    }

    /**
     * @return string
     */
    public function getRollback()
    {
        return $this->getAdd();
    }
}