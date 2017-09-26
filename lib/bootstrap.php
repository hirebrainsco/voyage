<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

/**
 * Path to Voyage classes.
 */
define('VOYAGE_CLASS_DIR', __DIR__ . '/../classes/');

/**
 * SPL autoload
 * @param $className
 */
function voyageAutoload($className)
{
    $namespace = 'Voyage\\';
    if (strpos($className, $namespace) === false) {
        return;
    }

    $pathToClass = VOYAGE_CLASS_DIR . str_replace([$namespace, '\\'], ['', '/'], $className) . '.php';
    if(file_exists($pathToClass)) {
        require_once($pathToClass);
    }
}

spl_autoload_register('voyageAutoload');