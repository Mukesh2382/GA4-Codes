<?php
namespace Wisdmlabs;

if ( ! class_exists( '\Wisdmlabs\GA4_ECommerce' ) ) {
    abstract class GA4_ECommerce {
        private $downloads_data = array(
            // The download id.
            '20277' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '2', '4' ),
            ),
            '366223' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '366236' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '127679' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '9' ),
            ),
            '366218' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '366221' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '707478' => array( 
                'item_category' => 'LearnDash',
                'price_ids'     => array( '7', '3', '5', '2', '4', '6' ),
            ),
            '44670' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '9' ),
            ),
            '366225' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '368742' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '109665' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '8' ),
            ),
            '366227' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '368744' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '34202' => array(
                'item_category' => 'LearnDash',
                'is_free'       => true,
                'price_ids'     => array ( '2', '4' ),
            ),
            '368743' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            '479075' => array(
                'item_category' => 'LearnDash',
                'price_ids'     => array ( '1', '2' ),
            ),
            // WooCommerce Downloads
            '3212' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '2', '4' ),
            ),
            '878908' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '2', '0' ),
            ),
            '878897' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '1', '0' ),
            ),
            '6963' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '2', '4' ),
            ),
            '878912' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '1', '4' ),
            ),
            '878897' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '1', '4' ),
            ),
            '10055' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '2', '5', '7', '4', '6', '8' ),
            ),
            '399006' => array(
                'item_category' => 'WooCommerce',
                'is_free'       => true,
                'price_ids'     => array ( '2' ),
            ),
            '475758' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '1', '2' ),
            ),
            '878897' => array(
                'item_category' => 'WooCommerce',
                'price_ids'     => array ( '1', '4' ),
            ),
        );

        /**
         * Return the downloads data.
         */
        public function return_downloads_data() {
            return $this->downloads_data;
        }
    }
}
