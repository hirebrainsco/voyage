<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

class ActionsFormatter
{
    private $actions = [];

    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }

    /**
     * @return string
     */
    public function generate()
    {
        if (empty($this->actions)) {
            return '';
        }

        $applyCode = '# @APPLY' . PHP_EOL;
        $rollbackCode = '# @ROLLBACK' . PHP_EOL;

        foreach ($this->actions as $action) {
            $applyCode .= $action->getApply() . PHP_EOL;
            $rollbackCode .= $action->getRollback() . PHP_EOL;
        }

        return $applyCode . PHP_EOL . $rollbackCode;
    }
}