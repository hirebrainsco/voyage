<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

/**
 * Class IgnoreDataFieldRule
 * @package Voyage\Configuration
 */
class IgnoreDataFieldRule
{
    /**
     * @var string
     */
    private $tableName = '';

    /**
     * @var string
     */
    private $fieldName = '';

    /**
     * @var string
     */
    private $rule = '';

    /**
     * @param $rule
     * @throws \Exception
     */
    public function setRule($rule)
    {
        $matches = [];
        if (!preg_match("/(.*)\.(.*)/", $rule, $matches) || sizeof($matches) !== 3) {
            throw new \Exception('Invalid rule: ' . $rule);
        }

        $tableName = trim($matches[1]);
        $fieldName = trim($matches[2]);

        if (empty($tableName)) {
            throw new \Exception('Wrong ignore rule "' . $rule . '", table name cannot be empty!');
        }

        if (empty($fieldName)) {
            throw new \Exception('Wrong ignore rule "' . $rule . '", field name cannot be empty!');
        }

        $this->tableName = $tableName;
        $this->fieldName = $fieldName;
    }

    /**
     * @return mixed
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param array $row
     * @return bool
     */
    public function shouldIgnore(array $row)
    {
        if (!isset($row[$this->fieldName])) {
            return false;
        }

        return true;
    }

    /**
     * IgnoreDataRowRule constructor.
     * @param $rule
     * @throws \Exception
     */
    public function __construct($rule)
    {
        $this->setRule($rule);
    }
}