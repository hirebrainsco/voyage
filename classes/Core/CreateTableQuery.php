<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;

class CreateTableQuery extends QueryParser
{
    /**
     * @param $query
     * @return bool
     */
    public static function isCreateTable($query)
    {
        return stripos($query, 'CREATE TABLE') !== false;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        $matches = [];
        preg_match('/CREATE TABLE\s*`([^`]*)`\s*\((.*)\)(.*)/i', $this->getQuery(), $matches);

        if (empty($matches) || sizeof($matches) !== 4 || empty($matches[1])) {
            return '';
        }

        return trim($matches[1]);
    }
}