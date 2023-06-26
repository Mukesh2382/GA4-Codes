<?php
namespace Edwiser;

if ( ! class_exists( '\Edwiser\GA4_ECommerce_Remove_from_Cart' ) && class_exists( '\Edwiser\GA4_ECommerce' ) ) {
    class GA4_ECommerce_Remove_from_Cart extends GA4_ECommerce {
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
            add_action( 'wp_enqueue_scripts', [ $this, 'is_checkout_page' ],100000 );
        }
       
       function is_checkout_page(){
            if(is_page( 'checkout' )){
                $cart_data =  $this->localize_checkout_data();
                wp_localize_script( 'minimal-checkout-script', 'wdm_ga4_cart_data', $cart_data );
            }
       }
       function localize_checkout_data(){
            $cart_contents = edd_get_cart_content_details();
            return $cart_contents;
       }
    }
    
    GA4_ECommerce_Remove_from_Cart::get_instance();
}