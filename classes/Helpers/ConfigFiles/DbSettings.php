<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers\ConfigFiles;

/**
 * Class DbSettings
 * @package Voyage\Helpers\ConfigFiles
 */
class DbSettings extends ConfigFile
{
    /**
     * @var string
     */
    protected $filename = 'config';

    /**
     * @return string
     */
    protected function getTemplate()
    {
        $template = '#' . PHP_EOL;
        $template .= '# DB CONFIGURATION' . PHP_EOL;
        $template .= '#' . PHP_EOL;
        $template .= '# This configuration file contains database access information. You should ignore this file in your' . PHP_EOL;
        $template .= '# version control system.' . PHP_EOL;
        $template .= '#' . PHP_EOL . PHP_EOL;

        if (is_object($this->getSender())) {
            $databaseSettings = $this->getSender()->getDatabaseSettings();

            $template .= sprintf('host=%s:%s' . PHP_EOL, $databaseSettings->getHost(), $databaseSettings->getPort());
            $template .= sprintf('database=%s' . PHP_EOL, $databaseSettings->getDatabaseName());
            $template .= sprintf('username=%s' . PHP_EOL, $databaseSettings->getUsername());
            $template .= sprintf('password=%s' . PHP_EOL, $databaseSettings->getPassword());
        }

        return $template;
    }

    public function createEmptyFile()
    {
        parent::createEmptyFile();
    }
}