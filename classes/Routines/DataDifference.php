<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;

/**
 * Class DataDifference
 * @package Voyage\Routines
 */
class DataDifference extends DifferenceRoutines
{
    /**
     * @var Migration
     */
    private $migration;

    public function __construct(EnvironmentControllerInterface $environmentController, array $comparisonTables, Migration $migration)
    {
        parent::__construct($environmentController, $comparisonTables);
    }

    /**
     * @return bool
     */
    public function getDifference()
    {
        return true;
    }
}