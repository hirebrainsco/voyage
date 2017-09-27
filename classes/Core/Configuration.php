<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

/**
 * Class Configuration
 * @package Voyage\Core
 */
class Configuration
{
    /**
     * @var string
     */
    private $directoryName = '.voyage';
    /**
     * @var string
     */
    private $lockFilename = 'voyage.lock';

    /**
     * Check if Voyage has been initialized and not locked.
     */
    public function checkIntegrity()
    {
        $this->checkVoyageDirectory();
        $this->checkLockFile();
    }

    /**
     * @return string
     */
    public function getLockFilePath()
    {
        return $this->directoryName . '/' . $this->lockFilename;
    }

    /**
     * @return bool
     */
    public function isVoyageDirExist()
    {
        $path = $this->getVoyageDir();
        $result = file_exists($path) && is_readable($path) && is_readable($path) && is_dir($path);

        return $result;
    }

    /**
     * @return string
     */
    private function getVoyageDir()
    {
        return './' . $this->directoryName;
    }

    /**
     * Check if Voyage is locked (another Voyage process is running).
     * @throws \Exception
     */
    private function checkLockFile()
    {
        $path = realpath($this->getLockFilePath());
        if (file_exists($this->getLockFilePath())) {
            throw new \Exception('Voyage lock file exists at ' . $path . '. Another voyage process is running?');
        }
    }

    /**
     * Check if Voyage directory exists.
     * @throws \Exception
     */
    private function checkVoyageDirectory()
    {
        if (false === $this->isVoyageDirExist()) {
            throw new \Exception('Voyage has not been initialized yet. Please run "voyage init".');
        }
    }
}