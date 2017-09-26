<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Commands;

use Voyage\Core\Command;

/**
 * Class Reset
 * @package Voyage\Commands
 */
class Reset extends Command
{
    /**
     * Reset constructor.
     */
    public function __construct()
    {
        $this->setName('reset');
        $this->setDescription('Reset database to it\'s initial state (first taken dump) and remove all next migrations.');

        parent::__construct();
    }
}