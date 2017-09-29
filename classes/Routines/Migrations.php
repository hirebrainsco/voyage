<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Symfony\Component\Console\Question\Question;
use Voyage\Core\Routine;

/**
 * Class Migrations
 * @package Voyage\Routines
 */
class Migrations extends Routine
{
    public function make()
    {
        $databaseRoutines = new DatabaseRoutines($this->getSender());
        $databaseRoutines->checkPermissions();
        $migrationName = $this->promptMigrationName();
        unset($databaseRoutines);
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