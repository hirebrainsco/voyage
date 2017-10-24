<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

/**
 * Class Replacements
 * @package Voyage\Core
 */
class Replacements
{
    /**
     * @var array
     */
    private $replacements = [];

    /**
     * Replacements constructor.
     * @param array $replacements
     */
    public function __construct(array $replacements)
    {
        $this->replacements = $replacements;
    }

    /**
     * @param string $code
     * @return string
     */
    public function replace($code)
    {
        if (empty($this->replacements)) {
            return $code;
        }

        $code = $this->replaceInsert($code);
        $code = $this->replaceUpdate($code);
        $code = $this->replaceDelete($code);

        return $code;
    }

    /**
     * @param string $code
     * @return string
     */
    private function replaceInsert($code)
    {
        if (false === stripos($code, 'INSERT INTO ')) {
            return $code;
        }

        // Parse fields
        $matches = [];
        if (!preg_match("/INSERT INTO\s+`(.*)`\s+\((.*)\)\s+VALUES\s*\((.*)\)/i", $code, $matches)) {
            return $code;
        }

        $query = 'INSERT INTO `' . $matches[1] . '` (' . $matches[2] . ') VALUES (';

        $values = $this->parseInsertValues($matches[3]);
        $sz = sizeof($values);
        $i = 0;

        foreach ($values as $value) {
            $query .= self::prepareValue($value, $this->replacements, false);

            if ($i != $sz - 1) {
                $query .= ',';
            }

            $i++;
        }

        $query .= ')';
        return $query;
    }

    /**
     * @param $value
     * @param array $replacements
     * @param bool $pack
     * @return mixed|string
     */
    public static function prepareValue($value, array $replacements, $pack = true)
    {
        if (!is_string($value)) {
            if (!$value) {
                return 'NULL';
            }
            return $value;
        }

        if (empty($value)) {
            return '\'\'';
        }

        if (!empty($replacements)) {
            $serializedData = @unserialize($value);
            $quoteEscaped = false;
            if (false == $serializedData) {
                $serializedData = @unserialize(str_replace("\'", "'", $value));
                $quoteEscaped = $serializedData !== false;
            }

            if (false !== $serializedData) {
                array_walk_recursive($serializedData, function (&$item, $key) use ($replacements, $pack) {
                    if (is_string($item)) {
                        foreach ($replacements as $replacement) {
                            if ($pack) {
                                $item = str_replace($replacement[1], $replacement[0], $item);
                            } else {
                                $item = str_replace($replacement[0], $replacement[1], $item);
                            }
                        }
                    }
                });

                $value = serialize($serializedData);
                if ($quoteEscaped) {
                    $value = str_replace("'", "\'", $value);
                }
            } else {
                foreach ($replacements as $replacement) {
                    if ($pack) {
                        $value = str_replace($replacement[1], $replacement[0], $value);
                    } else {
                        $value = str_replace($replacement[0], $replacement[1], $value);
                    }
                }
            }
        }

        if ($pack) {
            $value = str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
        }

        return '\'' . str_replace(["\r", "\n", "\t"], ['\\r', '\\n', '\\t'], $value) . '\'';
    }

    /**
     * @param $code
     * @return string
     */
    private function replaceUpdate($code)
    {
        if (false === ($pos = stripos($code, 'UPDATE ')) || $pos > 0) {
            return $code;
        }

        $matches = [];
        if (!preg_match("/UPDATE(.*)SET(.*)WHERE(.*)/i", $code, $matches) || empty($matches[2])) {
            return $code;
        }

        $values = $this->parseUpdateValues($matches[2]);
        if (empty($values)) {
            return $code;
        }

        $code = 'UPDATE ' . $matches[1] . ' SET ';

        $sz = sizeof($values);
        $i = 0;
        foreach ($values as $key => $value) {
            $code .= $key . '=' . self::prepareValue($value, $this->replacements, false);
            if ($i < $sz - 1) {
                $code .= ', ';
            }
            $i++;
        }

        $code .= ' WHERE ' . $matches[3];

        return $code;
    }

    /**
     * @param $code
     * @return string
     */
    private function replaceDelete($code)
    {
        if (false === ($pos = stripos($code, 'DELETE ')) || $pos > 0) {
            return $code;
        }

        $matches = [];
        if (!preg_match("/DELETE FROM(.*)WHERE(.*)/i", $code, $matches) || empty($matches[2])) {
            return $code;
        }

        $values = $this->parseDeleteValues($matches[2]);
        if (empty($values)) {
            return $code;
        }

        $code = 'DELETE FROM ' . $matches[1] . ' WHERE ';

        $sz = sizeof($values);
        $i = 0;
        foreach ($values as $key => $value) {
            $code .= $key . '=' . self::prepareValue($value, $this->replacements, false);
            if ($i < $sz - 1) {
                $code .= ' AND ';
            }
            $i++;
        }

        return $code;
    }

