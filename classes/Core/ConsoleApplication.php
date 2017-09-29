<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleApplication extends \Symfony\Component\Console\Application
{
    public function renderException(\Exception $e, OutputInterface $output)
    {
        $output->writeln($e->getMessage());
    }
}