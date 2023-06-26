<?php
namespace Wisdmlabs;

if( ! class_exists('\Wisdmlabs\GA4_All_Products_Page_Event') && class_exists('\Wisdmlabs\GA4_Ecommerce')){
    class GA4_All_Products_Page_Event extends GA4_ECommerce {
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
           add_action('wp_enqueue_scripts', array($this,'enqueue_scripts')); 
        }

        /**
         * Enqueue scripts.
         *
         * @return void
         */
        public function is_products_landing_page() {
            if ( is_page( 'premium-wordpress-plugins' ) ) {
                return true;
            }

            return false;
        }

        public function enqueue_scripts() {
            $is_product_page = $this->is_products_landing_page();

            if ( $is_product_page && ! empty( $localize_all_products_data = $this->return_all_products_data() ) ) {
            
                wp_enqueue_script(
                    'view-item-list-script',
                    plugins_url( 'assets/js/ga4_ecommerce_view_item_list.js', __FILE__),
                    array( 'jquery' ) ,
                    filemtime( plugins_url( 'assets/js/ga4_ecommerce_view_item_list.js', __FILE__) ),
                    false
                );
                wp_localize_script( 'view-item-list-script', 'wdm_ga4_view_item_list_downloads_data', $localize_all_products_data );
            }
        }

        public function return_all_products_data(){
            $all_data                   = $this->return_downloads_data();
            $localize_all_products_data = array();

            $localize_all_products_data[ 'item_list_id' ]   = 'All-Wisdm-Products';
            $localize_all_products_data[ 'item_list_name' ] = 'All-Wisdm-Products';

            foreach ($all_data as $download_id => $download_data){
                //$download_data   = $all_downloads_data[ $download_id ];
                $download_object = edd_get_download( $download_id );

                $item_data[ 'item_id' ] = strval($download_id);
                $item_data[ 'item_name' ] = $download_object->post_title;
                $item_data[ 'item_category' ] = $download_data[ 'item_category' ];

                $item_data[ 'item_list_id' ]   = 'All-Wisdm-Products';
                $item_data[ 'item_list_name' ] = 'All-Wisdm-Products';

                if ( ! empty( $download_data[ 'is_free' ] ) ) {
                    $item_data[ 'price' ] = 0;
                } else {
                    $single_yearly_price = edd_get_price_option_amount( $download_id, $download_data[ 'price_ids' ][ '0' ] );
                    $item_data[ 'price' ] = floatval(edd_sanitize_amount( $single_yearly_price ));
                }

                $localize_all_products_data[ 'items' ][] = $item_data;
            }
            return $localize_all_products_data;
        }
    }
    GA4_All_Products_Page_Event::get_instance();
}
    