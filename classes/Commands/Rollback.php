<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Core\Command;
use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;
use Voyage\Core\Migrations;

/**
 * Class Rollback
 * @package Voyage\Commands
 */
class Rollback extends Command implements EnvironmentControllerInterface
{
    /**
     * Rollback constructor.
     */
    public function __construct()
    {
        $this->setName('rollback');
        $this->setDescription('Rollback to previous migration.');

        parent::__construct();

        $this->addOption('migration', 'm', InputOption::VALUE_OPTIONAL, 'Rollback a migration and apply changes to current database. If no parameters set the command will return to previous migration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);

            $this->displayAppName();
            $this->checkIntegrity($output);
            $this->initCurrentEnvironment();

            $this->createBackup();
            $this->rollback();
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    protected function createBackup()
    {
        // TODO: Create backup here
    }

    protected function rollback()
    {
        Configuration::getInstance()->lock();

        $migrations = $this->getMigrationsList();
        if (empty($migrations)) {
            $this->info('There\'re no steps to rollback.');
            return;
        }

        foreach ($migrations as $migrationData) {
            $this->report('<fg=cyan>' . $migrationData['id'] . '</>');
            $migration = new Migration($this);
            $migration->setId($migrationData['id']);
            $migration->rollback(false, true);
            unset($migration);

            $this->report('Rollback successful.');
            $this->report('');
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getMigrationsList()
    {
        $migrations = new Migrations($this);
        $list = $migrations->getAppliedMigrations();

        if (empty($list)) {
            return [];
        }

        $list = array_reverse($list);
        $migration = $this->getInput()->getOption('migration');

        $rollbackList = [];
        $migrationIdFound = false;

        foreach ($list as $item => $itemData) {
            if (empty($migration)) {
                $rollbackList[] = $itemData;
                break;
            }

            if (!empty($migration) && $migration == $item) {
                $migrationIdFound = true;
                break;
            } else {
                $rollbackList[] = $itemData;
            }
        }

        if (sizeof($rollbackList) == sizeof($list)) {
            // Exclude initial migration
            $rollbackList = array_splice($rollbackList, 0, sizeof($rollbackList) - 1);
        }

        if (!$migrationIdFound && !empty($migration)) {
            throw new \Exception('Migration "' . $migration . '" not found!');
        }

        return $rollbackList;
    }
}