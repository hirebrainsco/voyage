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
use Voyage\Routines\MigrationRoutines;

/**
 * Class Status
 * @package Voyage\Commands
 */
class Status extends Command implements EnvironmentControllerInterface
{
    /**
     * Status constructor.
     */
    public function __construct()
    {
        $this->setName('status');
        $this->setDescription('Check current status (current migration and list of migrations that hasn\'t been imported yet).');

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);

            $this->displayAppName();
            $this->checkIntegrity($output);
            $this->initCurrentEnvironment();

            $migrations = new MigrationRoutines($this);
            $migrations->status();
            unset($migrations);
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }
}