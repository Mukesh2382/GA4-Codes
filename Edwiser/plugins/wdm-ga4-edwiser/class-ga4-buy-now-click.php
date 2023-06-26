<?php
namespace Edwiser;

if ( ! class_exists( '\Edwiser\GA4_Buy_Now_Click' && class_exists( '\Edwiser\GA4_ECommerce' ) )) {
    class GA4_Buy_Now_Click extends GA4_ECommerce  {
       protected static $instance = null;

       protected $landing_pages_with_downloads = array(
        'remui' => array(
            '95719', '7249', '223403',
        ),
        'bridge' => array(
            '76021', '251876',
        ),
        'reports' => array(
            '275424', '251876',
        ),
        'all-access-pass' => array(
            '251876',
        ),
        'rapidgrader' => array(
            '88840' , '223403' , '251876',
        ),
        'forms' => array(
           '59808' , '251876',
        ),
       );

       /**
         * Return single instance of the class.
         *
         * @return GA4_Buy_Now_Click Return the class instance.
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
            //$this->return_trydemo_data();
        }


        /**
         * This function checks if the current page is product landing page.
         */
        public function is_product_landing_page() {
            global $wp_query;
            foreach ( $this->landing_pages_with_downloads as $url => $value) {

                if ( is_page( $url ) ) {
                    return $value;
                }
            }
            return false;
        }

        public function enqueue_scripts() {
            
            $localize_downloads_data = $this->return_localize_downloads_data();
            if ( ! empty( $localize_downloads_data )) {
                wp_enqueue_script(
                    'wdm_ga4_ecommerce_buy_now_click_js',
                    plugin_dir_url(__FILE__)  . 'assets/ga4_ecommerce_buy_now_click.js',
                    array( 'jquery' ),
                    filemtime( plugin_dir_url(__FILE__)  . 'assets/ga4_ecommerce_buy_now_click.js' ),
                    true
                );
                wp_localize_script( 'wdm_ga4_ecommerce_buy_now_click_js', 'wdm_ga4_downloads_data', $localize_downloads_data );
            }
        }


        // public function return_trydemo_data(){
        //     $demo_type = '';
        //     if( is_page( 'remui' ) ){
        //         $demo_type = 'remui';
        //     }
        //     if (is_page('reports')){
        //         $demo_type = 'reports';
        //     }
            
        //     error_log('This is demo type: ');
        //     error_log(print_r($demo_type,1));
        //     return $demo_type;
        // }
        
        public function return_localize_downloads_data() {
            $landing_page_download_ids = $this->is_product_landing_page();
            $localize_downloads_data   = array();
         
            if ( ! empty( $landing_page_download_ids ) ) {
                $all_downloads_data = $this->return_downloads_data();
                foreach ( $landing_page_download_ids as $download_id ) {
                    $download_data   = $all_downloads_data[ $download_id ];
                    $download_object = edd_get_download( $download_id );
                    

                    $localize_downloads_data[ $download_id ][ 'item_id' ] = strval($download_id);
                    $localize_downloads_data[ $download_id ][ 'item_name' ] = $download_object->post_title;

                    if(! array_key_exists('price_ids',$download_data[$download_id])){
                        $localize_downloads_data[ $download_id ][ 'single_price_amount' ] = $download_object->get_price();
                    }
                     else {
                        $variable_prices = $download_object->get_prices();
                        foreach ( $variable_prices as $variable_price_id => $variable_price_data ) {
                            $localize_downloads_data[ $download_id ][ 'variable_prices' ][ $variable_price_id ][ 'amount' ] = edd_sanitize_amount( $variable_price_data[ 'amount' ] );
                        }
                    }
                }
            }
            // error_log('Data --------->');
            // error_log(print_r($localize_downloads_data,1));           
            return $localize_downloads_data;
        }
    }
    // error_log('Class Called');
    GA4_Buy_Now_Click::get_instance();
}