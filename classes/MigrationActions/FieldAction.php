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
 * Class FieldAction
 * @package Voyage\MigrationActions
 */
abstract class FieldAction extends MigrationAction
{
    /**
     * @var FieldData
     */
    protected $fieldData;

    /**
     * FieldAction constructor.
     * @param DatabaseConnection $connection
     * @param $tableName
     * @param FieldData $fieldData
     */
    public function __construct(DatabaseConnection $connection, $tableName, FieldData $fieldData)
    {
        $this->fieldData = $fieldData;
        parent::__construct($connection, $tableName);
    }

    /**
     * @param FieldData $field
     * @param FieldData|null $oldField
     * @return string
     */
    protected function getAlterIndex(FieldData $field, FieldData $oldField = null)
    {
        $sql = '';
        if (!is_null($oldField) && !empty($oldField->key) && $field->key != $oldField->key) {
            $sql .= 'ALTER TABLE `' . $this->tableName . '` DROP INDEX `' . $oldField->name . '`;' . PHP_EOL;
        }

        $sql .= 'ALTER TABLE `' . $this->tableName . '` ';
        if (empty($field->key)) {
            $sql .= 'DROP INDEX `' . $field->name . '`';
        } else {
            if ($field->key == 'MUL') {
                if ($this->isTextOrBlobType($field)) {
                    $sql .= 'ADD FULLTEXT INDEX `' . $this->fieldData->name . '`(`' . $this->fieldData->name . '`)';
                } else {
                    $sql .= 'ADD INDEX `' . $this->fieldData->name . '`(`' . $this->fieldData->name . '`)';
                }
            } else if ($field->key == 'PRI') {
                $sql .= 'ADD PRIMARY KEY(`' . $this->fieldData->name . '`)';
            } else if ($field->key == 'UNI') {
                $sql .= 'ADD UNIQUE KEY(`' . $this->fieldData->name . '`)';
            } else {
                return '';
            }
        }

        $sql .= ';';
        return $sql;
    }

    /**
     * @param FieldData $fieldData
     * @return bool
     */
    protected function isTextOrBlobType(FieldData $fieldData)
    {
        $type = strtoupper($fieldData->type);
        return false === strpos($type, 'ENUM') && (false !== strpos($type, 'TEXT') || false !== strpos($type, 'BLOB'));
    }

    /**
     * @return string
     */
    protected function getAdd()
    {
        $sql = sprintf('ALTER TABLE `%s` ADD `%s` %s',
            $this->tableName,
            $this->fieldData->name,
            $this->getFieldAlter($this->fieldData));

        $sql = trim($sql);
        $sql .= ';';

        if ($this->fieldData->key != '') {
            $sql .= PHP_EOL;
            $sql .= $this->getAlterIndex($this->fieldData);
        }

        return $this->prepareTableNameForExport($sql);
    }

    /**
     * @param FieldData $field
     * @return string
     */
    public function getFieldAlter(FieldData $field)
    {
        if ($field->nullValue == 'NO') {
            $defVal = 'NOT NULL ';
        } else {
            $defVal = '';
        }

        if ($field->default == 'NULL') {
            $defVal .= 'DEFAULT NULL';
        } else {
            $defByType = $this->getDefaultValueByType($field);
            if ($defByType !== '') {
                $defVal .= "DEFAULT " . $defByType;
            }
        }

        $sql = sprintf('%s %s %s',
            $field->type,
            $defVal,
            strtoupper($field->extra));

        if ($field->key == 'PRI') {
            $sql .= ' PRIMARY KEY';
        }

        return $sql;
    }

    /**
     * @return string
     */
    protected function getDrop()
    {
        $sql = sprintf('ALTER TABLE `%s` DROP `%s`;', $this->tableName, $this->fieldData->name);
        return $this->prepareTableNameForExport($sql);
    }

    /**
     * @param FieldData $field
     * @return float|int|string
     */
    protected function getDefaultValueByType(FieldData $field)
    {
        if ($this->isIntType()) {
            if ($field->default == '') {
                return '';
            }

            return intval($field->default);
        }

        if ($this->isDecimalType()) {
            if ($field->default == '') {
                return '';
            }
            return floatval($field->default);
        }

        return "'" . str_replace("'", "\\'", $field->default) . "'";
    }

    /**
     * @return bool
     */
    private function isIntType()
    {
        return strpos(strtoupper($this->fieldData->type), 'INT') !== false;
    }

    /**
     * @return bool
     */
    private function isDecimalType()
    {
        $type = strtoupper($this->fieldData->type);
        return strpos($type, 'DECIMAL') !== false ||
            strpos($type, 'NUMERIC') !== false ||
            strpos($type, 'FLOAT') !== false ||
            strpos($type, 'REAL') !== false ||
            strpos($type, 'DOUBLE') !== false;
    }
}