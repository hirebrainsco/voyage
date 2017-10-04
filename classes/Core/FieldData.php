<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

class FieldData
{
    public $name = '';
    public $type = '';
    public $default = '';
    public $nullValue = '';
    public $key = '';
    public $extra = '';

    /**
     * FieldData constructor.
     * @param string $name
     * @param string $type
     * @param string $default
     * @param string $nullValue
     * @param string $key
     * @param string $extra
     */
    public function __construct($name, $type, $default = '', $nullValue = '', $key = '', $extra = '')
    {
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
        $this->nullValue = $nullValue;
        $this->key = $key;
        $this->extra = $extra;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey()
    {
        return $this->key == 'PRI';
    }
}