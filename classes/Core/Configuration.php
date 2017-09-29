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
    private $tempTablePrefix = 'tmp_voyage_';

    /**
     * @var bool
     */
    private $locked = false;

    /**
     * @var string
     */
    private $lockFilename = 'voyage.lock';

    /**
     * @var string
     */
    private $migrationsTableName = 'voyage_migrations';

    /**
     * @return string
     */
    public function getTempTablePrefix()
    {
        return $this->tempTablePrefix;
    }

    /**
     * @param string $tempTablePrefix
     */
    public function setTempTablePrefix($tempTablePrefix)
    {
        $this->tempTablePrefix = $tempTablePrefix;
    }

    /**
     * @return string
     */
    public function getMigrationsTableName()
    {
        return $this->migrationsTableName;
    }

    /**
     * Check if Voyage has been initialized and not locked.
     */
    public function checkIntegrity()
    {
        $this->checkVoyageDirectory();
    }

    /**
     * @return Configuration
     */
    public static function getInstance()
    {
        static $instance = null;

        if (is_object($instance)) {
            return $instance;
        }

        $instance = new Configuration();
        return $instance;
    }

    /**
     * @return string
     */
    public function getLockFilePath()
    {
        return VOYAGE_WORKING_DIR . '/' . $this->directoryName . '/' . $this->lockFilename;
    }

    /**
     * @throws \Exception
     */
    public function lock()
    {
        $this->checkLockFile();

        if (!@file_put_contents($this->getLockFilePath(), time())) {
            throw new \Exception('Failed to create lock file at "' . $this->getLockFilePath() . '"');
        }

        $this->locked = true;
        register_shutdown_function([$this, 'shutdown']);
    }

    public function shutdown()
    {
        if ($this->locked) {
            $this->unlock();
        }
    }

    /**
     * @throws \Exception
     */
    public function unlock()
    {
        if (!$this->locked) {
            return;
        }

        if (file_exists($this->getLockFilePath())) {
            @unlink($this->getLockFilePath());
        }
    }

    /**
     * @return bool
     */
    public function isVoyageDirExist()
    {
        $path = $this->getPathToVoyage();
        $result = file_exists($path) && is_readable($path) && is_readable($path) && is_dir($path);

        return $result;
    }

    /**
     * @return string
     */
    public function getPathToVoyage()
    {
        return VOYAGE_WORKING_DIR . '/' . $this->directoryName;
    }

    /**
     * @return string
     */
    public function getPathToEnvironments()
    {
        return $this->getPathToVoyage() . '/environments';
    }

    /**
     * @return string
     */
    public function getPathToMigrations()
    {
        return $this->getPathToVoyage() . '/migrations';
    }

    /**
     * Check if Voyage is locked (another Voyage process is running).
     * @throws \Exception
     */
    private function checkLockFile()
    {
        $path = $this->getLockFilePath();
        if (file_exists($this->getLockFilePath())) {
            throw new \Exception('Voyage lock file exists at ' . $path . '. Another Voyage process is running?');
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