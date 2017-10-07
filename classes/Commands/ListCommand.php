<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Core\Command;
use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Core\MigrationFileParser;
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

            $this->showAppliedMigrations();
            $this->showNotAppliedMigrations();
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    /**
     * Display a list of applied migrations.
     */
    protected function showAppliedMigrations()
    {
        $this->report('<options=bold>Applied Migrations</>');
        $table = new Table($this->getOutput());
        $table->setHeaders([
            'ID', 'Description', 'Date', 'Current Migration'
        ]);

        $migrations = new Migrations($this);
        $appliedMigrations = $migrations->getAppliedMigrations();

        $sz = sizeof($appliedMigrations);
        $i = 0;
        foreach ($appliedMigrations as $migration) {
            $table->addRow([
                $migration['id'],
                $migration['name'],
                date('d M Y', $migration['ts']),
                $i == $sz - 1 ? '<-- Current --' : ''
            ]);
            $i++;
        }

        $table->render();
        unset($migrations);
    }

    /**
     * Display a list of not applied migrations.
     */
    protected function showNotAppliedMigrations()
    {
        $migrations = new Migrations($this);
        $notAppliedMigrations = $migrations->getNotAppliedMigrations();
        $table = new Table($this->getOutput());

        if (!empty($notAppliedMigrations)) {
            $this->report('');
            $this->report('<options=bold>List of Not Applied Migrations</>');
            $table->setHeaders([
                'ID', 'Description', 'Date'
            ]);

            $migrationsPath = Configuration::getInstance()->getPathToMigrations();

            foreach ($notAppliedMigrations as $migration) {
                $parser = new MigrationFileParser($migrationsPath . '/' . $migration . '.mgr');
                $description = $parser->getDescription();
                $ts = $parser->getTimestamp();

                $table->addRow([
                    $migration,
                    $description,
                    $ts > 0 ? date('d M Y', $ts) : ''
                ]);
                unset($parser);
            }

            $tableStyle = new TableStyle();
            $tableStyle->setCellHeaderFormat('<fg=cyan>%s</>');

            $table->setStyle($tableStyle);
            $table->render();
        }

        unset($migrations);
    }

    /**
     * @return InputDefinition
     */
    public function getNativeDefinition()
    {
        return new InputDefinition();
    }
}