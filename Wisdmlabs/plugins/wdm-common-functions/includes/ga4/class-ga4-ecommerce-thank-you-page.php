<?php
namespace Wisdmlabs;

if ( ! class_exists( '\Wisdmlabs\GA4_ECommerce_Thank_You_Page' ) && class_exists( '\Wisdmlabs\GA4_ECommerce' ) ) {
    class GA4_ECommerce_Thank_You_Page extends GA4_ECommerce {
        protected static $instance = null;

        protected $thank_you_page_slug_list = array(
            'purchase-confirmation-ldir',
            'purchase-confirmation-reports',
            'download-confirmation-reports',
            'purchase-confirmation-leap',
            'purchase-confirmation-cpb',
            'download-confirmation-wpcm',
            'purchase-confirmation-sbp',
            'purchase-confirmation-pep',
            'purchase-confirmation-csp',
            'purchase-confirmation-rrf',
            'purchase-confirmation-qre',
            'purchase-confirmation-ldgr',
            'purchase-confirmation-elumine',
            'download-confirmation',
            'purchase-confirmation',
            'confirmation',
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
                        plugins_url( 'assets/js/ga4_ecommerce_purchase_event.js', __FILE__),
                        array( 'jquery' ),
                        filemtime( plugins_url( 'assets/js/ga4_ecommerce_purchase_event.js', __FILE__) ),
                        true
                    );
                    wp_localize_script( 'wdm_ga4_ecommerce_purcahse_event_js', 'wdm_ga4_ecommerce_purchase_event_data', $localize_downloads_data );
                }
            }

            // Just for testing purpose
            // if ( is_page( 'purchase-confirmation-ldir' ) ) {
            //     /*
            //     array (
            //         'downloads' => 
            //         array (
            //           0 => 
            //           array (
            //             'id' => 20277,
            //             'options' => 
            //             array (
            //               'price_id' => '2',
            //               'recurring' => 
            //               array (
            //                 'period' => 'year',
            //                 'times' => 0,
            //                 'signup_fee' => 0,
            //                 'trial_period' => false,
            //               ),
            //             ),
            //             'quantity' => 1,
            //           ),
            //         ),
            //         'fees' => 
            //         array (
            //         ),
            //         'subtotal' => 80,
            //         'discount' => 80,
            //         'tax' => '0.00',
            //         'tax_rate' => 0,
            //         'price' => 0,
            //         'purchase_key' => '9e82413e1c16eac52a7c67feb2c1ff54',
            //         'user_email' => 'shaileshopen@gmail.comwdm',
            //         'date' => '2023-05-23 18:47:39',
            //         'user_info' => 
            //         array (
            //           'id' => 29585,
            //           'email' => 'shaileshopen@gmail.comwdm',
            //           'first_name' => 'Shailesh',
            //           'last_name' => '',
            //           'discount' => 'WisdmInternalPurchase',
            //           'address' => 
            //           array (
            //           ),
            //         ),
            //         'post_data' => 
            //         array (
            //           'log' => '',
            //           'pwd' => '',
            //           'redirect_to' => 'https://wisdmlabs.ga/checkout/',
            //           'instance' => '1',
            //           'action' => 'login',
            //           'wdm-security' => '480ebbeaa0',
            //           '_wp_http_referer' => '/checkout/',
            //           'edd_user_login' => '',
            //           'edd_email' => 'shaileshopen@gmail.comwdm',
            //           'edd_user_pass' => '',
            //           'edd_user_pass_confirm' => '',
            //           'edd_first' => 'Shailesh',
            //           'edd_last' => '',
            //           'edd_cust_country' => 'GA',
            //           'edd_cust_state' => '',
            //           'edd_agree_to_privacy_policy' => '1',
            //           'edd-user-id' => '29585',
            //           'edd_action' => 'purchase',
            //           'edd-gateway' => 'stripe',
            //           'edd-process-checkout-nonce' => '0ae0dea372',
            //         ),
            //         'cart_details' => 
            //         array (
            //           0 => 
            //           array (
            //             'name' => 'Instructor Role',
            //             'id' => 20277,
            //             'item_number' => 
            //             array (
            //               'id' => 20277,
            //               'options' => 
            //               array (
            //                 'price_id' => '2',
            //                 'recurring' => 
            //                 array (
            //                   'period' => 'year',
            //                   'times' => 0,
            //                   'signup_fee' => 0,
            //                   'trial_period' => false,
            //                 ),
            //               ),
            //               'quantity' => 1,
            //             ),
            //             'item_price' => 80,
            //             'quantity' => 1,
            //             'discount' => 80,
            //             'subtotal' => 80,
            //             'tax' => 0,
            //             'fees' => 
            //             array (
            //             ),
            //             'price' => 0,
            //           ),
            //         ),
            //         'gateway' => 'manual',
            //         'card_info' => 
            //         array (
            //           'card_name' => '',
            //           'card_cvc' => '',
            //           'card_exp_month' => '',
            //           'card_exp_year' => '',
            //           'card_address' => '',
            //           'card_address_2' => '',
            //           'card_city' => '',
            //           'card_state' => '',
            //           'card_country' => '',
            //           'card_zip' => '',
            //         ),
            //     )
            //     */
                
               
            // }
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
                    $items_data['item_brand']      = 'wisdmlabs';
                    $items_data[ 'item_category' ] = $downloads_data[ $download_id ][ 'item_category' ];
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
