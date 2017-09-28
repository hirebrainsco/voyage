<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

/**
 * Class GitIgnore
 * @package Voyage\Configuration
 */
class GitIgnore extends ConfigFile
{
    /**
     * @var string
     */
    protected $filename = '.gitignore';

    /**
     * @return string
     */
    protected function getTemplate()
    {
        $template = '#' . PHP_EOL;
        $template .= '# List of files that should be ignored in git.' . PHP_EOL;
        $template .= '#' . PHP_EOL;
        $template .= 'environment' . PHP_EOL;
        $template .= PHP_EOL;

        return $template;
    }
}