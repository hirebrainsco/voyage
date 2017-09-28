#!/usr/bin/env php
<?php

if (php_sapi_name() !== 'cli') {
    die('This tool should be executed only from command-line interface.');
}

define('VOYAGE_WORKING_DIR', __DIR__);

require VOYAGE_WORKING_DIR . '/vendor/autoload.php';
require VOYAGE_WORKING_DIR . '/lib/bootstrap.php';

use Voyage\Core\Application;

$application = new Application();
$application->run();