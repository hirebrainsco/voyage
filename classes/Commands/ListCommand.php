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

            $this->showMigrationsList();
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    /**
     * Display a list of applied migrations.
     */
    protected function showMigrationsList()
    {
        $totalItems = 0;

        $table = new Table($this->getOutput());
        $table->setHeaders([
            'ID', 'Description', 'Date', 'Status'
        ]);

        $tableStyle = new TableStyle();
        $tableStyle->setCellHeaderFormat('%s');
        $table->setStyle($tableStyle);

        $migrations = new Migrations($this);
        $appliedMigrations = $migrations->getAppliedMigrations();
        $notAppliedMigrations = $migrations->getNotAppliedMigrations();

        $migrationsList = array_merge($appliedMigrations, $notAppliedMigrations);
        ksort($migrationsList);

        $migrationsPath = Configuration::getInstance()->getPathToMigrations();
        $missingFiles = 0;

        foreach ($migrationsList as $migration) {
            $totalItems++;
            $pathToMigrationFile = $migrationsPath . '/' . $migration['id'] . '.mgr';

            if ($migration['applied']) {
                $isCurrent = isset($migration['current']) && $migration['current'] === true;
                $migrationFileExists = file_exists($pathToMigrationFile);

                if (!$migrationFileExists) {
                    $missingFiles++;
                }

                $status = $isCurrent ? '<-- Current --' : ' ';
                if (!$migrationFileExists) {
                    $status = '<fg=red>x-- Not Found --</>';
                }

                $table->addRow([
                    ($isCurrent ? '<fg=green>' : '') . $migration['id'] . ($isCurrent ? '</>' : ''),
                    $migration['name'],
                    date('d M Y', $migration['ts']),
                    $status
                ]);
            } else {
                $totalItems++;
                $parser = new MigrationFileParser($pathToMigrationFile);
                $description = $parser->getDescription();
                $ts = $parser->getTimestamp();

                $table->addRow([
                    '<fg=cyan>' . $migration['id'] . '</>',
                    '<fg=cyan>' . $description . '</>',
                    '<fg=cyan>' . ($ts > 0 ? date('d M Y', $ts) : '') . '</>',
                    '<fg=cyan>x-- Not applied --</>',
                ]);
                unset($parser);
            }
        }

        if ($totalItems > 0) {
            $this->report('<options=bold>Applied Migrations</>');
            $table->render();

            if ($missingFiles > 0) {
                $this->report('');
                $this->report('<error>Missing Migration Files</error>');
                $this->report('Some migration files are missing. This usually can happen if you switch to a different version in GIT where some migrations are not available yet.');
                $this->report('You should run "voyage reset", this command will rollback database to it\'s initial state and then will apply all available migration files.');
                $this->report('');
            }
        } else {
            $this->report('There\'re no applied migrations. Run "voyage make" to create the first migration.');
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