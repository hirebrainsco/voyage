<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

/**
 * Class ApacheConfig
 * @package Voyage\Configuration
 */
class ApacheConfig extends ConfigFile
{
    /**
     * @var string
     */
    protected $filename = '.htaccess';

    /**
     * @return string
     */
    protected function getTemplate()
    {
        $template = '#' . PHP_EOL;
        $template .= '# Do not allow access to this directory from web.' . PHP_EOL;
        $template .= '#' . PHP_EOL;
        $template .= 'Deny from all' . PHP_EOL;

        return $template;
    }
}