<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

/**
 * Class ActionsFormatter
 * @package Voyage\MigrationActions
 */
class ActionsFormatter
{
    /**
     * @var array
     */
    private $actions = [];

    /**
     * ActionsFormatter constructor.
     * @param array $actions
     */
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

        $hasApplyCode = false;
        $hasRollbackCode = false;

        foreach ($this->actions as $action) {
            $code = $action->getApply();

            if ($code !== false) {
                $hasApplyCode = true;
                $applyCode .= $code . PHP_EOL;
            }

            $code = $action->getRollback();
            if ($code !== false) {
                $hasRollbackCode = true;
                $rollbackCode .= $code . PHP_EOL;
            }
        }

        return ($hasApplyCode ? $applyCode . PHP_EOL : '') . ($hasRollbackCode ? $rollbackCode . PHP_EOL : '');
    }
}