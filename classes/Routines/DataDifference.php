<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Symfony\Component\Console\Helper\ProgressBar;
use Voyage\Configuration\Ignore;
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
     * @var ProgressBar
     */
    private $progressBar;

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

            $this->environmentController->reportProgress('Comparing: `' . $table->name . '`');

            $ignore = new Ignore();
            $ignore->setSender($this->environmentController);
            $ignoreList = $ignore->getIgnoreList();
            unset($ignore);

            $rowsIgnoreList = [];
            $fieldsIgnoreList = [];

            if (!empty($ignoreList) && isset($ignoreList['data']) && isset($ignoreList['data'][$table->name])) {
                $rowsIgnoreList = $ignoreList['data'][$table->name];
            }

            if (!empty($ignoreList) && isset($ignoreList['field']) && isset($ignoreList['field'][$table->name])) {
                $fieldsIgnoreList = $ignoreList['field'][$table->name];
            }

            unset($ignoreList);

            if (!isset($this->comparisonTables['old'][$table->name])) {
                // Generate inserts for a new table
                $recordsCount += $this->generateInsertsForNewTables($table, $rowsIgnoreList);
            } else {
                // Process existing tables
                // Prepare for detection of changes in data
                $databaseRoutines = new DatabaseRoutines($this->environmentController);
                $fields = $databaseRoutines->getTableFields($table->name);
                unset($databaseRoutines);

                $primaryKey = $this->getPrimaryKey($fields);

                if (false === $primaryKey) {
                    // No primary key
                    $recordsCount += $this->generateChangesWithoutPrimaryKey($table, $fields, $rowsIgnoreList, $fieldsIgnoreList);
                } else {
                    // Primary key exists
                    $recordsCount += $this->generateInserts($table, $fields, $primaryKey, $rowsIgnoreList, $fieldsIgnoreList);
                    $recordsCount += $this->generateUpdates($table, $fields, $primaryKey, $rowsIgnoreList, $fieldsIgnoreList);
                    $recordsCount += $this->generateDeletes($table, $fields, $primaryKey, $rowsIgnoreList);
                }

            }
        }

        $this->environmentController->clearProgress();
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