<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\FieldData;
use Voyage\Core\Migration;

class InsertRecordAction extends RecordAction
{
    public static $insertQueryTemplate = '';

    /**
     * @var bool
     */
    protected $isNewTable = false;

    public function getApply()
    {
        $this->prepareStaticData();
        $sql = self::$insertQueryTemplate;

        $i = 0;
        foreach ($this->row as $column) {
            $sql .= $this->prepareValue($column);
            if ($i != self::$totalFields - 1) {
                $sql .= ', ';
            }

            $i++;
        }

        $sql .= ');';
        return $this->prepareTableNameForExport($sql);
    }

    public function getRollback()
    {
        if ($this->isNewTable) {
            return false;
        }

        $this->prepareStaticData();
        $sql = 'DELETE FROM `' . $this->tableName . '` WHERE ';

        if (!empty(self::$primaryKey)) {
            $sql .= '`' . self::$primaryKey . '`=\'' . $this->row[self::$primaryKey] . '\';';
        } else {
            // Bad thing.. we don't have a primary key, so we'll have to compare by all fields.
            /**
             * @var FieldData $field
             */
            $i = 0;
            foreach (self::$fields as $field) {
                $sql .= '`' . $field->name . '`=' . $this->prepareValue($this->row[$field->name]);
                if ($i != self::$totalFields - 1) {
                    $sql .= ' AND ';
                }

                $i++;
            }

            $sql .= ';';
        }

        return $this->prepareTableNameForExport($sql);
    }

    /**
     * Prepare static cache for faster processing of multiple records.
     */
    protected function prepareStaticData()
    {
        /**
         * @var FieldData $field
         * @var int $i
         */

        if (self::$staticDataForTable == $this->tableName) {
            return;
        }

        self::$insertQueryTemplate = 'INSERT INTO `' . $this->tableName . '` (';
        parent::prepareStaticData();

        $i = 0;
        foreach (self::$fields as $field) {
            self::$insertQueryTemplate .= $field->name;

            if ($i < self::$totalFields - 1) {
                self::$insertQueryTemplate .= ', ';
            }

            $i++;
        }

        self::$insertQueryTemplate .= ') VALUES (';
    }

    /**
     * InsertRecordAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     * @param array $row
     * @param Migration $migration
     * @param bool $isNewTable
     */
    public function __construct(DatabaseConnection $connection, $tableName, array $row, Migration $migration, $isNewTable = false)
    {
        parent::__construct($connection, $tableName, $row, $migration);
        $this->isNewTable = $isNewTable;
    }
}