    /**
     * @param $values
     * @return array
     */
    private function parseInsertValues($values)
    {
        $sz = strlen($values);
        $result = [];

        $valueStarted = false;
        $startedWithQuote = false;
        $item = '';
        $startedIndex = 0;

        for ($i = 0; $i < $sz; $i++) {
            $chr = $values[$i];

            if (!$valueStarted) {
                if ($chr == "\t" || $chr == ' ' || $chr == ',') {
                    continue;
                }

                $valueStarted = true;
                $startedWithQuote = false;
                $startedIndex = $i;
                $item = '';

                if ($chr == "'") {
                    $startedWithQuote = true;
                    continue;
                }

            }

            if ($chr == "'" && $startedWithQuote || !$startedWithQuote && $chr == ',') {
                $prevChar = $values[$i - 1];
                $nextChar = $i < $sz - 1 ? $values[$i + 1] : '';

                if ($chr == "," || $chr == "'" && $prevChar == "'" && $startedIndex == $i - 1 || $chr == "'" && $prevChar != "\\" && $prevChar != "'" && $nextChar != "'") {
                    $valueStarted = false;
                    $result[] = $item;
                    $item = '';
                    continue;
                }
            }

            $item .= $chr;
        }

        if (!empty($item)) {
            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param $values
     * @return array
     */
    private function parseUpdateValues($values)
    {
        $values = trim($values);
        $sz = strlen($values);
        $result = [];

        $key = '';
        $value = '';
        $valueStarted = false;
        $startedWithQuote = false;
        $assignOperator = false;

        for ($i = 0; $i < $sz; $i++) {
            $chr = $values[$i];
            if (!$valueStarted) {
                if ($chr == '=') {
                    $assignOperator = true;
                    continue;
                }

                if ($assignOperator) {
                    if ($chr == '\'') {
                        $valueStarted = true;
                        $startedWithQuote = true;
                        continue;
                    } else if ($chr != ' ' && $chr != "\t") {
                        $valueStarted = true;
                        $startedWithQuote = false;
                    } else {
                        continue;
                    }
                } else if ($chr != ',') {
                    $key .= $chr;
                }
            }

            if ($valueStarted) {
                if ($chr == "'" && $startedWithQuote) {
                    if ($i == $sz - 1 || $values[$i + 1] != '\'' && $values[$i - 1] != '\'' && $values[$i - 1] != '\\') {
                        $key = trim($key);
                        $valueStarted = false;
                        $assignOperator = false;
                        $result[$key] = $value;
                        $value = '';
                        $key = '';
                        continue;
                    }
                }

                $value .= $chr;
            }
        }

        if (!empty($value)) {
            $key = trim($key);
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param $values
     * @return array
     */
    private function parseDeleteValues($values)
    {
        $values = str_ireplace(' AND', ',', $values);
        $sz = strlen($values);
        $result = [];

        $key = '';
        $value = '';
        $valueStarted = false;
        $startedWithQuote = false;
        $assignOperator = false;

        for ($i = 0; $i < $sz; $i++) {
            $chr = $values[$i];

            if (!$valueStarted) {
                if ($chr == '=') {
                    $assignOperator = true;
                    continue;
                }

                if ($assignOperator) {
                    if ($chr == '\'') {
                        $valueStarted = true;
                        $startedWithQuote = true;
                        continue;
                    } else if ($chr != ' ' && $chr != "\t") {
                        $valueStarted = true;
                        $startedWithQuote = false;
                    } else {
                        continue;
                    }
                } else if ($chr != ',') {
                    $key .= $chr;
                }
            }

            if ($valueStarted) {
                if ($chr == "'" && $startedWithQuote) {
                    if ($i == $sz - 1 || $values[$i + 1] != '\'' && $values[$i - 1] != '\'' && $values[$i - 1] != '\\') {
                        $key = trim($key);
                        $valueStarted = false;
                        $assignOperator = false;
                        $result[$key] = $value;
                        $value = '';
                        $key = '';
                        continue;
                    }
                }

                $value .= $chr;
            }
        }

        if (!empty($value)) {
            $key = trim($key);
            $result[$key] = $value;
        }

        return $result;
    }
}