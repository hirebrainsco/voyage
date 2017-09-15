<?php

namespace Voyage;

class ApplyCommand extends Command
{
    public function __construct()
    {
        $this->setName('apply');
        $this->setDescription('Apply all migrations that hasn\'t been applied to the current database yet.');

        parent::__construct();
    }
}