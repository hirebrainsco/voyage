<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

/**
 * Class AddFieldAction
 * @package Voyage\MigrationActions
 */
class AddFieldAction extends FieldAction
{
    /**
     * @return string
     */
    public function getApply()
    {
        return $this->getAdd();
    }

    /**
     * @return string
     */
    public function getRollback()
    {
        return $this->getDrop();
    }
}