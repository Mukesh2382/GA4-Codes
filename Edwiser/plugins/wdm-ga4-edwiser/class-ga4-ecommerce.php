<?php
namespace Edwiser;

if ( ! class_exists( '\Edwiser\GA4_ECommerce' ) ) {
    abstract class GA4_ECommerce {
        private $downloads_data = array(
            // The download id.
            '95719' => array(
                'price_ids' => array ( '1' , '2'),
            ),
            '275424' => array(
                'price_ids' => array ( '1' , '2'),
            ),
            '7249' => array(
                'price_ids' => array ( '2' , '3' ),
            ),
            '76021' => array(
                'price_ids' => array ( '1' , '3' ),
            ),
            '251876' => array(
                'price_ids' => array ( '1' , '2' ),
            ),
            '88840' => array(
                'price_ids' => array ( '1' , '2' ),
            ),
            '59808' => array(),
            '223403' => array(
                'price_ids' => array( '1', '2',),
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
