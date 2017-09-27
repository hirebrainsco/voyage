<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Core\Configuration;
use Voyage\Core\InputOutputInterface;

/**
 * Class FileSystemRoutines
 * @package Voyage\Helpers
 */
class FileSystemRoutines
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var InputOutputInterface
     */
    private $reporter;

    public function __construct(InputOutputInterface $reporter)
    {
        $this->configuration = new Configuration();
        $this->reporter = $reporter;
    }

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
        if (!$this->configuration->isVoyageDirExist()) {
            return;
        }

        $this->reporter->info('Path: ' . $this->configuration->getPathToVoyage() . ' exists. Removing it.');

        // Remove voyage directory
        $path = $this->configuration->getPathToVoyage();
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $item = $this->configuration->getPathToVoyage() . '/' . $item;
            if (is_dir($item)) {
                @rmdir($item);
            } else {
                @unlink($item);
            }
        }

        @rmdir($path);
    }
}