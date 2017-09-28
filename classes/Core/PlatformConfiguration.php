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
     * @var string
     */
    protected $defaultTablePrefix = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDefaultTablePrefix()
    {
        return $this->defaultTablePrefix;
    }

    /**
     * @return array
     */
    public function getIgnoreTables()
    {
        return [];
    }

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
     * @return null|DatabaseSettings
     */
    public function getDatabaseSettings()
    {
        if (!$this->configFileExists()) {
            return null;
        }

        $result = $this->extract();
        if (empty($result)) {
            return null;
        }

        $settings = new DatabaseSettings();

        $settings->setUsername($result['user']);
        $settings->setPassword($result['pass']);
        $settings->setDatabaseName($result['name']);
        $settings->setHost($result['host']);

        if (!is_null($result['prefix'])) {
            $settings->setTablePrefix($result['prefix']);
        }

        return $settings;
    }

    protected function extract()
    {
        $result = [
            'name' => null,
            'host' => null,
            'user' => null,
            'pass' => null,
            'prefix' => null
        ];

        return $result;
    }
}