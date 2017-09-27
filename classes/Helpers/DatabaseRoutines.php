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

class DatabaseRoutines extends Routines
{
    /**
     * @var DatabaseConnection
     */
    private $databaseConnection;

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
}