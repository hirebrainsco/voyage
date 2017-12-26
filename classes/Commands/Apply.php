<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Voyage\Core\Command;
use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;
use Voyage\Core\Migrations;
use Voyage\Routines\DatabaseRoutines;

/**
 * Class Apply
 * @package Voyage\Commands
 */
class Apply extends Command implements EnvironmentControllerInterface
{
    /**
     * Apply constructor.
     */
    public function __construct()
    {
        $this->setName('apply');
        $this->setDescription('Apply all migrations that hasn\'t been applied to the current database yet.');

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);

            $this->displayAppName();
            $this->checkIntegrity($output);
            $this->initCurrentEnvironment();

            $databaseRoutines = new DatabaseRoutines($this);
            $databaseRoutines->createTable(); // Create voyage migrations table.
            unset($databaseRoutines);

            $this->apply();
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    protected function apply()
    {
        $migrations = new Migrations($this);
        $allMigrations = $migrations->getAllMigrations();
        $notAppliedMigrations = $migrations->getNotAppliedMigrations();

        if (empty($notAppliedMigrations)) {
            $this->info('There\'re no not applied migrations.');
            return;
        }

        // Check if we're applying for the first time
        $this->checkFirstTimeImport($migrations);

        Configuration::getInstance()->lock();

        // Check if we need rollback
        $rolledBackMigrationIds = $this->rollbackIfNeeded($allMigrations);
        $notAppliedMigrations = $migrations->getNotAppliedMigrations();

        if (empty($notAppliedMigrations)) {
            $this->info('There\'re no not applied migrations.');
            return;
        }

        foreach ($notAppliedMigrations as $migrationData) {
            $migrationId = $migrationData['id'];
            $canReport = !in_array($migrationId, $rolledBackMigrationIds);

            if ($canReport) {
                $this->info($migrationId);
            }

            $migration = new Migration($this);
            $migration->setId($migrationId);
            $migration->apply();
            unset($migration);

            if ($canReport) {
                $this->report('Applied successfully.');
                $this->report('');
            }
        }
    }

    protected function checkFirstTimeImport(Migrations $migrations)
    {
        if ($migrations->isInitialImport()) {
            $this->info('Initial data import. Checking if database contains tables that should be recreated from migration files.');
            $tablesToRecreate = $migrations->getTablesToRecreateFromMigrations();

            if (!empty($tablesToRecreate)) {
                $message = PHP_EOL . '<options=bold>We\'ve found tables in database which must be recreated from migration files.</>' . PHP_EOL;
                $message .= 'This usually happens if you create a full dump of a database on another server and then apply migrations.' . PHP_EOL . PHP_EOL;
                $message .= 'Here is a list of those tables:' . PHP_EOL;

                foreach ($tablesToRecreate as $tableName) {
                    $message .= ' - ' . $tableName . PHP_EOL;
                }

                $message .= PHP_EOL . 'These tables needs to be dropped before we can apply them from migration files.' . PHP_EOL;
                $this->report($message);

                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('Press [Y/y] to drop these tables and continue; or any other key to stop execution.' . PHP_EOL, false);

                if (!$helper->ask($this->getInput(), $this->getOutput(), $question)) {
                    $this->report('Aborted by user.');
                    exit();
                }

                $databaseRoutines = new DatabaseRoutines($this);
                foreach ($tablesToRecreate as $tableName) {
                    $databaseRoutines->dropTable($tableName);
                }
            }
        }
    }

    protected function rollbackIfNeeded(array $migrations)
    {
        foreach ($migrations as $migrationId => $migration) {
            if (!$migration['applied']) {
                break;
            }

            unset($migrations[$migrationId]);
        }

        if (empty($migrations)) {
            return [];
        }

        $migrations = array_reverse($migrations, true);
        $rolledBackMigrationIds = [];

        foreach ($migrations as $migrationId => $migration) {
            if (!$migration['applied']) {
                continue;
            }

            $migration = new Migration($this);
            $migration->setId($migrationId);
            $migration->rollback(false, true);
            unset($migration);
            $rolledBackMigrationIds[] = $migrationId;
        }

        return $rolledBackMigrationIds;
    }
}