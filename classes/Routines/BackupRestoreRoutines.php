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
     * @param string $filename
     * @throws \Exception
     */
    public function restore($filename = '')
    {
        if (empty($filename)) {
            $filename = $this->getLatestBackupFilename();
            if (empty($filename)) {
                throw new \Exception('There\'re no backups have been taken yet.');
            }
        }

        $filePath = Configuration::getInstance()->getPathToBackups() . '/' . $filename;
        if (!file_exists($filePath)) {
            throw new \Exception('Backup file doesn\'t exist at "' . $filePath . '"');
        }
    }

    /**
     * @return string
     */
    protected function generateBackupFilename()
    {
        $filename = date('Ymd-His') . '-backup.sql';
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