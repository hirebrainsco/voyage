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
     * @throws \Exception
     */
    public function __construct($name, $ignoreData)
    {
        if (empty($name)) {
            throw new \Exception('Table name cannot be empty!');
        }

        $this->name = $name;
        $this->ignoreData = $ignoreData;
    }
}