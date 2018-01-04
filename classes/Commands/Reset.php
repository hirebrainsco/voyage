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
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migration;
use Voyage\Core\Migrations;

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
        $this->setDescription('Reset database to the first migration.');

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

    private function reset()
    {
        $migrations = new Migrations($this);
        $appliedMigrations = $migrations->getAppliedMigrations();
        unset($migrations);

        if (empty($appliedMigrations)) {
            throw new \Exception('There are no applied migrations.');
        }

        $appliedMigrations = array_reverse($appliedMigrations);

        /**
         * @var array $migrationData
         */
        $sz = sizeof($appliedMigrations);
        foreach ($appliedMigrations as $migrationData) {
            $migration = new Migration($this);
            $migration->setId($migrationData['id']);
            $migration->rollback($sz > 1, $sz > 1);

            if ($sz <= 1) {
                $migration->apply(false);
                $this->info('Successfully reset to the first migration.');
                break;
            }

            $sz--;
        }
    }
}