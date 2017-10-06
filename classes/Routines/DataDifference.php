<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;
use Voyage\MigrationActions\ActionsFormatter;

/**
 * Class DataDifference
 * @package Voyage\Routines
 */
class DataDifference extends DifferenceRoutines
{
    use DataDifferenceWithPrimaryKey, DataDifferenceWithoutPrimaryKey;

    /**
     * Maximum number of records in buffer.
     */
    const BufferMaxRecords = 100;

    /**
     * @var Migration
     */
    private $migration;

    /**
     * DataDifference constructor.
     * @param EnvironmentControllerInterface $environmentController
     * @param array $comparisonTables
     * @param Migration $migration
     */
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
                // Generate inserts for a new table
                $recordsCount += $this->generateInsertsForNewTables($table);
            } else {
                // Process existing tables
                // Prepare for detection of changes in data
                $databaseRoutines = new DatabaseRoutines($this->environmentController);
                $fields = $databaseRoutines->getTableFields($table->name);
                unset($databaseRoutines);

                $primaryKey = $this->getPrimaryKey($fields);

                if (false === $primaryKey) {
                    // No primary key
                    $recordsCount += $this->generateChangesWithoutPrimaryKey($table, $fields);
                } else {
                    // Primary key exists
                    $recordsCount += $this->generateInserts($table, $fields, $primaryKey);
                    $recordsCount += $this->generateUpdates($table, $fields, $primaryKey);
                    $recordsCount += $this->generateDeletes($table, $fields, $primaryKey);
                }

            }
        }

        return $recordsCount > 0;
    }

    /**
     * @param array $buffer
     * @return bool
     */
    private function flushBuffer(array $buffer)
    {
        $formatter = new ActionsFormatter($buffer);
        $code = $formatter->generate();
        unset($formatter);

        $hasData = !empty($code);
        $this->migration->appendMigrationFile($code);

        unset($code);
        return $hasData;
    }
}