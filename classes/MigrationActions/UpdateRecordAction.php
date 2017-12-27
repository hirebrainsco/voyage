<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\Migration;

/**
 * Class UpdateRecordAction
 * @package Voyage\MigrationActions
 */
class UpdateRecordAction extends RecordAction
{
    /**
     * @var array
     */
    private $oldRow = [];

    public function canBeIgnored()
    {
        $this->prepareStaticData();
        $dataToUpdate = $this->getDataToUpdate($this->row, $this->oldRow);
        if (empty($dataToUpdate)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getApply()
    {
        $this->prepareStaticData();

        $data = $this->getDataToUpdate($this->row, $this->oldRow);
        if (empty($data)) {
            return false;
        }

        $sql = $this->getSqlUpdate();
        $sql .= implode(', ', $data);
        $sql .= $this->getSqlWhere();
        $sql .= ';';

        return $this->prepareTableNameForExport($sql, 'UPDATE ');
    }

    /**
     * @return bool|string
     */
    public function getRollback()
    {
        $this->prepareStaticData();

        $data = $this->getDataToUpdate($this->oldRow, $this->row);
        if (empty($data)) {
            return false;
        }

        $sql = $this->getSqlUpdate();
        $sql .= implode(', ', $data);
        $sql .= $this->getSqlWhere();
        $sql .= ';';

        return $this->prepareTableNameForExport($sql, 'UPDATE ');
    }

    /**
     * @return string
     */
    private function getSqlUpdate()
    {
        $sql = 'UPDATE `' . $this->tableName . '` SET ';
        return $sql;
    }

    /**
     * @return string
     */
    private function getSqlWhere()
    {
        $sql = ' WHERE `' . self::$primaryKey . '`=' . $this->prepareValue($this->row[self::$primaryKey]);
        return $sql;
    }

    /**
     * @param array $rowA
     * @param array $rowB
     * @return array
     */
    private function getDataToUpdate(array $rowA, array $rowB)
    {
        $dataToUpdate = [];
        foreach ($rowA as $key => $value) {
            if (in_array($key, $this->ignoreFields)) {
                continue;
            }

            $valueA = $this->prepareValue($value);
            $valueB = $this->prepareValue($rowB[$key]);

            if ($valueA != $valueB) {
                $dataToUpdate[] = '`' . $key . '`=' . $valueA;
            }
        }

        return $dataToUpdate;
    }

    /**
     * UpdateRecordAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     * @param array $row
     * @param array $oldRow
     * @param Migration $migration
     */
    public function __construct(DatabaseConnection $connection, $tableName, array $row, array $oldRow, Migration $migration)
    {
        parent::__construct($connection, $tableName, $row, $migration);
        $this->oldRow = $oldRow;
    }
}