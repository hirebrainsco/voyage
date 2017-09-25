<?php

namespace Voyage\Core;

class Configuration
{
    private $dirname = '.voyage';
    private $lockFile = 'voyage.lock';

    public function checkIntegrity()
    {
        $this->checkVoyageDirectory();
        $this->checkLockFile();
    }

    public function getLockFilePath()
    {
        return $this->dirname . '/' . $this->lockFile;
    }

    public function isVoyageDirExist()
    {
        $path = $this->getVoyageDir();
        $result = file_exists($path) && is_readable($path) && is_readable($path) && is_dir($path);

        return $result;
    }

    private function getVoyageDir()
    {
        return './' . $this->dirname;
    }

    private function checkLockFile()
    {
        $path = realpath($this->getLockFilePath());
        if (file_exists($this->getLockFilePath())) {
            throw new \Exception('Voyage lock file exists at ' . $path . '. Another voyage process is running?');
        }
    }

    private function checkVoyageDirectory()
    {
        if (false === $this->isVoyageDirExist()) {
            throw new \Exception('Voyage has not been initialized yet. Please run "voyage init".');
        }
    }
}