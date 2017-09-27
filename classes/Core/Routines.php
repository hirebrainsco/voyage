<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

abstract class Routines
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var InputOutputInterface
     */
    private $reporter;

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return InputOutputInterface
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * Routines constructor.
     * @param InputOutputInterface $reporter
     */
    public function __construct(InputOutputInterface $reporter)
    {
        $this->configuration = new Configuration();
        $this->reporter = $reporter;
    }
}