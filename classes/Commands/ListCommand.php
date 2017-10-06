<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Core\Command;
use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\Migrations;

/**
 * Class ListCommand
 * @package Voyage\Commands
 */
class ListCommand extends Command implements EnvironmentControllerInterface
{
    /**
     * ListCommand constructor.
     */
    public function __construct()
    {
        $this->setName('list');
        $this->setDescription('Show a list of all database migrations.');

        parent::__construct();
    }

    /**
     *
     */
    public function configure()
    {

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

            $this->report('<options=bold>Applied Migrations</>');
            $table = new Table($output);
            $table->setHeaders([
                'ID', 'Description', 'Date', 'Current Migration'
            ]);

            $migrations = new Migrations($this);
            $appliedMigrations = $migrations->getAppliedMigrations();

            $sz = sizeof($appliedMigrations);
            $i = 0;
            foreach ($appliedMigrations as $migration) {
                $table->addRow([
                    $migration['id'], $migration['name'], date('d M Y', $migration['ts']), $i == $sz - 1 ? '<-- Current --' : ''
                ]);
                $i++;
            }

            $table->render();

            $notAppliedMigrations = $migrations->getNotAppliedMigrations();
            $table = new Table($output);

            if (!empty($notAppliedMigrations)) {
                $this->report('<options=bold></>');
                $this->report('<options=bold>List of Not Applied Migrations</>');
                $table->setHeaders([
                    'ID'
                ]);
                foreach ($notAppliedMigrations as $migration) {
                    $table->addRow([
                        $migration
                    ]);
                }
                $table->render();
            }
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    /**
     * @return InputDefinition
     */
    public function getNativeDefinition()
    {
        return new InputDefinition();
    }
}