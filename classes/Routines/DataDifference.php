<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;
use Voyage\Core\TableData;
use Voyage\MigrationActions\ActionsFormatter;
use Voyage\MigrationActions\InsertRecordAction;

/**
 * Class DataDifference
 * @package Voyage\Routines
 */
class DataDifference extends DifferenceRoutines
{
    const BufferMaxRecords = 100;

    /**
     * @var Migration
     */
    private $migration;

    public function __construct(EnvironmentControllerInterface $environmentController, array $comparisonTables, Migration $migration)
    {
        parent::__construct($environmentController, $comparisonTables);
        $this->migration = $migration;
    }

    /**
     * @return bool
     */
    public function getDifference()
    {
        if (!$this->hasData()) {
            return false;
        }

        $recordsCount = 0;

        /**
         * @var \Voyage\Core\TableData $table
         */
        foreach ($this->comparisonTables['current'] as $table) {
            if ($table->ignoreData) {
                continue;
            }

            if (!isset($this->comparisonTables['old'][$table->name])) {
                $recordsCount += $this->generateInsertsForNewTables($table);
                continue;
            }

            // TODO: Implement comparison
        }

        return $recordsCount > 0;
    }

    /**
     * @param TableData $table
     * @return int
     */
    protected function generateInsertsForNewTables(TableData $table)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        $sql = 'SELECT * FROM `' . $table->name . '`';
        $stmt = $this->connection->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $totalRecords++;
            $bufferedRecords++;

            $buffer[] = new InsertRecordAction($this->connection, $table->name, $row, $this->migration, true);

            if ($bufferedRecords >= DataDifference::BufferMaxRecords) {
                $this->flushBuffer($buffer);
                $bufferedRecords = 0;

                unset($buffer);
                $buffer = [];
            }
        }

        if ($bufferedRecords > 0 && !empty($buffer)) {
            $this->flushBuffer($buffer);
            unset($buffer);
        }

        return $totalRecords;
    }

    /**
     * @param array $buffer
     */
    private function flushBuffer(array $buffer)
    {
        $formatter = new ActionsFormatter($buffer);
        $code = $formatter->generate();
        unset($formatter);

        $this->migration->appendMigrationFile($code);
        unset($code);
    }
}