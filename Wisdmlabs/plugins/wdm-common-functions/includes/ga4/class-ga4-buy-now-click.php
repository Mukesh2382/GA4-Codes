<?php
namespace Wisdmlabs;

if ( ! class_exists( '\Wisdmlabs\GA4_Buy_Now_Click' ) && class_exists( '\Wisdmlabs\GA4_ECommerce' ) ) {
    class GA4_Buy_Now_Click extends GA4_ECommerce  {
        protected static $instance = null;

        // protected $landing_pages_with_downloads = array(
        //     // The product landing page slug.
        //     'instructor-role-for-learndash' => array(
        //         // The download id set on the Buy Now button on the product landing page.
        //         '20277' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '2', '4' ),
        //         ),
        //         '366223' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //         '366236' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //     ),
        //     'elumine' => array(
        //         '127679' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '9' ),
        //         ),
        //         '366218' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //         '366221' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //     ),
        //     'reports-for-learndash' => array(
        //         '707478' => array( 
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array( '7', '3', '5', '2', '4', '6' ),
        //         ),
        //     ),
        //     'group-registration-for-learndash' => array(
        //         '44670' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '9' ),
        //         ),
        //         '366225' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //         '368742' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //     ),
        //     'learndash-ratings-reviews-feedback' => array(
        //         '109665' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '8' ),
        //         ),
        //         '366227' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //         '368744' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //     ),
        //     'course-content-cloner-for-learndash' => array(
        //         '34202' => array(
        //             'item_category' => 'LearnDash',
        //             'is_free'       => true,
        //             'price_ids'     => array ( '2', '4' ),
        //         ),
        //         '368743' => array(
        //             'item_category' => 'LearnDash',
        //             'price_ids'     => array ( '1', '2' ),
        //         ),
        //     ),
        //     'woocommerce-product-enquiry-pro' => array(
        //         '3212' => array(
        //             'item_category' => 'WooCommerce',
        //             'price_ids'     => array ( '2', '4' ),
        //         ),
        //         '878908' => array(
        //             'item_category' => 'WooCommerce',
        //             'price_ids'     => array ( '2', '0' ),
        //         ),
        //         '878897' => array(
        //             'item_category' => 'WooCommerce',
        //             'price_ids'     => array ( '1', '0' ),
        //         ),
        //     ),
        //     'woocommerce-user-specific-pricing-extension' => array(
        //         '6963' => array(
        //             'item_category' => 'WooCommerce',
        //             'price_ids'     => array ( '2', '4' ),
        //         ),
        //         '878912' => array(
        //             'item_category' => 'WooCommerce',
        //             'price_ids'     => array ( '1', '4' ),
        //         ),
        //         '878897' => array(
        //             'item_category' => 'WooCommerce',
        //             'price_ids'     => array ( '1', '4' ),
        //         ),
        //     ),
        //     'assorted-bundles-woocommerce-custom-product-boxes-plugin' => array(
        //         '10055' => array(
        //             'item_category' => 'WooCommerce',
        //             'price_ids'     => array ( '2', '5', '7', '4', '6', '8' ),
        //         ),
        //     ),
        //     'woocommerce-catalog-mode' => array(
        //         '399006' => array(
        //             'item_category' => 'WooCommerce',
        //             'is_free'       => true,
        //             'price_ids'     => array ( '2' ),
        //         ),
        //     ),
        // );

        protected $landing_pages_with_downloads = array(
            // The product landing page slug.
            'instructor-role-for-learndash' => array(
                // The download id set on the Buy Now button on the product landing page.
                '20277', '366223', '366236',
            ),
            'elumine' => array(
                '127679', '366218', '366221',
            ),
            'reports-for-learndash' => array(
                '707478',
            ),
            'group-registration-for-learndash' => array(
                '44670', '366225', '368742',
            ),
            'learndash-ratings-reviews-feedback' => array(
                '109665', '366227', '368744',
            ),
            'course-content-cloner-for-learndash' => array(
                '34202', '368743',
            ),
            'leap' => array(
                '479075'
            ),
            'woocommerce-product-enquiry-pro' => array(
                '3212', '878908', '878897',
            ),
            'woocommerce-user-specific-pricing-extension' => array(
                '6963', '878912', '878897',
            ),
            'assorted-bundles-woocommerce-custom-product-boxes-plugin' => array(
                '10055',
            ),
            'woocommerce-catalog-mode' => array(
                '399006',
            ),
            'woocommerce-sales-booster' => array(
                '475758', '878897',
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
        }

        /**
         * This function checks if the current page is product landing page.
         */
        public function is_product_landing_page() {
            foreach ( $this->landing_pages_with_downloads as $url => $value ) {
                if ( is_page( $url ) ) {
                    return $value;
                }
            }

            return false;
        }
        // public function is_product(){
        //     foreach ( $this->landing_pages_with_downloads as $url => $value){
        //         return $value;
        //     }
        // }

        public function enqueue_scripts() {
            $localize_downloads_data = $this->return_localize_downloads_data();
            if ( ! empty( $localize_downloads_data )) {
                // wp_localize_script( 'theme_custom_scripts', 'wdm_ga4_downloads_data', $localize_downloads_data );
                // wp_add_inline_script( 'theme_custom_scripts', $this->return_event_js_code() );
                // wp_localize_script( 'theme_scripts', 'wdm_ga4_downloads_data', $localize_downloads_data );
                // wp_add_inline_script( 'theme_scripts', $this->return_event_js_code() );
                // error_log('shvsh');
                // error_log( plugins_url( 'includes/ga4/test.js', WDM_COMMON_FUNCTIONS_FILE ) );
                wp_enqueue_script(
                    'wdm_ga4_ecommerce_buy_now_click_js',
                    plugins_url( '/assets/js/ga4_ecommerce_buy_now_click.js', __FILE__),
                    array( 'jquery' ),
                    filemtime( plugins_url( '/assets/js/ga4_ecommerce_buy_now_click.js', __FILE__) ),
                    true
                );
                wp_localize_script( 'wdm_ga4_ecommerce_buy_now_click_js', 'wdm_ga4_downloads_data', $localize_downloads_data );
            }
        }

        /**
         * Return the downloads data for localization.
         */
        public function return_localize_downloads_data() {
            $landing_page_download_ids = $this->is_product_landing_page();
            $localize_downloads_data   = array();
         
            if ( ! empty( $landing_page_download_ids ) ) {
                $all_downloads_data = $this->return_downloads_data();
                foreach ( $landing_page_download_ids as $download_id ) {
                    $download_data   = $all_downloads_data[ $download_id ];
                    $download_object = edd_get_download( $download_id );
                    $variable_prices = $download_object->get_prices();

                    $localize_downloads_data[ $download_id ][ 'item_id' ] = strval($download_id);
                    $localize_downloads_data[ $download_id ][ 'item_name' ] = $download_object->post_title;
                    $localize_downloads_data[ $download_id ][ 'item_category' ] = $download_data[ 'item_category' ];

                    if ( ! empty( $download_data[ 'is_free' ] ) ) {
                        $localize_downloads_data[ $download_id ][ 'single_price_amount' ] = 0;
                    } else {
                        foreach ( $variable_prices as $variable_price_id => $variable_price_data ) {
                            $localize_downloads_data[ $download_id ][ 'variable_prices' ][ $variable_price_id ][ 'amount' ] = edd_sanitize_amount( $variable_price_data[ 'amount' ] );
                        }
                    }
                }
            }
            
            return $localize_downloads_data;
        }

        
            
        

        /**
         * Return the JS code for GA4.
         */
        public function return_event_js_code() {
            $js_code = '
                document.addEventListener( "DOMContentLoaded", function() {
                    // Code to be executed when the DOM is ready
                    let wdm_is_ga4_captured  = false;
                    let buy_now_forms        = document.querySelectorAll(".wdm-cta-buy-now-button");
                    let buy_now_link_buttons = document.querySelectorAll(".wdm-buy-now-link-button a");

                    buy_now_forms.forEach( function( buy_now_form ) {
                        buy_now_form.addEventListener( "submit", function( e ){
                            if ( true === wdm_is_ga4_captured ) {
                                return;
                            }

                            e.preventDefault();
        
                            let download_id = this.querySelector(\'input[name="download_id"]\').value;
                            let price_id = this.querySelector(\'input[name="edd_options[price_id][]"]\').value;

                            wdm_ga4_datalayer_push_buy_now_event( download_id, price_id );
                            wdm_is_ga4_captured = true;
                            this.submit();
                        });
                    });

                    buy_now_link_buttons.forEach( function( buy_now_link_button ) {
                        buy_now_link_button.addEventListener("click", function( e ){
                            if ( true === wdm_is_ga4_captured ) {
                                return;
                            }

                            e.preventDefault();

                            let urlString   = this.href;
                            let paramString = decodeURI( urlString.split("?")[1] );
                            let params_arr  = paramString.split("&");
                            let download_id = "";
                            let price_id    = "";

                            for (let i = 0; i < params_arr.length; i++) {
                                let pair = params_arr[i].split("=");
                                if ( "download_id" == pair[0] ) {
                                    download_id = pair[1];
                                }
                                if ( "edd_options[price_id]" == pair[0] ) {
                                    price_id = pair[1];
                                }
                            }

                            wdm_ga4_datalayer_push_buy_now_event( download_id, price_id );
                            wdm_is_ga4_captured = true;
                            this.click();
                        });
                    });

                    function wdm_ga4_datalayer_push_buy_now_event( download_id, price_id ) {
                        try {
                            // if ( undefined == wdm_ga4_downloads_data[ download_id ] || undefined == wdm_ga4_downloads_data[ download_id ][ "variable_prices" ][ price_id ] ) {
                            //     console.log( "The specified downloads has not been configured for GA4." );

                            //     return true;
                            // }

                            dataLayer.push({ ecommerce: null }); // Clear the previous ecommerce object.
                            dataLayer.push({
                                event: "begin_checkout",
                                ecommerce: {
                                    currency: "USD",
                                    items: [
                                    {
                                        item_id: download_id,
                                        item_name: wdm_ga4_downloads_data[ download_id ][ "item_name" ],
                                        discount: 0.00,
                                        index: 0,
                                        item_brand: "WisdmLabs",
                                        item_category: wdm_ga4_downloads_data[ download_id ][ "item_category" ],
                                        item_list_id: "123456",
                                        item_list_name: "All-Wisdm-Products",
                                        price: wdm_ga4_downloads_data[ download_id ][ "variable_prices" ][ price_id ][ "amount" ],
                                        quantity: 1
                                    }
                                    ]
                                }
                            });
                            return 1;
                        } catch( error ) {
                            console.log( "The download has not been configured for GA4." );
                            return -1;
                        }
                    }
                });
            ';

            return $js_code;
        }
    }

    GA4_Buy_Now_Click::get_instance();
}
