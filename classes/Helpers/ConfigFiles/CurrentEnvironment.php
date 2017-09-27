<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Helpers\ConfigFiles;

class CurrentEnvironment extends ConfigFile
{
    protected $filename = 'environment';

    protected function getTemplate()
    {
        $template = '# Current environment.' . PHP_EOL;
        $template .= $this->getSender()->getEnvironment()->getName() . PHP_EOL;

        return $template;
    }
}