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