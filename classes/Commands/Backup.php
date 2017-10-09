<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Voyage\Core\Command;
use Voyage\Core\Configuration;
use Voyage\Core\EnvironmentControllerInterface;
use Voyage\Routines\BackupRoutines;

class Backup extends Command implements EnvironmentControllerInterface
{
    public function __construct()
    {
        $this->setName('backup');
        $this->setDescription('Create a backup of database.');

        parent::__construct();

        $this->addArgument('list', InputArgument::OPTIONAL, 'Show a list ot taken backups.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);

            $this->displayAppName();
            $this->checkIntegrity($output);
            $this->initCurrentEnvironment();

            if ($input->getArgument('list')) {
                $this->showList();
            } else {
                $this->createBackup();
            }
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    /**
     * Display list of backups.
     */
    protected function showList()
    {

        $backupRoutines = new BackupRoutines($this);
        $list = $backupRoutines->getAllBackups();

        $backupsPath = Configuration::getInstance()->getPathToBackups();
        $this->report('Path to backups: <fg=cyan>' . $backupsPath . '</>');

        if (empty($list)) {
            $this->report('No backups has been found found.');
            return;
        }

        $table = new Table($this->getOutput());
        $table->setHeaders(['#', 'Name', 'Date']);
        $tableStyle = new TableStyle();
        $tableStyle->setCellHeaderFormat('%s');
        $table->setStyle($tableStyle);

        $i = 0;

        foreach ($list as $item) {
            $i++;

            $filename = pathinfo($item, PATHINFO_FILENAME);
            $date = $this->getDateFromFilename($filename);

            $table->addRow([$i, $filename, $date]);
        }

        $table->render();
    }

    /**
     * @param $filename
     * @return false|string
     */
    protected function getDateFromFilename($filename)
    {
        $pattern = '/\s*(\d{4})(\d{2})(\d{2})\-\d{4,}\-(.*)/';
        $matches = [];
        if (preg_match($pattern, $filename, $matches)) {
            $date = date('d M Y', strtotime($matches[1] . '-' . $matches[2] . '-' . $matches[3]));
            return $date;
        }

        return '';
    }

    /**
     * Create a new backup.
     */
    protected function createBackup()
    {
        $backup = new BackupRoutines($this);
        $exportFilePath = $backup->backup();
        unset($backup);

        if (false !== $exportFilePath) {
            $this->info('Backup has been successfully created at "' . $exportFilePath . '"');
        } else {
            $this->report('Failed to create backup file.');
        }
    }
}