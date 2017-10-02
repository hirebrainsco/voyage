<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\DatabaseConnection;
use Voyage\MigrationActions\ActionsFormatter;
use Voyage\MigrationActions\CreateTableAction;
use Voyage\MigrationActions\DropTableAction;

/**
 * Class TablesDifference
 * @package Voyage\Routines
 */
class TablesDifference
{
    /**
     * @var DatabaseConnection
     */
    private $connection;
    /**
     * @var array
     */
    private $comparisonTables;

    /**
     * TablesDifference constructor.
     * @param DatabaseConnection $connection
     * @param array $comparisonTables
     */
    public function __construct(DatabaseConnection $connection, array $comparisonTables)
    {
        $this->comparisonTables = $comparisonTables;
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getDifference()
    {
        $code = '';
        if (empty($this->comparisonTables) || !isset($this->comparisonTables['current']) || !isset($this->comparisonTables['old'])) {
            return $code;
        }

        $difference = array_merge($this->getTablesToCreate(), $this->getTablesToDrop());
        if (empty($difference)) {
            return $difference;
        }

        $code .= '# Tables List' . PHP_EOL;
        $code .= '# ' . str_repeat('-', 78) . PHP_EOL;

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