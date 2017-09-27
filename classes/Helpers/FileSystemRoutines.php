<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Core\Configuration;
use Voyage\Core\InputOutputInterface;
use Voyage\Core\Routines;

/**
 * Class FileSystemRoutines
 * @package Voyage\Helpers
 */
class FileSystemRoutines extends Routines
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
        $this->getReporter()->info('Path: ' . $voyageBasePath . ' exists. Removing it.');

        // Remove voyage directory
        $items = scandir($voyageBasePath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $item = $voyageBasePath . '/' . $item;
            if (is_dir($item)) {
                @rmdir($item);
            } else {
                @unlink($item);
            }
        }

        @rmdir($voyageBasePath);
    }
}