<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Voyage\Core\Configuration;
use Voyage\Core\Migration;
use Voyage\Core\MigrationParser;
use Voyage\Core\Migrations;

/**
 * Trait MigrationStatusRoutines
 * @package Voyage\Routines
 */
trait MigrationStatusRoutines
{
    /**
     * Show current status.
     */
    public function status()
    {
        $this->migrations = new Migrations($this->getSender());

        Configuration::getInstance()->lock();
        $this->showCurrentMigrationInfo();
        $this->showNotAppliedMigrations();
        $this->checkMissingMigrations();
        $this->showCurrentStatus();
    }

    private function checkMissingMigrations()
    {
        $missingFiles = 0;

        $migrations = new Migrations($this->getSender());
        $appliedMigrations = $migrations->getAppliedMigrations();

        if (empty($appliedMigrations)) {
            return;
        }

        $migrationsPath = Configuration::getInstance()->getPathToMigrations();
        foreach ($appliedMigrations as $migration) {
            $pathToMigrationFile = $migrationsPath . '/' . $migration['id'] . '.mgr';
            if (!file_exists($pathToMigrationFile)) {
                $missingFiles++;
            }
        }

        if ($missingFiles > 0) {
            $this->getSender()->report('');
            $this->getSender()->report('<error>Missing Migration Files</error>');
            $this->getSender()->report('Some migration files are missing. This usually can happen if you switch to a different version in GIT where some migrations are not available yet.');
            $this->getSender()->report('You should run "voyage reset", this command will rollback database to it\'s initial state and then will apply all available migration files.');
            $this->getSender()->report('Run "voyage list" to view the list of missing migration files.');
            $this->getSender()->report('');
            exit(1);
        }
    }

    /**
     * Compute changes and show current status.
     */
    private function showCurrentStatus()
    {
        $this->getSender()->report('<options=bold>Current Status</>');
        $this->databaseRoutines = new DatabaseRoutines($this->getSender());
        $this->getSender()->report('Checking permissions.');
        $this->databaseRoutines->checkPermissions(); // Check if we have sufficient rights to modify database.
        $this->migrations->push(); // Push migrations to database (temp tables) for comparison.

        // Compare and generate migration.
        $difference = $this->dryRun();
        if (!empty($difference)) {
            $this->getSender()->warning('<fg=cyan>There are changes in database.</>');

            $helper = $this->getSender()->getHelper('question');
            $question = new ConfirmationQuestion('Would you like to view SQL code with a list of changes [Y/n]? ', true);

            if ($helper->ask($this->getSender()->getInput(), $this->getSender()->getOutput(), $question)) {
                $this->getSender()->warning('');
                $this->getSender()->warning('<fg=cyan>SQL Code:</>');
                $migrationContent = new MigrationParser();
                $migrationContent->setContents($difference);
                echo implode(';' . PHP_EOL, $migrationContent->getApply()) . ';';
                $this->getSender()->report('');
                unset($migrationContent);
            }

            $this->getSender()->report('');
        }

        $this->migrations->dropTemporaryTables();
    }

    /**
     * Show not applied migrations.
     */
    private function showNotAppliedMigrations()
    {
        $this->getSender()->report('<options=bold>Not Applied Migrations</>');
        $notAppliedMigrations = $this->migrations->getNotAppliedMigrations();
        if (empty($notAppliedMigrations)) {
            $this->getSender()->report('- There\'re no not applied migrations.');
        } else {
            $i = 0;
            foreach ($notAppliedMigrations as $migrationData) {
                if ($migrationData['applied'] === true) {
                    continue;
                }

                $migration = $migrationData['id'];
                $i++;
                $this->getSender()->report(' ' . $i . '. ' . $migration);
            }
        }
        $this->getSender()->report('');
    }

    /**
     * Show information about the current environment and migration.
     */
    private function showCurrentMigrationInfo()
    {
        $currentEnvironment = $this->getSender()->getEnvironment()->getName();
        $currentMigration = $this->migrations->getLatestAppliedMigrationData();

        $this->getSender()->report('');
        $this->getSender()->report('Environment: ' . $currentEnvironment);

        if (empty($currentMigration)) {
            $this->getSender()->report('Current Migration: none');
        } else {
            $this->getSender()->report(sprintf('Current Migration: <fg=green>%s</>', $currentMigration['id']));
            $this->getSender()->report(sprintf('Created On: %s', date('d M Y', $currentMigration['ts'])));
            $this->getSender()->report('');
        }
    }

    /**
     * Compute changes without creating a new migration.
     * @return string
     */
    protected function dryRun()
    {
        $migration = new Migration($this->getSender());
        $hasChanges = $migration->generate($this->getComparisonTables());
        $result = '';

        if ($hasChanges) {
            $result = $migration->getFileContents();
            $migration->removeMigrationFile();
        }

        unset($migration);
        return $result;
    }
}