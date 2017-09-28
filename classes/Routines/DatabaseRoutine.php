<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\InputOutputInterface;
use Voyage\Core\Routine;

/**
 * Class DatabaseRoutines
 * @package Voyage\Routines
 */
class DatabaseRoutine extends Routine
{
    /**
     * Remove voyage table with a list of migrations if it exists.
     */
    public function clean()
    {
        $sql = sprintf('DROP TABLE IF EXISTS `%s`', $this->getConfiguration()->getMigrationsTableName());
        $this->getDatabaseConnection()->exec($sql);
    }

    /**
     * Create voyage table
     */
    public function createTable()
    {
        $sql = "
            CREATE TABLE %s (
              `id` VARCHAR(15) NOT NULL DEFAULT '',
              `name` VARCHAR(255) NOT NULL DEFAULT '', 
              `ts` INT(11) NOT NULL DEFAULT 0,
              UNIQUE key (`id`)
            ) ENGINE=MyISAM
        ";

        $sql = sprintf($sql, $this->getConfiguration()->getMigrationsTableName());

        try {
            $this->getDatabaseConnection()->exec($sql);
            $this->getSender()->report('Created migrations table.');
        } catch (\Exception $e) {
            $this->getSender()->fatalError($e->getMessage());
        }
    }
}