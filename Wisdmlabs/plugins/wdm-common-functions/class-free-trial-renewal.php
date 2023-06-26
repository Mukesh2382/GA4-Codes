<?php
namespace WDMCommonFunctions {

    /**
    * Class to handle ___
    */
    class WdmFreeTrialRenewal
    {
        /**
         * Instance of this class.
         *
         * @since    1.0.0
         *
         * @lvar object
         */
        protected static $instance = null;

        public function __construct()
        {
            add_action( 'edd_subscription_post_renew', array( $this, 'edd_subscription_post_renew' ), 10, 4 );
            add_action( 'edd_payment_advanced_filters_row', array( $this, 'edd_payment_advanced_filters_row' ) );
            add_action( 'wp_ajax_wdm_export_free_trial_renewal', array( $this, 'wdm_export_free_trial_renewal' ) );
            
            if (isset($_GET['page']) && $_GET['page']=='edd-payment-history') {
                add_action('admin_enqueue_scripts', array($this,'admin_enqueue_scripts'));
            }

        }

        /**
         * 
        * After renewal add a meta to identify free trial renewal.
        */
        public function edd_subscription_post_renew($sub_id, $expiration, $sub, $payment_id)
        {
            $payment      = new \EDD_Payment( $payment_id );
            $payment_exists = $payment->ID;
            if ( empty( $payment_exists ) ) {
                return;
            }
            $cart_items     = $payment->cart_details;
            unset($payment);
            if ( empty( $cart_items ) ) {
                return;
            }
            foreach ( $cart_items as $key => $cart_item ) {
                $item_id    = isset( $cart_item['id']    )                                  ? $cart_item['id']                                 : $cart_item;
                $price_id   = isset( $cart_item['item_number']['options']['price_id'] )     ? $cart_item['item_number']['options']['price_id'] : null;
                $download   = new \EDD_Download( $item_id );
                $initial_payment = wp_get_post_parent_id( $payment_id );
                if ( isset( $price_id ) && edd_recurring()->has_free_trial( $item_id, $price_id ) && $initial_payment ) {
                    update_post_meta( $payment_id,'free_trial_renewal',$initial_payment );
                    update_post_meta( $payment_id,'free_trial_renewal_download',$item_id );
                }
            }
            return;
        }

        /**
         * edd_payment_advanced_filters_row adds export buttons before search field
         *
         * @return void
         */
        public function edd_payment_advanced_filters_row(){
            echo '<p><button class="button wdm_exp_tr_ren">Export Trial Renewals</button> <button class="button wdm_exp_upgr">Export Upgrades</button></p>';
            echo '<p><b>Note:</b> You can select Start Date, End Date and Gateway to apply filters on data.</p>';
        }
        
        /**
         * wdm_export_free_trial_renewal Ajax handler to export free trial renewal data
         *
         * @return void
         */
        public function wdm_export_free_trial_renewal(){
            // [action] => wdm_export_free_trial_renewal
            // [sdate] => 04/01/2021
            // [edate] => 04/30/2021
            // [gateway] => stripe
            $args = array( 
                'post_type'=>'edd_payment',
                'post_status'=>'edd_subscription',
                'post_parent__not_in'=> array(0),
                'posts_per_page' => -1
            );
            if(!empty($_POST['sdate']) && !empty($_POST['edate'])){
                $sdate = $this->wp_strtotime($_POST['sdate']. ' 00:00:00');
                $edate = $this->wp_strtotime($_POST['edate'].' 23:59:59');

                $args['date_query'] = array(
                    array(
                        'after'     => date( 'c' , $sdate ),
                        'before'    => date( 'c' , $edate ),
                        'inclusive' => true,
                    ),
                );
            }
            $args['meta_query'][0]['key'] = 'free_trial_renewal';
            $args['meta_query'][0]['compare'] = 'EXISTS';
            if(!empty($_POST['gateway']) && 'all'!==$_POST['gateway']){
                $args['meta_query'][1]['key'] = '_edd_payment_gateway';
                $args['meta_query'][1]['value'] = array($_POST['gateway']);
                $args['meta_query'][1]['compare'] = 'IN';
                $args['meta_query']['relation'] = 'AND';
            }
            $wp_query_res = new \WP_Query( $args );
         
            $data = array();
            $data[0] = array('Payment ID','Customer','Download','Date d/m/y');
            if ( $wp_query_res->have_posts() ) :
                foreach ( $wp_query_res->posts as $post ):
                    $payment      = new \EDD_Payment( $post->ID );

                    // Sanity check... fail if purchase ID is invalid
                    $payment_exists = $payment->ID;
                    if ( empty( $payment_exists ) ) {
                        continue;
                    }
                    $customer = new \EDD_Customer( $payment->customer_id );
                    $download = get_post_meta($post->ID,'free_trial_renewal_download',true);
                    $data[] = array($post->ID, $customer->name . ' - ' . $customer->email, $download, date( 'd/m/y g:i A', strtotime($post->post_date)));
                endforeach;
            endif;
            if($data){
                $this->convert_to_csv($data, ',');
            }
            die;
        }
                
        /**
         * convert_to_csv converts array into csv
         *
         * @param  mixed $input_array
         * @param  mixed $delimiter
         * @return void
         */
        public function convert_to_csv($input_array, $delimiter)
        {
            if ( !empty( $input_array ) ):

                $fp = fopen( 'php://output', 'w' );
                
                foreach ($input_array as $line) {
                    /** default php csv handler **/
                    fputcsv($fp, $line, $delimiter);
                }

                fclose( $fp );
            endif;

            exit();
        }

        /**
         * admin_enqueue_scripts includes js required for ajax handling
         *
         * @return void
         */
        public function admin_enqueue_scripts(){
            wp_enqueue_script('edd-export-report', plugins_url('assets/js/edd-export-report.js', __FILE__), array('jquery'), '1.0.0');
            wp_localize_script('edd-export-report', 'wdm_ajax_object', array( 'ajax_url' => admin_url('admin-ajax.php') ));
        }
        
        /**
         * wp_strtotime To get str to time according to WP Site's set timezone
         *
         * @param  mixed $str
         * @return string
         */
        public function wp_strtotime($str) {
            
            $tz_string = get_option('timezone_string');
            $tz_offset = get_option('gmt_offset', 0);

            if (!empty($tz_string)) {
                // If site timezone option string exists, use it
                $timezone = $tz_string;

            } elseif ($tz_offset == 0) {
                // get UTC offset, if it isn’t set then return UTC
                $timezone = 'UTC';

            } else {
                $timezone = $tz_offset;
                
                if(substr($tz_offset, 0, 1) != "-" && substr($tz_offset, 0, 1) != "+" && substr($tz_offset, 0, 1) != "U") {
                    $timezone = "+" . $tz_offset;
                }
            }

            $datetime = new \DateTime($str, new \DateTimeZone($timezone));
            return $datetime->format('U');
        }

        /**
         * Returns an instance of this class.
         *
         * @since     1.0.0
         *
         * @return object A single instance of this class.
         */
        public static function getInstance()
        {
            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

    }
    WdmFreeTrialRenewal::getInstance();
}
