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
    protected $name = 'magento1';

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
                    if (isset($data->table_prefix)) {
                        $result['prefix'] = $data->table_prefix;
                    } else if (isset($data->connection)) {
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
                        } // if connection is active
                    } // if isset connection
                } // foreach resource
            } // foreach resources
        } // if not empty resources

        return $result;
    }

    public function getIgnoreTables()
    {
        return [
            '~*_index',
            '~*_log',
            '~log_*',
            '~index_*',
            '~*_cache',
            '~*_debug',
            '~adminnotification_inbox',
            '~api_session',
            '~amasty_geoip_*',
            '~amasty_audit_*',
            '~aw_advancednewsletter_*',
            '~xmlconnect_history',
            '~salesrule_coupon_usage',
            '~sales_payment_transaction',
            '~sales_flat_*',
            '~review_status',
            '~poll_answer',
            '~persistent_session',
            '~paypaluk_api_debug',
            '~paypal_payment_transaction',
            '~oscommerce_orders_status_history',
            '~oauth_token',
            '~oauth_nonce',
            '~mailchimp_errors',
            '~gtspeed_stat',
            '~gtspeed_image',
            '~catalogsearch_*',
            '~cataloginventory_stock_status*',
            '~catalog_product_index*',
            '~catalog_category_product_index*',
            '~catalog_category_flat*',
            '~catalog_category_entity*',
            '~catalog_category_anc_*',
            '~blog_url_rewrite',
            '~aw_core_logger'
        ];
    }
}