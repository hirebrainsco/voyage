<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configuration;

use Voyage\Core\Configuration as CoreConfiguration;

/**
 * Class Ignore
 * @package Voyage\Configuration
 */
class Ignore extends ConfigFile
{
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
        $template .= '# A list of tables which should be ignored. Include tables which contain debug, log or binary data to this list.' . PHP_EOL;
        $template .= '# Add one table name per line. A table without any parameters will be ignored completely (data and structure). ' . PHP_EOL;
        $template .= '# If you would like to keep tracking changes of table structure and but ignore data then add a "~" before table name, ' . PHP_EOL;
        $template .= '# For example:  ' . PHP_EOL;
        $template .= '#    ~users - this will record only changes in structure of the `users` table.' . PHP_EOL;
        $template .= '#    users - this will completely ignore table `users`.' . PHP_EOL;
        $template .= '#' . PHP_EOL . PHP_EOL;

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

            $ignoreList[] = new IgnoreRule($rule);
        }

        return $ignoreList;
    }

    /**
     * @param $tableName
     * @return bool
     */
    public static function shouldIgnore($tableName)
    {
        if ($tableName == CoreConfiguration::getInstance()->getMigrationsTableName()) {
            return true;
        }

        $ignore = new Ignore();
        $ignoreList = $ignore->getIgnoreList();
        $result = false;

        if (!empty($ignoreList)) {
            foreach ($ignoreList as $item) {
                if ($item->shouldIgnore($tableName)) {
                    $result = true;
                    break;
                }
            }
        }

        unset($ignore);
        return $result;
    }
}