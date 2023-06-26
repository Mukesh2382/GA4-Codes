<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WisdmEW_AjaxHandler {
    
	// To store current class object
    private static $instance;

    private function __construct(){
        add_action('wp_ajax_nopriv_subscribe_to_newsletter', [ $this , 'subscribe_to_newsletter']);
        add_action('wp_ajax_subscribe_to_newsletter', [$this , 'subscribe_to_newsletter'] );

        // Remove cart ajax
        add_action( 'wp_ajax_wdm_elem_cart_remove_item', array($this,'wdm_elem_cart_remove_item') );
        add_action( 'wp_ajax_nopriv_wdm_elem_cart_remove_item', array($this,'wdm_elem_cart_remove_item') );
    }

    public function subscribe_to_newsletter(){
        $email   = filter_input(INPUT_POST, 'email_id');
        $list_id = filter_input(INPUT_POST, 'sendy_list_id'); // ID of Sendy List
        $country = '';
    
        if (empty($email)|| $email == false) {
            ob_clean();
            echo json_encode([
                'status' => 'failed',
                'message' => 'Email id is empty'
            ]);
            exit;
        }

        /**
         * Check if the user is subscribed and the current status.
         */
        if ($list_id !== '') {
            $result = $this->wdmSubscribeUser( $email, $list_id, $country);
            ob_clean();
            echo json_encode($result);exit;
        }
        else{
            ob_clean();
            echo json_encode([
                'status' => 'failed',
                'message' => 'list id empty'
            ]);
            exit;
        }
    }

    public function wdmSubscribeUser($email, $list_id, $country){
        $return_result = [
            'status' => 'failed',
            'message' => 'Something went wrong',
            'output' => null,
        ];

        $sendy_url = esc_attr(get_option('sendy_url'));
        $sendy_api_key = esc_attr(get_option('sendy_api_key'));
        $url     = $sendy_url.'/api/subscribers/subscription-status.php';
        $data    = array(
            'api_key'    => $sendy_api_key,
            'email'      => $email,
            'list_id'    => $list_id,
        );
        $options     = array(
            'method'         => 'POST',
            'headers'        => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
            'body'           => $data,
            'timeout'        => 5,
            'httpversion'    => '1.0',
        );
        $result      = wp_remote_post($url, $options);
        $response    = wp_remote_retrieve_body($result);
       
        /**
         * Adding user if not exist to sendy
         */
        if ($response === 'Email does not exist in list') {
            $url     = $sendy_url.'/subscribe';
            $data    = array(
                'email'      => $email,
                'list'       => $list_id,
                'country'    => $country,
                'boolean'   => 'true',
                'gdpr'      => 'true',
            );

            $options = array(
                'method'         => 'POST',
                'headers'        => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
                'body'           => $data,
                'timeout'        => 5,
                'httpversion'    => '1.0',
            );
            $result  = wp_remote_post($url, $options);
            $error   = wp_remote_retrieve_body($result);

            $return_result['message'] = $result;
            if ($result == 'Some fields are missing.' || $result == 'Invalid email address.' || $result == 'Invalid list ID.') {
                $email_to      = get_option('admin_email');
                $subject = "Failed to add Landing Page Contact Form Entry to Sendy List";
                $message = "Dear Admin,<br>";
                $message .="<p>User $email was not added to the sendy list. Email of the user is $email. And the list ID is $list_id. The error message is  '$error' .</p>";
                $message .= "<p> Please add user to the list and take appropriate action. ";
                wp_mail($email_to, $subject, $message);
            }
            else{
                $return_result['status'] = "success";
            }
        } 
        elseif ($response == 'No data passed' || $response == 'API key not passed' || $response == 'Invalid API key' || $response    == 'Email not passed' || $response  == 'List ID not passed') {
            $email_to      = get_option('admin_email');
            $subject = "Failed to add Landing Page Contact Form Entry to Sendy List";
            $message = "Dear Admin,<br>";
            $message .="<p>User $email was not added to the sendy list. Email of the user is $email.And the list ID is $list_id. The error message is  '$response' .</p>";
            $message .= "<p> Please add user to the list and take appropriate action. ";
            wp_mail($email_to, $subject, $message);
            $return_result['message'] = $response;
        }
        return $return_result;
    }

    public function wdm_elem_cart_remove_item(){
        if(!empty($_POST['nonce']) && (wp_verify_nonce($_POST['nonce'], 'wdm-elem-cart') || wp_verify_nonce($_POST['nonce'], 'wdm-elem-related-product')) && isset($_POST['cart_key'])){
            $cart_items_count = count(edd_get_cart_contents());
            $ga4_data = apply_filters( 'wdm_ga4_remove_from_checkout_data', array(), $_POST['cart_key'] );
            $this->remove_item($_POST['cart_key']);
            $cart_items_count_after_remove = count(edd_get_cart_contents());
            $total = edd_get_cart_total();
            $subtotal = edd_get_cart_subtotal();
            // $coupon_value = filter_var( edd_cart_subtotal(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) - filter_var( edd_cart_total(0), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
            $coupon_value = edd_get_cart_subtotal() - EDD()->cart->get_total();
            $discount_id = $amount = $discount = '';
            $discounts = EDD()->cart->get_discounts();
		    foreach ( $discounts as $discount ) {
                $discount_id = edd_get_discount_id_by_code( $discount );
            }
            if($discount_id){
                $amount    = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );
            }
            if($cart_items_count_after_remove < $cart_items_count){
                $return = array(
                    'subtotal_plain'        => $subtotal,
                    'subtotal'              => html_entity_decode( edd_currency_filter( edd_format_amount( $subtotal ) ), ENT_COMPAT, 'UTF-8' ),
                    'total_plain'           => $total,
                    'total'                 => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
                    'discounts'             => edd_get_cart_discounts(),
                    'msg'                   => 'valid',
                    'coupon_value'          => $coupon_value?html_entity_decode( edd_currency_symbol(edd_get_currency()) . number_format($coupon_value,2), ENT_COMPAT, 'UTF-8' ):'',
                    'amount'                => $amount,
                    'ga4_data'              => $ga4_data,
                );
                echo json_encode($return);
                die();
            }
        }
        echo 0;
        die();
    }
    
    public function remove_item($key){
        return edd_remove_from_cart($key);
    }


    public function valid_sendy_list($list_id){
        $sendy_url = esc_attr(get_option('sendy_url'));
        $sendy_api_key = esc_attr(get_option('sendy_api_key'));
        $url     = $sendy_url.'/api/subscribers/active-subscriber-count.php';
        $data    = array(
            'api_key'    => $sendy_api_key,
            'list_id'    => $list_id,
        );

        $options     = array(
            'method'         => 'POST',
            'headers'        => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
            'body'           => $data,
            'timeout'        => 5,
            'httpversion'    => '1.0',
        );
        $result      = wp_remote_post($url, $options);
        $response    = wp_remote_retrieve_body($result);
        $invalid_responses = [
            'No data passed',
            'API key not passed',
            'Invalid API key',
            'List ID not passed',
            'List does not exist',
        ];
        $success = isset($response) && !in_array($response,$invalid_responses);
        if($success ){
            return true;
        }
        else{
            if($response == 'List does not exist'){
                return false;
            }
        }
    }

    // To get object of the current class
    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new WisdmEW_AjaxHandler;
        }
        return self::$instance;
    }
}

WisdmEW_AjaxHandler::getInstance();