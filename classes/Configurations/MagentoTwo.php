<?php
/**
 * Copyright (c) 2017 HireBrains
 * Author: Dmitry Martynenko
 * Email: dmitry@hirebrains.co
 */

namespace Voyage\Configurations;

use Voyage\Core\PlatformConfiguration;

/**
 * Class MagentoTwo
 * @package Voyage\Configurations
 */
class MagentoTwo extends PlatformConfiguration
{
    protected $name = 'magento2';

    /**
     * MagentoOne constructor.
     */
    public function __construct()
    {
        $this->pathToConfig = VOYAGE_WORKING_DIR . '/app/etc/env.php';
    }

    /**
     * @return array
     */
    protected function extract()
    {
        $result = parent::extract();
        $config = include($this->pathToConfig);

        if (empty($config) || !isset($config['db']) || !isset($config['db']['connection']) || empty($config['db']['connection'])) {
            return $result;
        }

        if (isset($config['db']['table_prefix'])) {
            $result['prefix'] = $config['db']['table_prefix'];
        }

        foreach ($config['db']['connection'] as $connection) {
            if (!isset($connection['active']) || !$connection['active']) {
                continue;
            }

            if (isset($connection['host'])) {
                $result['host'] = $connection['host'];
            }

            if (isset($connection['dbname'])) {
                $result['name'] = $connection['dbname'];
            }

            if (isset($connection['username'])) {
                $result['user'] = $connection['username'];
            }

            if (isset($connection['password'])) {
                $result['pass'] = $connection['password'];
            }

            break;
        }

        return $result;
    }

    public function getIgnoreTables()
    {
        return [
            '~*_flat',
            '~*_fulltext*',
            '~*_index*',
            '~*cache*',
            '~session',
            '~tax_order_aggregated_*',
            '~url_rewrite',
            '~vault_payment_token*',
            '~sequence_*',
            '~*_log',
            '~search_query',
            '~search_synonyms',
            '~persistent_session',
            '~oauth_*',
            '~newsletter_problem',
            '~newsletter_queue',
            '~newsletter_queue_link',
            '~newsletter_queue_store_link',
            '~newsletter_subscriber',
            '~mview_state',
            '~*_transaction',
            '~mst_core_urlrewrite',
            '~mana_filter*',
            '~indexer_state',
            '~import_history'
        ];
    }
}