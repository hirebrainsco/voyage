<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\FieldData;
use Voyage\Core\Migration;
use Voyage\Core\TableData;
use Voyage\MigrationActions\ActionsFormatter;
use Voyage\MigrationActions\DeleteRecordAction;
use Voyage\MigrationActions\InsertRecordAction;

/**
 * Class DataDifference
 * @package Voyage\Routines
 */
class DataDifference extends DifferenceRoutines
{
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
                $recordsCount += $this->generateInsertsForNewTables($table);
                continue;
            }

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

            // Detect records that should be deleted
        }

        return $recordsCount > 0;
    }

    /**
     * @param TableData $currentTable
     * @param array $fields
     * @param $primaryKey
     * @return int
     */
    protected function generateInserts(TableData $currentTable, array $fields, $primaryKey)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        $oldTableName = Configuration::getInstance()->getTempTablePrefix() . $currentTable->name;
        $sql = 'SELECT `' . $currentTable->name . '`.* FROM `' . $currentTable->name . '` WHERE `' . $currentTable->name . '`.`' . $primaryKey . '` NOT IN (SELECT `' . $primaryKey . '` FROM `' . $oldTableName . '`)';
        $stmt = $this->connection->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $totalRecords++;
            $bufferedRecords++;

            $buffer[] = new InsertRecordAction($this->connection, $currentTable->name, $row, $this->migration, false);

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
     * @param TableData $currentTable
     * @param array $fields
     * @param $primaryKey
     * @return int
     */
    protected function generateDeletes(TableData $currentTable, array $fields, $primaryKey)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        $oldTableName = Configuration::getInstance()->getTempTablePrefix() . $currentTable->name;
        $sql = 'SELECT `' . $oldTableName . '`.* FROM `' . $oldTableName . '` WHERE `' . $oldTableName . '`.`' . $primaryKey . '` NOT IN (SELECT `' . $primaryKey . '` FROM `' . $currentTable->name . '`)';
        $stmt = $this->connection->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $totalRecords++;
            $bufferedRecords++;

            $buffer[] = new DeleteRecordAction($this->connection, $currentTable->name, $row, $this->migration);

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
     * @param TableData $table
     * @param array $fields
     * @param $primaryKey
     * @return int
     */
    protected function generateUpdates(TableData $table, array $fields, $primaryKey)
    {
//        $buffer = [];
//        $totalRecords = $bufferedRecords = 0;
//
//        $sql = '';
//        $stmt = $this->connection->query($sql);
//
//        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
//            $totalRecords++;
//            $bufferedRecords++;
//
//            if($row['action'] == 'current') {
//                $buffer[] = new InsertRecordAction($this->connection, $table->name, $this->migration, false);
//            } else {
//                // TODO: $buffer[] = new DeleteRecordAction()
//            }
//
//            if($bufferedRecords >= DataDifference::BufferMaxRecords) {
//                $this->flushBuffer($buffer);
//                $bufferedRecords = 0;
//
//                unset($buffer);
//                $buffer = [];
//            }
//        }
//
//        if($bufferedRecords > 0 && !empty($buffer)) {
//            $this->flushBuffer($buffer);
//            unset($buffer);
//        }
//
//        return $totalRecords;

        return 0;
    }

    /**
     * @param TableData $currentTable
     * @param array $fields
     * @return int
     */
    protected function generateChangesWithoutPrimaryKey(TableData $currentTable, array $fields)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        $oldTableName = Configuration::getInstance()->getTempTablePrefix() . $currentTable->name;
        $fieldsList = implode(',', array_keys($fields));
        $sql = 'SELECT \'current\' as `___action`, ' . $currentTable->name . '.* FROM ' . $currentTable->name . ' WHERE ROW(' . $fieldsList . ') NOT IN (SELECT * FROM ' . $oldTableName . ') UNION ALL SELECT \'old\' as `___action`, ' . $oldTableName . '.* FROM ' . $oldTableName . ' WHERE ROW(' . $fieldsList . ') NOT IN (SELECT * FROM ' . $currentTable->name . ')';
        $stmt = $this->connection->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $totalRecords++;
            $bufferedRecords++;
            $action = $row['___action'];

            unset($row['___action']);
            if ($action == 'current') {
                $buffer[] = new InsertRecordAction($this->connection,
                    $currentTable->name,
                    $row,
                    $this->migration,
                    false);
            } else {
                $buffer[] = new DeleteRecordAction($this->connection,
                    $currentTable->name,
                    $row,
                    $this->migration);
            }

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
     * @param array $fields
     * @return string
     */
    private function getPrimaryKey(array $fields)
    {
        /**
         * @var FieldData $field
         */
        foreach ($fields as $field) {
            if ($field->isPrimaryKey()) {
                return $field->name;
            }
        }
        return false;
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
        print_r($code);
        $this->migration->appendMigrationFile($code);
        unset($code);
    }
}