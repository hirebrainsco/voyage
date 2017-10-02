<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Symfony\Component\Console\Question\Question;
use Voyage\Core\Configuration;
use Voyage\Core\Migration;
use Voyage\Core\Migrations;
use Voyage\Core\Routine;
use Voyage\Core\TableData;

/**
 * Class Migrations
 * @package Voyage\Routines
 */
class MigrationRoutines extends Routine
{
    /**
     * @var Migrations
     */
    private $migrations;

    /**
     * @var DatabaseRoutines
     */
    private $databaseRoutines;

    public function make()
    {
        $this->databaseRoutines = new DatabaseRoutines($this->getSender());
        $this->migrations = new Migrations($this->getSender());

        $this->databaseRoutines->checkPermissions(); // Check if we have sufficient rights to modify database.
        $this->migrations->push(); // Push migrations to database (temp tables) for comparison.

        $this->compareAndGenerateMigration();
        // Perform comparison.
        // Generate migration.
    }

    protected function compareAndGenerateMigration()
    {
        $migration = new Migration($this->getSender());
        $migration->setName($this->promptMigrationName());
        $migration->generate($this->getComparisonTables());
    }

    protected function getComparisonTables()
    {
        $result = [
            'current' => [],
            'old' => []
        ];

        // Get list of current tables in database and pass them through ignore filter.
        $result['current'] = $this->databaseRoutines->getTables();

        // Get list of old tables
        $tmpTables = $this->migrations->getTemporaryTables();
        $oldTables = [];

        if (!empty($tmpTables)) {
            $prefix = Configuration::getInstance()->getTempTablePrefix();
            foreach ($tmpTables as $tableName) {
                $tableName = str_replace($prefix, '', $tableName);
                $ignoreData = isset($result['current'][$tableName]) ? $result['current'][$tableName]->ignoreData : false;
                $oldTables[$tableName] = new TableData($tableName, $ignoreData);
            }
        }

        $result['old'] = $oldTables;
        return $result;
    }

    /**
     * Prompt for migration's name.
     * @return mixed|string
     */
    protected function promptMigrationName()
    {
        $name = $this->getSender()->getInput()->getOption('name');
        if (!is_null($name)) {
            $name = trim($name);
            if (!empty($name)) {
                return $name;
            }
        }

        // Prompt for the name
        $helper = $this->getSender()->getHelper('question');
        $question = new Question('Migration name: ');
        $question->setValidator(function ($answer) {
            $answer = trim($answer);
            if (empty($answer)) {
                throw new \RuntimeException('Migration name cannot be empty, please check your input and try again.');
            }

            return $answer;
        });

        $name = $helper->ask($this->getSender()->getInput(), $this->getSender()->getOutput(), $question);
        unset($question);

        return $name;
    }
}