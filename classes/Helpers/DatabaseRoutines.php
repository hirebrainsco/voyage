<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Core\DatabaseConnection;
use Voyage\Core\InputOutputInterface;
use Voyage\Core\Routines;

/**
 * Class DatabaseRoutines
 * @package Voyage\Helpers
 */
class DatabaseRoutines extends Routines
{
    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

    /**
     * DatabaseRoutines constructor.
     * @param InputOutputInterface $reporter
     * @param DatabaseConnection $databaseConnection
     */
    public function __construct(InputOutputInterface $reporter, DatabaseConnection $databaseConnection)
    {
        parent::__construct($reporter);
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * Remove voyage table with a list of migrations if it exists.
     */
    public function clean()
    {
        $sql = sprintf('DROP TABLE IF EXISTS `%s`', $this->getConfiguration()->getMigrationsTableName());
        $this->databaseConnection->exec($sql);
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
            $this->databaseConnection->exec($sql);
            $this->getReporter()->report('Created migrations table.');
        } catch (\Exception $e) {
            $this->getReporter()->fatalError($e->getMessage());
        }
    }
}