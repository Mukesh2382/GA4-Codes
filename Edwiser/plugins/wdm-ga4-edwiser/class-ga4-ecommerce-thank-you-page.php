<?php
namespace Edwiser;

if ( ! class_exists( '\Edwiser\GA4_ECommerce_Thank_You_Page' ) && class_exists( '\Edwiser\GA4_ECommerce' ) ) {
    class GA4_ECommerce_Thank_You_Page extends GA4_ECommerce {
        protected static $instance = null;

        protected $thank_you_page_slug_list = array(
            'purchase-confirmation',
        );

        /**
         * Return single instance of the class.
         *
         * @return GA4_ECommerce_Thank_You_Page Return the class instance.
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
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 45 );
        }

        /**
         * This function checks if the current page is thank you page.
         */
        public function is_thank_you_page() {
            $thank_you_page_slug_list = $this->thank_you_page_slug_list;

            foreach ( $thank_you_page_slug_list as $slug ) {
                if ( is_page( $slug ) ) {
                    return true;
                }
            }

            return false;
        }

        public function enqueue_scripts() {
            if ( $this->is_thank_you_page() ) {
                $localize_downloads_data = $this->return_localize_downloads_data();

                if ( ! empty( $localize_downloads_data ) ) {
                    wp_enqueue_script(
                        'wdm_ga4_ecommerce_purcahse_event_js',
                        plugin_dir_url(__FILE__)  . 'assets/ga4_ecommerce_purchase_event.js',
                        array( 'jquery' ),
                        filemtime( plugin_dir_url(__FILE__)  . 'assets/ga4_ecommerce_purchase_event.js' ),
                        true
                    );
                    wp_localize_script( 'wdm_ga4_ecommerce_purcahse_event_js', 'wdm_ga4_ecommerce_purchase_event_data', $localize_downloads_data );
                }
            }
        }

        /**
         * Return the downloads data for localization.
         */
        public function return_localize_downloads_data() {
            $localize_downloads_data = array();
            $get_purchase_data       = edd_get_purchase_session();
            if ( ! empty( $get_purchase_data ) ) {
                $downloads_data                              = $this->return_downloads_data();
                $localize_downloads_data[ 'value' ]          = floatval($get_purchase_data[ 'price' ]);
                $localize_downloads_data[ 'transaction_id' ] = edd_get_purchase_id_by_key( $get_purchase_data[ 'purchase_key' ] );
                //$localize_downloads_data[ 'coupon' ]         = $get_purchase_data[ 'user_info' ][ 'discount' ];
                $coupon_code = $get_purchase_data[ 'user_info' ][ 'discount' ];
                if($coupon_code === ''){
                    $localize_downloads_data[ 'coupon' ] = 'Not Applied';
                }
                else{
                    $localize_downloads_data[ 'coupon' ]     = $get_purchase_data[ 'user_info' ][ 'discount' ];
                }

                foreach ( $get_purchase_data[ 'cart_details' ] as $index => $single_cart_item) {
                    $download_id = $single_cart_item[ 'id' ];

                    $items_data[ 'item_id' ]       = strval($download_id);
                    $items_data[ 'item_name' ]     = $single_cart_item[ 'name' ];
                    $items_data[ 'discount' ]      = $single_cart_item[ 'discount' ];
                    $items_data[ 'index' ]         = $index;
                    $items_data['item_brand']      = 'Edwiser';
                    $items_data[ 'price' ]         = floatval($single_cart_item[ 'item_price' ]);
                    $items_data[ 'quantity' ]      = $single_cart_item[ 'quantity' ];

                    $localize_downloads_data[ 'items' ][] = $items_data;
                }
            }
            return $localize_downloads_data;
        }
    }
    GA4_ECommerce_Thank_You_Page::get_instance();
}
