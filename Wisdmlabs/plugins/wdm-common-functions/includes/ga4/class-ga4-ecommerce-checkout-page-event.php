<?php
namespace Wisdmlabs;

if ( ! class_exists( '\Wisdmlabs\GA4_ECommerce_Checkout_Page_Event' ) && class_exists( '\Wisdmlabs\GA4_ECommerce' ) ) {
    class GA4_ECommerce_Checkout_Page_Event extends GA4_ECommerce {
        protected static $instance = null;

        /**
         * Return single instance of the class.
         *
         * @return GA4_ECommerce_Checkout_Page_Event Return the class instance.
         */
        public static function get_instance() {
            if ( null == self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor.
         */
        public function __construct() {
            add_filter( 'wdm_ga4_remove_from_checkout_data', array( $this, 'return_remove_from_checkout_data' ), 15, 2 );
            add_action( 'wp_ajax_wdm_ga4_add_payment_info', array( $this, 'return_add_payment_info' ) );
            add_action( 'wp_ajax_nopriv_wdm_ga4_add_payment_info', array( $this, 'return_add_payment_info' ) );
        }

        /**
         * Return the checkout items data for localization.
         *
         * @return array Return the checkout items data for localization or empty array if checkout is empty.
         */
        public function return_localize_downloads_data( $cart_item_index = false ) {
            $localize_downloads_data = array();
            $cart_content_details    = edd_get_cart_content_details();

            if ( ! empty( $cart_content_details ) ) {
                $all_downloads_data = $this->return_downloads_data();

                if ( false === $cart_item_index ) {
                    foreach ( $cart_content_details as $index => $single_cart_product_data ) {
                        $download_id = $single_cart_product_data[ 'id' ];
    
                        $item_data[ 'item_id' ]       = strval($download_id);
                        $item_data[ 'item_name' ]     = $single_cart_product_data[ 'name' ];
                        $item_data[ 'price' ]         = $single_cart_product_data[ 'item_price' ];
                        $item_data[ 'discount' ]      = $single_cart_product_data[ 'discount' ];
                        $item_data[ 'index' ]         = $index;
                        $item_data[ 'item_brand' ]    = 'Wisdmlabs';
                        $item_data[ 'item_category' ] = $all_downloads_data[ $download_id ][ 'item_category' ];
                        $item_data[ 'quantity' ]      = $single_cart_product_data[ 'quantity' ];
    
                        $localize_downloads_data[ 'items' ][] = $item_data;
                    }
                } else {
                    $download_id = $cart_content_details[ $cart_item_index ][ 'id' ];
    
                    $item_data[ 'item_id' ]       = strval($download_id);
                    $item_data[ 'item_name' ]     = $cart_content_details[ $cart_item_index ][ 'name' ];
                    $item_data[ 'price' ]         = $cart_content_details[ $cart_item_index ][ 'item_price' ];
                    $item_data[ 'discount' ]      = $cart_content_details[ $cart_item_index ][ 'discount' ];
                    $item_data[ 'item_brand' ]    = 'Wisdmlabs';
                    $item_data[ 'item_category' ] = $all_downloads_data[ $download_id ][ 'item_category' ];
                    $item_data[ 'quantity' ]      = $cart_content_details[ $cart_item_index ][ 'quantity' ];

                    $localize_downloads_data[ 'single_item_data' ] = $item_data;
                }
            }

            return $localize_downloads_data;
        }

        public function return_remove_from_checkout_data( $ga4_data, $cart_item_index ) {
            $ga4_data            = $this->return_localize_downloads_data( $cart_item_index );
            $ga4_data[ 'value' ] = edd_get_cart_item_final_price( $cart_item_index );
            return $ga4_data;
        }

        public function return_add_payment_info() {
            $ga4_data            = $this->return_localize_downloads_data();
            $ga4_data[ 'value' ] = edd_get_cart_total();

            // $ga4_data['coupon'] = edd_get_cart_discounted_amount();
            wp_send_json($ga4_data);
            wp_die();
        }

    }
    GA4_ECommerce_Checkout_Page_Event::get_instance();
}
