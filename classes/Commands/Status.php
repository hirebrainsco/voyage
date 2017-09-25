<?php

namespace Voyage\Commands;

use Voyage\Core\Command;

class Status extends Command
{
    public function __construct()
    {
        $this->setName('status');
        $this->setDescription('Check current status (current migration and list of migrations that hasn\'t been imported yet).');

        parent::__construct();
    }
}