<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

use Voyage\Core\Configuration as CoreConfiguration;
use Voyage\Core\Configuration;

/**
 * Class Ignore
 * @package Voyage\Configuration
 */
class Ignore extends ConfigFile
{
    const IgnoreFully = 1;
    const IgnoreDataOnly = 2;
    const DontIgnore = 3;

    /**
     * @var string
     */
    protected $filename = 'ignore';

    /**
     * @return string
     */
    protected function getTemplate()
    {
        $template = '#' . PHP_EOL;
        $template .= '# Ignore List' . PHP_EOL;
        $template .= '#' . PHP_EOL;
        $template .= '# A list of tables & data values which should be ignored. Include tables which contain debug, log or binary data to this list.' . PHP_EOL;
        $template .= '# Add one table name per line. A table without any parameters will be ignored completely (data and structure). ' . PHP_EOL;
        $template .= '# If you would like to keep tracking changes of table structure and but ignore data then add a "~" before table name, ' . PHP_EOL;
        $template .= '# For example:  ' . PHP_EOL;
        $template .= '#    ~users - this will record only changes in structure of the `users` table.' . PHP_EOL;
        $template .= '#    users - this will completely ignore table `users`.' . PHP_EOL;
        $template .= '#    users.name=*john* - this will ignore records in table `users` where field `name` contains word \'john\'.' . PHP_EOL;
        $template .= '#' . PHP_EOL . PHP_EOL;
        $template .= 'tmp_voyage_*' . PHP_EOL;

        $environment = $this->getSender()->getEnvironment();
        if (is_object($environment) && is_object($environment->getPlatformConfiguration())) {
            $ignoreList = $environment->getPlatformConfiguration()->getIgnoreTables();

            if (!empty($ignoreList)) {
                $prefix = $environment->getDatabaseSettings()->getTablePrefix();
                if (empty($prefix)) {
                    $prefix = $environment->getPlatformConfiguration()->getDefaultTablePrefix();
                }

                foreach ($ignoreList as $tableName) {
                    $tableName = trim($tableName);
                    if (empty($tableName)) {
                        continue;
                    }

                    if (!empty($prefix)) {
                        if ($tableName[0] == '~') {
                            $tableName = '~' . $prefix . substr($tableName, 1);
                        } else {
                            $tableName = $prefix . $tableName;
                        }
                    }

                    $template .= $tableName . PHP_EOL;
                }
            }
        }

        return $template;
    }

    /**
     * @return array
     */
    public function getIgnoreList()
    {
        static $ignoreList = null;
        if (is_array($ignoreList)) {
            return $ignoreList;
        }

        $configData = $this->getConfigContents();
        $ignoreRules = explode("\n", $configData);
        unset($configData);

        foreach ($ignoreRules as $rule) {
            $rule = trim($rule);
            if (empty($rule)) {
                continue;
            }

            if (strpos($rule, '=') !== false) {
                if (!isset($ignoreList['data'])) {
                    $ignoreList['data'] = [];
                }

                $rule = new IgnoreDataRowRule($rule);
                if (!isset($ignoreList['data'][$rule->getTableName()])) {
                    $ignoreList['data'][$rule->getTableName()] = [];
                }

                $ignoreList['data'][$rule->getTableName()][] = $rule;
            } else if (strpos($rule, '.') !== false) {
                if (!isset($ignoreList['field'])) {
                    $ignoreList['field'] = [];
                }

                $rule = new IgnoreDataFieldRule($rule);
                if (!isset($ignoreList['field'][$rule->getTableName()])) {
                    $ignoreList['field'][$rule->getTableName()] = [];
                }

                $ignoreList['field'][$rule->getTableName()][] = $rule->getFieldName();
            } else {
                if (!isset($ignoreList['tables'])) {
                    $ignoreList['tables'] = [];
                }
                $ignoreList['tables'][] = new IgnoreTableRule($rule);
            }
        }

        return $ignoreList;
    }

    /**
     * @param $tableName
     * @return bool
     */
    public static function shouldIgnore($tableName)
    {
        $tmpTablesPattern = '/^' . Configuration::getInstance()->getTempTablePrefix() . '(.*)$/';
        if ($tableName == CoreConfiguration::getInstance()->getMigrationsTableName() || preg_match($tmpTablesPattern, $tableName)) {
            return Ignore::IgnoreFully;
        }

        $ignore = new Ignore();
        $ignoreList = $ignore->getIgnoreList();
        $result = Ignore::DontIgnore;

        if (!empty($ignoreList) && isset($ignoreList['tables']) && !empty($ignoreList['tables'])) {
            foreach ($ignoreList['tables'] as $item) {
                if ($item->shouldIgnore($tableName)) {
                    $result = $item->isIgnoreFully() ? Ignore::IgnoreFully : Ignore::IgnoreDataOnly;
                    break;
                }
            }
        }

        unset($ignore);
        return $result;
    }
}