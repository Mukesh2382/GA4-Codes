<?php  
namespace Edwiser;

if ( ! class_exists( '\Edwiser\GA4_Select_Item' ) && class_exists( '\Edwiser\GA4_ECommerce' ) ) {
    class GA4_Select_Item extends GA4_ECommerce  {
        protected static $instance = null;


        /**
         * Return single instance of the class.
         *
         * @return GA4_Select_Item Return the class instance.
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
        

        public function enqueue_scripts(){
            $data_for_si = $this->return_all_products_data_for_si();
            if(! empty($data_for_si)){
                wp_enqueue_script(
                    'wdm_ga4_ecommerce_select_item_js',
                    plugin_dir_url(__FILE__)  . 'assets/ga4_ecommerce_select_item.js',
                    array( 'jquery' ),
                    filemtime( plugin_dir_url(__FILE__)  . 'assets/ga4_ecommerce_select_item.js' ),
                    true
                );
                wp_localize_script( 'wdm_ga4_ecommerce_select_item_js' , 'wdm_ga4_select_item' , $data_for_si );
            }
        }
        public $landing_pages_with_downloads = array(
            // The product landing page slug.
            'remui' => array(
                '95719',
            ),
            'bridge' => array(
                '76021',
            ),
            'reports' => array(
                '275424',
            ),
            'all-access-pass' => array(
                '251876',
            ),
            'rapidgrader' => array(
                '88840' ,
            ),
            'forms' => array(
               '59808' ,
            ),
            'site-monitor' => array(
                '74539'
            ),
            'course-formats' => array(
                '80379'
            ),
        );

        public function return_all_products_data_for_si(){
            if(is_page('products')){
                $all_data                   = $this->return_downloads_data();
            $localize_all_products_data_for_si = array();

            foreach ($this->landing_pages_with_downloads as $url => $value ){
                $download_id = $value[0];
                $download_data   = $all_data[ $download_id ];
                $download_object = edd_get_download( $download_id );

                $item_data[ 'item_id' ] = strval($download_id);
                $item_data[ 'item_name' ] = $download_object->post_title;
                
                $item_data[ 'item_list_id' ]   = 'All-Edwiser-Products';
                $item_data[ 'item_list_name' ] = 'All-Edwiser-Products';
                $item_data[ 'price' ] = $download_object->get_price();
            
                $localize_all_products_data_for_si[ $url ] = $item_data;
            }           
            return $localize_all_products_data_for_si;
            }
        }
    }
    GA4_Select_Item::get_instance();
}