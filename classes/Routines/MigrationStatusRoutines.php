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
        $this->showCurrentStatus();
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