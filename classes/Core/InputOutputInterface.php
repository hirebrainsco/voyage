<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface InputOutputInterface
{
    /**
     * @return InputInterface
     */
    public function getInput();

    /**
     * @return OutputInterface
     */
    public function getOutput();

    /**
     * @param $string
     */
    public function writeln($string);

    /**
     * @return bool
     */
    public function isQuiet();

    /**
     * @param $message
     */
    public function report($message);

    /**
     * @param $message
     */
    public function info($message);

    /**
     * @param $message
     */
    public function fatalError($message);

    /**
     * @param $name
     * @return mixed
     */
    public function getHelper($name);
}