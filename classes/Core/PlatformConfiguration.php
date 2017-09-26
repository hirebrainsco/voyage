<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

/**
 * Class PlatformConfiguration
 * @package Voyage\Core
 */
class PlatformConfiguration
{
    /**
     * @var string
     */
    protected $pathToConfig = '';

    /**
     * @return bool
     * @throws \Exception
     */
    public function configFileExists()
    {
        if (empty($this->pathToConfig)) {
            throw new \Exception('Path to configuration file cannot be empty!');
        }

        return file_exists($this->pathToConfig) && is_file($this->pathToConfig) && is_readable($this->pathToConfig);
    }

    /**
     * @throws \Exception
     */
    public function getDatabaseSettings()
    {
        throw new \Exception('You should override getDatabaseSettings() method!');
    }
}