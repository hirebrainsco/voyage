<?php

namespace Voyage;

class MakeCommand extends Command
{
    public function __construct()
    {
        $this->setName('make');
        $this->setDescription('Calculate difference between current database state and latest migration and create a new migration containing changes.');

        parent::__construct();
    }
}