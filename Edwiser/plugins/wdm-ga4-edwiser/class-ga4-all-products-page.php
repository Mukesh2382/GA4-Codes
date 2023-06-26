<?php
namespace Edwiser;

if( ! class_exists('\Edwiser\GA4_All_Products_Page') && class_exists('\Edwiser\GA4_Ecommerce')){
    class GA4_All_Products_Page extends GA4_ECommerce {
        protected static $instance = null;

        /**
         * Return single instance of the class.
         *
         * @return GA4_ECommerce_Checkout_Page Return the class instance.
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
            if ( is_page( 'products' ) ) {
                return true;
            }

            return false;
        }

        public function enqueue_scripts() {
            $is_product_page = $this->is_products_landing_page();

            if ( $is_product_page && ! empty( $localize_all_products_data = $this->return_all_products_data() ) ) {
            
                wp_enqueue_script(
                    'view-item-list-script',
                    plugin_dir_url(__FILE__)  . 'assets/ga4_ecommerce_view_item_list.js',
                    array( 'jquery' ) ,
                    false
                );
                wp_localize_script( 'view-item-list-script', 'wdm_ga4_view_item_list_downloads_data', $localize_all_products_data );
            }
        }

        public function return_all_products_data(){
            $all_data                   = $this->return_downloads_data();
            $localize_all_products_data = array();

            $localize_all_products_data[ 'item_list_id' ]   = 'All-Edwiser-Products';
            $localize_all_products_data[ 'item_list_name' ] = 'All-Edwiser-Products';
            $index= 0;
            foreach ($all_data as $download_id => $download_data){
                //$download_data   = $all_downloads_data[ $download_id ];
                $download_object = edd_get_download( $download_id );

                $item_data[ 'item_id' ] = strval($download_id);
                $item_data[ 'item_name' ] = $download_object->post_title;

                $item_data[ 'item_list_id' ]   = 'All-Edwiser-Products';
                $item_data[ 'item_list_name' ] = 'All-Edwiser-Products';
                $item_data['index'] = $index;
                $item_data[ 'price' ] = $download_object->get_price();

                $localize_all_products_data[ 'items' ][] = $item_data;
                $index++;
            }
            return $localize_all_products_data;
        }
    }
    GA4_All_Products_Page::get_instance();
}
    