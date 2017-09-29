<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\Routine;

/**
 * Class Migrations
 * @package Voyage\Routines
 */
class Migrations extends Routine
{
    public function make()
    {
        $databaseRoutines = new DatabaseRoutines($this->getSender());
        $databaseRoutines->checkPermissions();
        $tables = $databaseRoutines->getTables();

        print_r($tables);
        unset($databaseRoutines);
    }
}