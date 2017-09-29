<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

class TableData
{
    public $name;
    public $ignoreData = false;

    /**
     * TableData constructor.
     * @param $name
     * @param bool $ignoreData
     */
    public function __construct($name, $ignoreData)
    {
        $this->name = $name;
        $this->ignoreData = $ignoreData;
    }
}