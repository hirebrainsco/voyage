<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

require('./vendor/autoload.php');

$compiler = new \Secondtruth\Compiler\Compiler(__DIR__);
$compiler->addIndexFile('voyage.php');
$compiler->addDirectory('vendor');
$compiler->addDirectory('lib');
$compiler->addDirectory('classes');
$compiler->compile('bin/voyage.phar');