<?php  
namespace Wisdmlabs;

if ( ! class_exists( '\Wisdmlabs\GA4_Select_Item' ) && class_exists( '\Wisdmlabs\GA4_ECommerce' ) ) {
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
                    plugins_url( 'assets/js/ga4_ecommerce_select_item.js', __FILE__),
                    array( 'jquery' ),
                    filemtime( plugins_url( 'assets/js/ga4_ecommerce_select_item.js', __FILE__) ),
                    true
                );
                wp_localize_script( 'wdm_ga4_ecommerce_select_item_js' , 'wdm_ga4_select_item' , $data_for_si );
            }
        }
        public $landing_pages_with_downloads = array(
            // The product landing page slug.
            'instructor-role-for-learndash' => array(
                // The download id set on the Buy Now button on the product landing page.
                '20277',
            ),
            'elumine' => array(
                '127679',
            ),
            'reports-for-learndash' => array(
                '707478',
            ),
            'group-registration-for-learndash' => array(
                '44670', 
            ),
            'learndash-ratings-reviews-feedback' => array(
                '109665',
            ),
            'course-content-cloner-for-learndash' => array(
                '34202',
            ),
            'leap' => array(
                '479075'
            ),
            'woocommerce-product-enquiry-pro' => array(
                '3212',
            ),
            'woocommerce-user-specific-pricing-extension' => array(
                '6963', 
            ),
            'assorted-bundles-woocommerce-custom-product-boxes-plugin' => array(
                '10055',
            ),
            'woocommerce-catalog-mode' => array(
                '399006',
            ),
            'woocommerce-sales-booster' => array(
                '475758',
            ),
        );

        public function return_all_products_data_for_si(){
            $all_data                   = $this->return_downloads_data();
            $localize_all_products_data_for_si = array();

            foreach ($this->landing_pages_with_downloads as $url => $value ){
                $download_id = $value[0];
                $download_data   = $all_data[ $download_id ];
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
                // foreach ($this->landing_pages_with_downloads as $url => $value ){
                //     if($value[0] === $download_id){
                //         $localize_all_products_data_for_si[ $url ] = $item_data;
                //     }
                // }
                $localize_all_products_data_for_si[ $url ] = $item_data;
            }

          
           
            return $localize_all_products_data_for_si;
        }
    }

    GA4_Select_Item::get_instance();
}