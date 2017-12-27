<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Configuration\Ignore;
use Voyage\Configuration\IgnoreDataRowRule;
use Voyage\MigrationActions\DeleteRecordAction;
use Voyage\MigrationActions\InsertRecordAction;
use Voyage\Core\Configuration;
use Voyage\Core\TableData;

trait DataDifferenceWithoutPrimaryKey
{

    /**
     * @param TableData $currentTable
     * @param array $fields
     * @param array $rowsIgnoreList
     * @param array $fieldsIgnoreList
     * @return int
     */
    protected function generateChangesWithoutPrimaryKey(TableData $currentTable, array $fields, array $rowsIgnoreList, array $fieldsIgnoreList)
    {
        $buffer = [];
        $totalRecords = $bufferedRecords = 0;

        $oldTableName = Configuration::getInstance()->getTempTablePrefix() . $currentTable->name;
        $fieldsList = '`' . implode('`,`', array_keys($fields)) . '`';
        $sql = 'SELECT \'current\' as `___action`, `' . $currentTable->name . '`.* FROM `' . $currentTable->name . '` WHERE ROW(' . $fieldsList . ') NOT IN (SELECT * FROM `' . $oldTableName . '`) UNION ALL SELECT \'old\' as `___action`, `' . $oldTableName . '`.* FROM `' . $oldTableName . '` WHERE ROW(' . $fieldsList . ') NOT IN (SELECT * FROM `' . $currentTable->name . '`)';
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
}