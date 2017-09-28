<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

class CurrentEnvironment extends ConfigFile
{
    protected $filename = 'environment';

    protected function getTemplate()
    {
        $template = '# Current environment.' . PHP_EOL;
        $template .= $this->getSender()->getEnvironment()->getName() . PHP_EOL;

        return $template;
    }

    public function getEnvironment()
    {

    }

    public function getEnvironmentName()
    {
        $contents = str_replace(["\r", "\n", "\t", " "], '', trim($this->getConfigContents()));
        return $contents;
    }
}