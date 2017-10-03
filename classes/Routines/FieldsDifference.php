<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\Configuration;
use Voyage\Core\FieldData;
use Voyage\MigrationActions\ActionsFormatter;
use Voyage\MigrationActions\AddFieldAction;
use Voyage\MigrationActions\ChangeFieldAction;
use Voyage\MigrationActions\DropFieldAction;

class FieldsDifference extends DifferenceRoutines
{
    /**
     * @return string
     */
    public function getDifference()
    {
        $code = '';
        if (!$this->hasData()) {
            return $code;
        }

        $difference = $this->getFieldsDifference();
        if (empty($difference)) {
            return $code;
        }

        $code .= $this->getHeader('Fields List');

        $formatter = new ActionsFormatter($difference);
        $code .= $formatter->generate();
        unset($difference, $formatter);

        return $code;
    }

    /**
     * @return array
     */
    protected function getFieldsDifference()
    {
        $difference = [];
        $databaseRoutines = new DatabaseRoutines($this->environmentController);

        /**
         * @var \Voyage\Core\TableData $table
         */
        foreach ($this->comparisonTables['current'] as $table) {
            if (!isset($this->comparisonTables['old'][$table->name])) {
                // Skip new tables.
                continue;
            }

            $currentFields = $databaseRoutines->getTableFields($table->name);
            $oldFields = $databaseRoutines->getTableFields(Configuration::getInstance()->getTempTablePrefix() . $table->name);

            if (empty($currentFields) && empty($oldFields)) {
                continue;
            }

            $addFields = array_diff_key($currentFields, $oldFields);
            $dropFields = array_diff_key($oldFields, $currentFields);
            $changeFields = array_intersect_key($currentFields, $oldFields);

            if (!empty($addFields)) {
                /**
                 * @var FieldData $field
                 */
                foreach ($addFields as $field) {
                    $difference[] = new AddFieldAction($this->connection, $table->name, $field);
                }
            }
            unset($addFields);

            if (!empty($dropFields)) {
                /**
                 * @var FieldData $field
                 */
                foreach ($dropFields as $field) {
                    $difference[] = new DropFieldAction($this->connection, $table->name, $field);
                }
            }
            unset($dropFields);

            if (!empty($changeFields)) {
                /**
                 * @var FieldData $field
                 */
                foreach ($changeFields as $field) {
                    if (true === $this->areFieldsDifferent($field, $oldFields[$field->name])) {
                        $difference[] = new ChangeFieldAction($this->connection, $table->name, $field, $oldFields[$field->name]);
                    }
                }
            }

            unset($changeFields);
            unset($currentFields, $oldFields);
        }

        return $difference;
    }

    /**
     * Compare two fields and check whether they're different.
     * @param FieldData $left
     * @param FieldData $right
     * @return bool
     */
    private function areFieldsDifferent(FieldData $left, FieldData $right)
    {
        return $left->type != $right->type || $left->default != $right->default ||
            $left->nullValue != $right->nullValue || $left->extra != $right->extra || $left->key != $right->key;
    }
}