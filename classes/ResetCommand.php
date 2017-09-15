<?php

namespace Voyage;

class ResetCommand extends Command
{
    public function __construct()
    {
        $this->setName('reset');
        $this->setDescription('Reset database to it\'s initial state (first taken dump) and remove all next migrations.');

        parent::__construct();
    }
}