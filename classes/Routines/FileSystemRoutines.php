<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;

use Voyage\Core\Routine;
use Voyage\Configuration\ConfigFiles;

/**
 * Class FileSystemRoutines
 * @package Voyage\Routines
 */
class FileSystemRoutines extends Routine
{
    /**
     * Remove all voyage files.
     */
    public function clean()
    {
        $this->removeVoyageDirectory();
    }

    /**
     * Remove voyage directory if it exists.
     */
    private function removeVoyageDirectory()
    {
        if (!$this->getConfiguration()->isVoyageDirExist()) {
            return;
        }

        $voyageBasePath = $this->getConfiguration()->getPathToVoyage();
        $this->getSender()->info('Path: ' . $voyageBasePath . ' exists. Removing it.');

        $this->removeDir($voyageBasePath);
    }

    /**
     * @param $filePath
     * @param $content
     */
    public function prepend($filePath, $content)
    {
        @file_put_contents($filePath, $content . @file_get_contents($filePath));
    }

    /**
     * @param $path
     */
    private function removeDir($path)
    {
        // Remove voyage directory
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $item = $path . '/' . $item;
            if (is_dir($item)) {
                $this->removeDir($item);
            } else {
                @unlink($item);
            }
        }

        @rmdir($path);
    }

    /**
     * Create empty voyage directories.
     */
    public function createDirectories()
    {
        // Create voyage directory
        if (!$this->getConfiguration()->isVoyageDirExist()) {
            if (!@mkdir($this->getConfiguration()->getPathToVoyage())) {
                $this->getSender()->fatalError('Failed to create Voyage directory at "' . $this->getConfiguration()->getPathToVoyage() . '"');
            }
        }

        @mkdir($this->getConfiguration()->getPathToEnvironments());
        @mkdir($this->getConfiguration()->getPathToBackups());
        @mkdir($this->getConfiguration()->getPathToMigrations());
    }

    /**
     * Create configuration files.
     */
    public function createConfigFiles()
    {
        $configFiles = new ConfigFiles($this->getSender());
        $configFiles->createConfigurationFiles();
        unset($configFiles);

        $this->getSender()->report('Created configuration files.');
    }
}