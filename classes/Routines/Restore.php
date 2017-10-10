<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\EnvironmentControllerInterface;

class Restore
{
    /**
     * @var string
     */
    private $importFilePath = '';

    /**
     * @var EnvironmentControllerInterface
     */
    private $environmentController;

    /**
     * Backup constructor.
     * @param EnvironmentControllerInterface $environmentController
     * @param string $importFilePath
     */
    public function __construct(EnvironmentControllerInterface $environmentController, $importFilePath)
    {
        $this->environmentController = $environmentController;
        $this->setImportFilePath($importFilePath);
    }

    /**
     * @return string
     */
    public function getImportFilePath()
    {
        return $this->importFilePath;
    }

    /**
     * @param string $importFilePath
     */
    public function setImportFilePath($importFilePath)
    {
        $this->importFilePath = $importFilePath;
    }

    public function restore()
    {
        $this->checkImportFile();

        $fileHandle = fopen($this->getImportFilePath(), 'r');
        if (!is_resource($fileHandle)) {
            throw new \Exception('Failed to read: ' . $this->getImportFilePath());
        }

        $queryCode = '';
        while (($line = fgets($fileHandle)) !== false) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if ($line[0] == '#') {
                continue;
            }

            $queryCode .= ' ' . $line;
            $matches = [];

            if (preg_match("/\A\s*(.*)\s*;\s*\z/", $queryCode, $matches)) {
                $queryCode = '';
                $contents[] = trim($matches[1]);
            }
        }

        foreach ($contents as $item) {
            if (stripos($item, 'siteurl') !== false) {
                echo $item . PHP_EOL;
            }
        }
        fclose($fileHandle);
    }

    /**
     * @throws \Exception
     */
    protected function checkImportFile()
    {
        $filePath = $this->getImportFilePath();
        if (empty($filePath)) {
            throw new \Exception('Restore filename cannot be empty!');
        }

        if (!file_exists($filePath) || !is_file($filePath) || !is_readable($filePath)) {
            throw new \Exception('Cannot find or read "' . $filePath . '"');
        }
    }
}