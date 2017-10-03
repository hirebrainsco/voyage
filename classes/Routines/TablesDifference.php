<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\MigrationActions\ActionsFormatter;
use Voyage\MigrationActions\CreateTableAction;
use Voyage\MigrationActions\DropTableAction;

/**
 * Class TablesDifference
 * @package Voyage\Routines
 */
class TablesDifference extends DifferenceRoutines
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

        $difference = array_merge($this->getTablesToCreate(), $this->getTablesToDrop());
        if (empty($difference)) {
            return $code;
        }

        $code .= $this->getHeader('Tables List');

        $formatter = new ActionsFormatter($difference);
        $code .= $formatter->generate();
        unset($difference, $formatter);

        return $code;
    }

    /**
     * @return array
     */
    protected function getTablesToCreate()
    {
        $difference = [];
        $tables = array_diff_key($this->comparisonTables['current'], $this->comparisonTables['old']);
        if (empty($tables)) {
            return $difference;
        }

        foreach ($tables as $table) {
            $difference[] = new CreateTableAction($this->connection, $table->name);
        }

        return $difference;
    }

    /**
     * @return array
     */
    protected function getTablesToDrop()
    {
        $difference = [];
        $tables = array_diff_key($this->comparisonTables['old'], $this->comparisonTables['current']);
        if (empty($tables)) {
            return $difference;
        }

        foreach ($tables as $table) {
            $difference[] = new DropTableAction($this->connection, $table->name);
        }

        return $difference;
    }
}