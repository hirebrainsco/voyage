<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\MigrationActions\DeleteRecordAction;
use Voyage\MigrationActions\InsertRecordAction;
use Voyage\MigrationActions\UpdateRecordAction;
use Voyage\Core\Configuration;
use Voyage\Core\TableData;
use Voyage\Core\FieldData;

trait DataDifferenceWithPrimaryKey
{
    /**
     * @param TableData $currentTable
     * @param array $fields
     * @param $primaryKey
     * @param array $rowsIgnoreList
     * @param array $fieldsIgnoreList
     * @return int
     */
    protected function generateInserts(TableData $currentTable, array $fields, $primaryKey, array $rowsIgnoreList, array $fieldsIgnoreList)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        $oldTableName = Configuration::getInstance()->getTempTablePrefix() . $currentTable->name;
        $sql = 'SELECT `' . $currentTable->name . '`.* FROM `' . $currentTable->name . '` WHERE `' . $currentTable->name . '`.`' . $primaryKey . '` NOT IN (SELECT `' . $primaryKey . '` FROM `' . $oldTableName . '`)';
        $stmt = $this->connection->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!empty($rowsIgnoreList)) {
                /**
                 * @var IgnoreDataRowRule $ignoreRule
                 */
                $shouldIgnore = false;
                foreach ($rowsIgnoreList as $ignoreRule) {
                    if ($ignoreRule->shouldIgnore($row)) {
                        $shouldIgnore = true;
                        break;
                    }
                }

                if ($shouldIgnore) {
                    continue;
                }
            }

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
     * @param array $ignoreList
     * @return int
     */
    protected function generateDeletes(TableData $currentTable, array $fields, $primaryKey, array $ignoreList)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        $oldTableName = Configuration::getInstance()->getTempTablePrefix() . $currentTable->name;
        $sql = 'SELECT `' . $oldTableName . '`.* FROM `' . $oldTableName . '` WHERE `' . $oldTableName . '`.`' . $primaryKey . '` NOT IN (SELECT `' . $primaryKey . '` FROM `' . $currentTable->name . '`)';
        $stmt = $this->connection->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!empty($ignoreList)) {
                /**
                 * @var IgnoreDataRowRule $ignoreRule
                 */
                $shouldIgnore = false;
                foreach ($ignoreList as $ignoreRule) {
                    if ($ignoreRule->shouldIgnore($row)) {
                        $shouldIgnore = true;
                        break;
                    }
                }

                if ($shouldIgnore) {
                    continue;
                }
            }

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
     * @param array $rowsIgnoreList
     * @param array $fieldsIgnoreList
     * @return int
     */
    protected function generateUpdates(TableData $table, array $fields, $primaryKey, array $rowsIgnoreList, array $fieldsIgnoreList)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        // Get fields of the old table
        // Compare them
        // If they are different then apply changes to the old table so that it's the same as the new one

        $fieldsList = $this->getCommaSeparatedFields($fields);
        $oldTableName = Configuration::getInstance()->getTempTablePrefix() . $table->name;

        $rowWhere = $fieldsList;
        if (sizeof($fields) > 1) {
            $rowWhere = 'ROW(' . $rowWhere . ')';
        }

        $sql = 'SELECT `' . $table->name . '`.* FROM `' . $table->name . '` WHERE ' . $rowWhere . ' NOT IN (SELECT * FROM `' . $oldTableName . '`) AND `' . $table->name . '`.`' . $primaryKey . '` IN (SELECT `' . $primaryKey . '` FROM `' . $oldTableName . '`);';
        $stmt = $this->connection->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!empty($rowsIgnoreList)) {
                /**
                 * @var IgnoreDataRowRule $ignoreRule
                 */
                $shouldIgnore = false;
                foreach ($rowsIgnoreList as $ignoreRule) {
                    if ($ignoreRule->shouldIgnore($row)) {
                        $shouldIgnore = true;
                        break;
                    }
                }

                if ($shouldIgnore) {
                    continue;
                }
            }

            $bufferedRecords++;

            $oldRecsSql = 'SELECT * FROM `' . $oldTableName . '` WHERE `' . $primaryKey . '`=\'' . $row[$primaryKey] . '\'';
            $oldRecsStmt = $this->connection->query($oldRecsSql);
            $oldRow = $oldRecsStmt->fetch(\PDO::FETCH_ASSOC);
            unset($oldRecsStmt);

            $action = new UpdateRecordAction($this->connection, $table->name, $row, $oldRow, $this->migration);
            if (!empty($fieldsIgnoreList)) {
                $action->setIgnoreFields($fieldsIgnoreList);
                if ($action->canBeIgnored()) {
                    unset($action);
                    continue;
                }
            }

            $buffer[] = $action;

            if ($bufferedRecords >= DataDifference::BufferMaxRecords) {
                $hasData = $this->flushBuffer($buffer);
                $bufferedRecords = 0;

                if ($hasData) {
                    $totalRecords++;
                }

                unset($buffer);
                $buffer = [];
            }
        }

        if ($bufferedRecords > 0 && !empty($buffer)) {
            $hasData = $this->flushBuffer($buffer);
            unset($buffer);

            if ($hasData) {
                $totalRecords++;
            }
        }

        return $totalRecords;
    }

    /**
     * @param TableData $table
     * @param array $ignoreList
     * @return int
     */
    protected function generateInsertsForNewTables(TableData $table, array $ignoreList)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        $sql = 'SELECT * FROM `' . $table->name . '`';
        $stmt = $this->connection->query($sql);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!empty($ignoreList)) {
                /**
                 * @var IgnoreDataRowRule $ignoreRule
                 */
                $shouldIgnore = false;
                foreach ($ignoreList as $ignoreRule) {
                    if ($ignoreRule->shouldIgnore($row)) {
                        $shouldIgnore = true;
                        break;
                    }
                }

                if ($shouldIgnore) {
                    continue;
                }
            }

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
     * @param array $fields
     * @return string
     */
    protected function getPrimaryKey(array $fields)
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
     * @param array $fields
     * @return string
     */
    private function getCommaSeparatedFields(array $fields)
    {
        $fieldsList = '';
        $i = 0;
        $sz = sizeof($fields);

        foreach ($fields as $field) {
            $fieldsList .= '`' . $field->name . '`';
            if ($i < $sz - 1) {
                $fieldsList .= ', ';
            }
            $i++;
        }

        return $fieldsList;
    }
}