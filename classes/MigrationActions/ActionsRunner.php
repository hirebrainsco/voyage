<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\Configuration;
use Voyage\Core\DatabaseConnection;
use Voyage\Routines\DatabaseRoutines;

/**
 * Class ActionsRunner
 * @package Voyage\MigrationActions
 */
class ActionsRunner
{
    /**
     * Apply Mode
     */
    const Apply = true;
    /**
     * Rollback Mode
     */
    const Rollback = false;

    /**
     * @var array
     */
    private $actions = [];

    /**
     * @var bool
     */
    private $runOnTemporaryTables = false;

    /**
     * @var DatabaseConnection
     */
    private $connection;

    /**
     * ActionsFormatter constructor.
     * @param DatabaseConnection $connection
     * @param array $actions
     * @param bool $runOnTemporaryTables
     */
    public function __construct(DatabaseConnection $connection, array $actions, $runOnTemporaryTables = false)
    {
        $this->actions = $actions;
        $this->connection = $connection;
        $this->setRunOnTemporaryTables($runOnTemporaryTables);
    }

    /**
     * @return bool
     */
    public function isRunOnTemporaryTables()
    {
        return $this->runOnTemporaryTables;
    }

    /**
     * @param bool $runOnTemporaryTables
     */
    public function setRunOnTemporaryTables($runOnTemporaryTables)
    {
        $this->runOnTemporaryTables = $runOnTemporaryTables;
    }

    /**
     *
     */
    public function apply()
    {
        $this->execute(ActionsRunner::Apply);
    }

    /**
     *
     */
    public function rollback()
    {
        $this->execute(ActionsRunner::Rollback);
    }

    /**
     * @param $mode
     */
    protected function execute($mode)
    {
        if (empty($this->actions)) {
            return;
        }

        $prefix = '';
        if ($this->isRunOnTemporaryTables()) {
            $prefix = Configuration::getInstance()->getTempTablePrefix();
        }

        /**
         * @var MigrationAction $action
         */
        foreach ($this->actions as $action) {
            $code = ($mode == ActionsRunner::Apply ? $action->getApply() : $action->getRollback());
            if (empty($code)) {
                continue;
            }

            if (strpos($code, PHP_EOL) !== false) {
                $lines = explode(PHP_EOL, $code);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) {
                        continue;
                    }

                    $code = DatabaseRoutines::replaceTableNames($line, $prefix);
                    $this->runCode($code);
                }
            } else {
                $code = DatabaseRoutines::replaceTableNames($code, $prefix);
                $this->runCode($code);
            }
        }
    }

    /**
     * @param $code
     */
    private function runCode($code)
    {
        $codeParts = explode(PHP_EOL, $code);
        if (empty($codeParts)) {
            return;
        }

        foreach ($codeParts as $sql) {
            $matches = [];
            if (preg_match("/\A\s*(.*)\s*;\s*\z/", $sql, $matches)) {
                $this->connection->exec($sql);
            }
        }
    }
}