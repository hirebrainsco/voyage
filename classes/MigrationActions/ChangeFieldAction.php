<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\MigrationActions;


use Voyage\Core\DatabaseConnection;
use Voyage\Core\FieldData;

/**
 * Class ChangeFieldAction
 * @package Voyage\MigrationActions
 */
class ChangeFieldAction extends FieldAction
{
    /**
     * @var FieldData
     */
    protected $oldField;

    /**
     * ChangeFieldAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     * @param FieldData $fieldData
     * @param FieldData $oldField
     */
    public function __construct(DatabaseConnection $connection, $tableName, FieldData $fieldData, FieldData $oldField)
    {
        parent::__construct($connection, $tableName, $fieldData);
        $this->oldField = $oldField;
    }

    /**
     * @return string
     */
    public function getApply()
    {
        $sqlCurrent = $this->prepareTableNameForExport($this->getChangeForField($this->fieldData));
        $sqlOld = $this->prepareTableNameForExport($this->getChangeForField($this->oldField));

        if ($sqlCurrent == $sqlOld) {
            if ($this->fieldData->key != $this->oldField->key) {
                return $this->prepareTableNameForExport($this->getAlterIndex($this->fieldData, $this->oldField));
            }

            return '';
        } else {
            return $sqlCurrent;
        }
    }

    /**
     * @return string
     */
    public function getRollback()
    {
        $sqlCurrent = $this->prepareTableNameForExport($this->getChangeForField($this->fieldData));
        $sqlOld = $this->prepareTableNameForExport($this->getChangeForField($this->oldField));

        if ($sqlCurrent == $sqlOld) {
            if ($this->fieldData->key != $this->oldField->key) {
                return $this->prepareTableNameForExport($this->getAlterIndex($this->oldField, $this->oldField));
            }

            return '';
        } else {
            return $sqlOld;
        }
    }

    /**
     * @param FieldData $field
     * @return string
     */
    protected function getChangeForField(FieldData $field)
    {
        $sql = 'ALTER TABLE `' . $this->tableName . '` CHANGE `' . $field->name . '` `' . $field->name . '` ' . $this->getFieldAlter($field);
        $sql = trim($sql);
        $sql .= ';';

        return $sql;
    }
}