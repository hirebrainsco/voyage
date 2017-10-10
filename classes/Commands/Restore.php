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
use Voyage\Routines\BackupRestoreRoutines;

class Restore extends Command implements EnvironmentControllerInterface
{
    public function __construct()
    {
        $this->setName('restore');
        $this->setDescription('Restore data from backup.');

        parent::__construct();

        $this->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'Name of the backup to restore data from. Use "voyage backup list" command to view list of backups.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);

            $this->displayAppName();
            $this->checkIntegrity($output);
            $this->initCurrentEnvironment();

            $this->restoreBackup($input->getOption('id'));
        } catch (\Exception $e) {
            $this->fatalError($e->getMessage());
        }
    }

    /**
     * Restore backup
     * @param string $backupId
     * @throws \Exception
     */
    protected function restoreBackup($backupId = '')
    {
        $backupsPath = Configuration::getInstance()->getPathToBackups();
        if (!file_exists($backupsPath)) {
            throw new \Exception('Backups directory doesn\'t exist at "' . $backupsPath . '"');
        }

        if (!empty($backupId)) {
            $backupId = $backupsPath . '/' . $backupId . '.sql';
        }

        $backup = new BackupRestoreRoutines($this);
        $backup->restore($backupId);
        unset($backup);

        $this->report('Backup has been successfully restored.');
    }
}