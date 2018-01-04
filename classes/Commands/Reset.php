<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Core\Command;
use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;
use Voyage\Core\Migrations;
use Voyage\Routines\DatabaseRoutines;
use Voyage\Routines\MigrationRoutines;

/**
 * Class Reset
 * @package Voyage\Commands
 */
class Reset extends Command implements EnvironmentControllerInterface
{
    /**
     * Reset constructor.
     */
    public function __construct()
    {
        $this->setName('reset');
        $this->setDescription('Reset database state to the first migration and then re-apply all migrations.');

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);

            $this->displayAppName();
            $this->checkIntegrity($output);
            $this->initCurrentEnvironment();

            $this->reset();
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    /**
     * @param Migrations $migrations
     */
    protected function checkFirstTimeImport(Migrations $migrations)
    {
        if ($migrations->isInitialImport()) {
            $tablesToRecreate = $migrations->getTablesToRecreateFromMigrations();

            if (!empty($tablesToRecreate)) {
                $databaseRoutines = new DatabaseRoutines($this);
                foreach ($tablesToRecreate as $tableName) {
                    $databaseRoutines->dropTable($tableName);
                }
            }
        }
    }

    private function reset()
    {
        // Un-register all migrations
        $migrationRoutines = new MigrationRoutines($this);
        $migrationRoutines->unRegisterAllMigrations();

        // Check if we're applying for the first time
        $migrations = new Migrations($this);
        $this->checkFirstTimeImport($migrations);
        Configuration::getInstance()->lock();

        $notAppliedMigrations = $migrations->getNotAppliedMigrations();

        if (!empty($notAppliedMigrations)) {
            foreach ($notAppliedMigrations as $migrationData) {
                $migrationId = $migrationData['id'];
                $this->info($migrationId);

                $migration = new Migration($this);
                $migration->setId($migrationId);
                $migration->apply();
                unset($migration);

                $this->report('Applied successfully.');
                $this->report('');
            }
        }

        $this->report('Reset has been successfully done.');
    }
}