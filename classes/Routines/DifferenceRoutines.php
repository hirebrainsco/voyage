<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\EnvironmentControllerInterface;

abstract class DifferenceRoutines
{
    /**
     * @var DatabaseConnection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $comparisonTables;

    /**
     * @var EnvironmentControllerInterface
     */
    protected $environmentController;

    /**
     * TablesDifference constructor.
     * @param EnvironmentControllerInterface $environmentController
     * @param array $comparisonTables
     */
    public function __construct(EnvironmentControllerInterface $environmentController, array $comparisonTables)
    {
        $this->comparisonTables = $comparisonTables;
        $this->environmentController = $environmentController;
        $this->connection = $environmentController->getDatabaseConnection();
    }

    /**
     * @throws \Exception
     */
    public function getDifference()
    {
        throw new \Exception('Not implemented!');
    }

    /**
     * @return bool
     */
    protected function hasData()
    {
        return !empty($this->comparisonTables) && isset($this->comparisonTables['current']) && isset($this->comparisonTables['old']);
    }

    /**
     * @param $caption
     * @return string
     */
    protected function getHeader($caption)
    {
        $code = '# ' . $caption . PHP_EOL;
        $code .= '# ' . str_repeat('-', 78) . PHP_EOL;

        return $code;
    }
}