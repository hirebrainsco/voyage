<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;


use Voyage\Core\Migration;
use Voyage\Core\Migrations;

/**
 * Trait MigrationMakeRoutines
 * @package Voyage\Routines
 */
trait MigrationMakeRoutines
{
    /**
     * Compute changes and make a new migration.
     */
    public function make()
    {
        $this->databaseRoutines = new DatabaseRoutines($this->getSender());
        $this->migrations = new Migrations($this->getSender());

        $this->databaseRoutines->checkPermissions(); // Check if we have sufficient rights to modify database.
        $this->migrations->push(); // Push migrations to database (temp tables) for comparison.

        // Compare and generate migration.
        $this->compareAndGenerateMigration();
        $this->migrations->dropTemporaryTables();
    }

    /**
     * Compare and generate a new migration file containing changes in database in current environment.
     */
    protected function compareAndGenerateMigration()
    {
        $migration = new Migration($this->getSender());
        $hasChanges = $migration->generate($this->getComparisonTables());

        if ($hasChanges) {
            $migration->setName($this->promptMigrationName());
            $migration->saveHeader();
            $migration->recordMigration();
            $migration->printSuccess();
        }

        unset($migration);
    }
}