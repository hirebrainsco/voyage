<?php

namespace Voyage\Commands;

use Voyage\Core\Command;

class Apply extends Command
{
    public function __construct()
    {
        $this->setName('apply');
        $this->setDescription('Apply all migrations that hasn\'t been applied to the current database yet.');

        parent::__construct();
    }
}