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
use Voyage\Routines\Migrations;

/**
 * Class Make
 * @package Voyage\Commands
 */
class Make extends Command implements EnvironmentControllerInterface
{
    /**
     * Make constructor.
     */
    public function __construct()
    {
        $this->setName('make');
        $this->setDescription('Calculate difference between current database state and latest migration and create a new migration containing changes.');

        parent::__construct();
        $this->addCommandOptions();
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
            Configuration::getInstance()->lock();

            $this->displayAppName();
            $this->checkIntegrity($output);
            $this->initCurrentEnvironment();

            $migrations = new Migrations($this);
            $migrations->make();
            unset($migrations);
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    /**
     * Add options for 'make' command.
     */
    private function addCommandOptions()
    {
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of migration. Voyage will prompt for a name if not passed via parameter.');
    }
}