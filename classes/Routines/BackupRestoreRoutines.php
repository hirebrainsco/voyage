<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\Configuration;
use Voyage\Core\Routine;

class BackupRestoreRoutines extends Routine
{
    /**
     * @param string $filename
     * @return bool|string
     */
    public function backup($filename = '')
    {
        if (empty($filename)) {
            $filename = $this->generateBackupFilename();
        }

        $filePath = Configuration::getInstance()->getPathToBackups() . '/' . $filename;

        $backup = new Backup($this->getSender(), $filePath);
        $result = $backup->create();
        unset($backup);

        return $result === true ? $filePath : false;
    }

    /**
     * @param string $filePath
     * @throws \Exception
     */
    public function restore($filePath = '')
    {
        if (empty($filePath)) {
            $filePath = $this->getLatestBackupFilename();
            if (empty($filePath)) {
                throw new \Exception('There\'re no backups have been taken yet.');
            }
        }

        if (!file_exists($filePath)) {
            throw new \Exception('Backup file doesn\'t exist at "' . $filePath . '"');
        }

        $this->getSender()->info('Restoring from "' . $filePath . '"');
        $restore = new Restore($this->getSender(), $filePath);
        $restore->restore();
        unset($restore);
    }

    /**
     * @return string
     */
    protected function generateBackupFilename()
    {
        $filename = date('Ymd-His') . '-' . $this->getSender()->getEnvironment()->getName() . '-backup.sql';
        return $filename;
    }

    /**
     * @return string
     */
    protected function getLatestBackupFilename()
    {
        $files = $this->getAllBackups();
        if (empty($files)) {
            return '';
        }

        return end($files);
    }

    /**
     * @return array
     */
    public function getAllBackups()
    {
        $files = glob(Configuration::getInstance()->getPathToBackups() . '/*.sql');
        if (empty($files)) {
            return [];
        }

        return $files;
    }
}