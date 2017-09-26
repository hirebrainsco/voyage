<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Voyage\Core\Command;

/**
 * Class Apply
 * @package Voyage\Commands
 */
class Apply extends Command
{
    /**
     * Apply constructor.
     */
    public function __construct()
    {
        $this->setName('apply');
        $this->setDescription('Apply all migrations that hasn\'t been applied to the current database yet.');

        parent::__construct();
    }
}