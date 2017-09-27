<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers;

use Voyage\Core\Configuration;

class FileSystemRoutines
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct()
    {
        $this->configuration = new Configuration();
    }

    public function clean()
    {
        if ($this->configuration->isVoyageDirExist()) {

        }
    }
}