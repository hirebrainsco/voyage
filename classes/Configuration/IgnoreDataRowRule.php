<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

/**
 * Class IgnoreDataRowRule
 * @package Voyage\Configuration
 */
class IgnoreDataRowRule
{
    /* Check value rules (optimization) */
    const Equal = 0;
    const StartsWith = 1;
    const EndsWith = 2;
    const Contains = 3;

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
    private $value = '';

    /**
     * @var string
     */
    private $rule = '';

    /**
     * @var int
     */
    private $checkValueRule = IgnoreDataRowRule::Equal;

    /**
     * @param $rule
     * @throws \Exception
     */
    public function setRule($rule)
    {
        $matches = [];
        if (!preg_match("/(.*)\.(.*)=(.*)/", $rule, $matches) || sizeof($matches) !== 4) {
            throw new \Exception('Invalid rule: ' . $rule);
        }

        $tableName = trim($matches[1]);
        $fieldName = trim($matches[2]);
        $value = trim($matches[3]);

        if (empty($tableName)) {
            throw new \Exception('Wrong ignore rule "' . $rule . '", table name cannot be empty!');
        }

        if (empty($fieldName)) {
            throw new \Exception('Wrong ignore rule "' . $rule . '", field name cannot be empty!');
        }

        $this->tableName = $tableName;
        $this->fieldName = $fieldName;
        $this->rule = $rule;
        $this->checkValueRule = $this->getCheckValueRuleFromValue($value);

        if ($value[0] == '*') {
            $value = substr($value, 1);
        }

        $lastIndex = strlen($value) - 1;
        if ($value[$lastIndex] == '*') {
            $value = substr($value, 0, -1);
        }

        $this->value = $value;
    }

    /**
     * @param $value
     * @return int
     */
    private function getCheckValueRuleFromValue($value)
    {
        if (empty($value)) {
            return IgnoreDataRowRule::Equal;
        }

        $lastIndex = strlen($value) - 1;
        if ($value[0] == '*' && $value[$lastIndex] == '*') {
            return IgnoreDataRowRule::Contains;
        } else if ($value[0] == '*') {
            return IgnoreDataRowRule::EndsWith;
        } else if ($value[$lastIndex] == '*') {
            return IgnoreDataRowRule::StartsWith;
        }

        return IgnoreDataRowRule::Equal;
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
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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

        $value = $row[$this->fieldName];
        switch ($this->checkValueRule) {
            case self::Contains:
                if (empty($this->value)) {
                    // Ignore all
                    return true;
                }

                return strpos($value, $this->value) !== false;

            case self::StartsWith:
                return strpos($value, $this->value) === 0;

            case self::EndsWith:
                return strstr($value, $this->value) === $this->value;

            default:
                return $value == $this->value;
        }
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