<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

use Voyage\Routines\StringUtils;

/**
 * Class IgnoreItem
 * @package Voyage\Configuration
 */
class IgnoreRule
{
    /**
     * @var string
     */
    private $rule = '';
    /**
     * @var bool
     */
    private $ignoreDataOnly = false;

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param string $rule
     */
    public function setRule($rule)
    {
        $rule = trim($rule);
        $this->ignoreDataOnly = false;

        if (!empty($rule)) {
            if ($rule[0] == '~') {
                $rule = substr($rule, 1);
                $this->ignoreDataOnly = true;
            }
        }

        $this->rule = $rule;
    }

    /**
     * @return bool
     */
    public function isIgnoreDataOnly()
    {
        return $this->ignoreDataOnly;
    }

    /**
     * @return bool
     */
    public function isIgnoreFully()
    {
        return !$this->ignoreDataOnly;
    }

    /**
     * @param bool $ignoreDataOnly
     */
    public function setIgnoreDataOnly($ignoreDataOnly)
    {
        $this->ignoreDataOnly = $ignoreDataOnly;
    }

    /**
     * Check whether the given string item (should be a name of a table in most cases) should be ignored according to
     * the current ignore rule.
     * @param $item
     * @return bool
     */
    public function shouldIgnore($item)
    {
        return StringUtils::matchesPattern($item, $this->rule);
    }

    /**
     * IgnoreItem constructor.
     * @param $rule
     */
    public function __construct($rule)
    {
        $this->setRule($rule);
    }
}