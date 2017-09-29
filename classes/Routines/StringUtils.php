<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Routines;


class StringUtils
{
    /**
     * Remove comments from the given string.
     * @param $contents
     * @return string
     */
    public static function removeComments($contents)
    {
        $contents = preg_replace("/(.*)\#(.*)$/im", '$1', $contents);
        return $contents;
    }

    /**
     * Remove empty lines from the given string.
     * @param $contents
     * @return string
     */
    public static function removeEmptyLines($contents)
    {
        $contents = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/im", "\n", $contents);
        return $contents;
    }

    /**
     * Check whether given string matches pattern.
     * @param $string
     * @param $pattern
     * @return bool
     */
    public static function matchesPattern($string, $pattern)
    {
        if (empty($pattern)) {
            return false;
        }

        if (strpos($pattern, '*') === false) {
            // No wildcards, we can compare strings equally.
            return strcasecmp($pattern, $string) === 0;
        }

        $pattern = str_replace('*', '(.*)', $pattern);
        return 1 == preg_match('/' . $pattern . '/i', $string);
    }
}