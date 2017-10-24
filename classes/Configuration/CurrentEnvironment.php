<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

use Voyage\Routines\EnvironmentsFactory;

/**
 * Class CurrentEnvironment
 * @package Voyage\Configuration
 */
class CurrentEnvironment extends ConfigFile
{
    /**
     * @var string
     */
    protected $filename = 'environment';

    /**
     * @return string
     */
    protected function getTemplate()
    {
        $template = '# Current environment.' . PHP_EOL;
        $template .= $this->getSender()->getEnvironment()->getName() . PHP_EOL;

        return $template;
    }

    /**
     * @return \Voyage\Core\Environment
     */
    public function getEnvironment()
    {
        $environmentName = $this->getEnvironmentName();
        if (empty($environmentName)) {
            $this->getSender()->fatalError("Current environment hasn't been set. Please, check settings in " . $this->getFilePath());
        }

        try {
            $factory = new EnvironmentsFactory($environmentName);
            $environmentInstance = $factory->create();
            unset($factory);

            return $environmentInstance;
        } catch (\Exception $e) {
            $this->getSender()->fatalError($e->getMessage());
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getEnvironmentName()
    {
        $contents = str_replace(["\r", "\n", "\t", " "], '', trim($this->getConfigContents()));
        return $contents;
    }
}