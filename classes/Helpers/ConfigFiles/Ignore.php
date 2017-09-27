<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers\ConfigFiles;

/**
 * Class Ignore
 * @package Voyage\Helpers\ConfigFiles
 */
class Ignore extends ConfigFile
{
    /**
     * @var string
     */
    protected $filename = 'ignore';

    /**
     * @return string
     */
    protected function getTemplate()
    {
        $template = '#' . PHP_EOL;
        $template .= '# IGNORE LIST' . PHP_EOL;
        $template .= '#' . PHP_EOL;
        $template .= '# A list of tables which should be ignored. Include tables which contain debug, log or binary data to this list.' . PHP_EOL;
        $template .= '# Add one table name per line. A table without any parameters will be ignored completely (data and structure). ' . PHP_EOL;
        $template .= '# If you would like to keep tracking changes of table structure and but ignore data then add a "~" before table name, ' . PHP_EOL;
        $template .= '# For example:  ' . PHP_EOL;
        $template .= '#    ~users - this will record only changes in structure of the `users` table.' . PHP_EOL;
        $template .= '#    users - this will completely ignore table `users`.' . PHP_EOL;
        $template .= '#' . PHP_EOL . PHP_EOL;

        return $template;
    }
}