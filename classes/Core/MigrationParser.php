<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Core;


use Voyage\Routines\DatabaseRoutines;

class MigrationParser
{
    const Apply = '# @APPLY';
    const Rollback = '# @ROLLBACK';

    private $contents = '';

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param string $contents
     */
    public function setContents($contents)
    {
        $contents = DatabaseRoutines::replaceTableNames($contents);
        $this->contents = $contents;
    }

    /**
     * @param $startMarker
     * @param $endMarker
     * @return array
     * @throws \Exception
     */
    private function processContents($startMarker, $endMarker)
    {
        $contents = [];
        $harvesting = false;
        $queryCode = '';

        $lines = explode(PHP_EOL, $this->contents);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (!$harvesting && $line == $startMarker) {
                $harvesting = true;
                $queryCode = '';
                continue;
            }

            if ($harvesting && $line == $endMarker) {
                $harvesting = false;
                $queryCode = '';
                continue;
            }

            if ($line[0] == '#' || !$harvesting) {
                continue;
            }

            $queryCode .= ' ' . $line;
            $matches = [];

            if (preg_match("/\A\s*(.*)\s*;\s*\z/", $queryCode, $matches)) {
                $queryCode = '';
                $contents[] = trim($matches[1]);
            }
        }

        return $contents;
    }

    /**
     * @return array
     */
    public function getApply()
    {
        return $this->processContents(self::Apply, self::Rollback);
    }

    /**
     * @return array
     */
    public function getRollback()
    {
        return $this->processContents(self::Rollback, self::Apply);
    }
}