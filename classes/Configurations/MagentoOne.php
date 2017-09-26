<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configurations;

use Voyage\Core\PlatformConfiguration;

/**
 * Class MagentoOne
 * @package Voyage\Configurations
 */
class MagentoOne extends PlatformConfiguration
{
    /**
     * MagentoOne constructor.
     */
    public function __construct()
    {
        $this->pathToConfig = VOYAGE_WORKING_DIR . '/app/etc/local.xml';
    }

    /**
     * @return array
     */
    protected function extract()
    {
        $result = parent::extract();

        if (!function_exists('simplexml_load_file')) {
            return $result;
        }

        $xml = simplexml_load_file($this->pathToConfig);
        $resources = $xml->xpath('//resources');

        if (!empty($resources)) {
            foreach ($resources as $resource) {
                foreach ($resource as $data) {
                    if (isset($data->connection)) {
                        if ($data->connection->active == 1 || $data->connection->active == 'true') {
                            if (isset($data->connection->host)) {
                                $result['host'] = $data->connection->host;
                            }

                            if (isset($data->connection->username)) {
                                $result['user'] = $data->connection->username;
                            }

                            if (isset($data->connection->password)) {
                                $result['pass'] = $data->connection->password;
                            }

                            if (isset($data->connection->dbname)) {
                                $result['name'] = $data->connection->dbname;
                            }

                            break;
                        }
                    }
                }
            }
        }


        return $result;
    }
}