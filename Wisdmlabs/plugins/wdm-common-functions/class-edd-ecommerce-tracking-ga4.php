<?php

/**
 * Google Analytics Enhanced Ecommerce
 * Class Edd Ecommerce Tracking GA4.
 * 
 * @class		EDD_Ecommerce_Tracking_GA4
 * @package		EDD Enhanced eCommerce Tracking Extension
 * @author		Swapnil Mahadik
 */

namespace WDMCommonFunctions;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EDD_Ecommerce_Tracking_GA4 {

    public static $instance;

    public function __construct(){
        add_action('init' , [$this , 'init']);
    } 

    function init(){
        if(!$this->valid_request()){
            return false;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->load_actions();
        $this->ga4_code = edd_get_option( 'eddeet_ga4_code' );
    }

    function valid_request(){
        $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";  
        $current_page = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];  
        $current_page = strtolower($current_page);
        $referer = isset($_SERVER['HTTP_REFERER']) ? strtolower($_SERVER['HTTP_REFERER']) : false;

        $valid_pages = [
            "checkout", // checkout page
            "purchase", // purchase confirmation pages
        ];
        // Check if current page or referer page is valid 
        foreach($valid_pages as $page){
            if (strpos($current_page, $page) !== false) {
                return true;
            }
            if($referer){
                if (strpos($referer, $page) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    function load_actions(){
        add_action( 'eddeet_settings',array($this,"add_ga4_code_section") ,99,1 );

        //add_action( 'wp_footer',array($this,"fire_ga4_events") ,99);
        
        add_filter( 'eddeet_trigger_impression_args', [ $this, 'trigger_view_item_item_impression' ], 99, 1 );
        add_filter( 'eddeet_trigger_add_to_cart_args', [ $this, 'trigger_add_to_cart_interaction' ], 99, 1 );
        add_filter( 'eddeet_trigger_remove_from_cart_args', [ $this, 'trigger_remove_from_cart_interaction' ], 99, 1 );
        add_filter( 'eddeet_trigger_update_quantity_args', [ $this, 'trigger_update_cart' ], 99, 1 );
        add_filter( 'eddeet_trigger_checkout_cart_args', [ $this, 'trigger_begin_checkout' ], 99, 1 );
        
        // add_action( 'edd_payment_receipt_before', [ $this, 'trigger_payment_complete_event' ], 99 ,1);

        add_shortcode( 'trigger_ga4_for_purchase', [ $this, 'trigger_payment_complete_event_shortcode' ] );

        add_action('show_better_pricing_table', [ $this, 'trigger_view_on_pricing_table' ], 99 ,1);
    }

    function trigger_view_on_pricing_table($pricing_table_plan){
        // y('asd');
        // x($pricing_table_plan);

    }
    public function prep_transaction_items( $payment_meta, $coupon=null ) {
    		$items        = array();
		if ( $payment_meta['cart_details'] ) :
			foreach ( $payment_meta['cart_details'] as $key => $item ) :
				$download       = new \EDD_Download( $item['id'] );
				$price_options  = $download->get_prices();
				$price_id       = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
				$variation      = ! is_null( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['name'] : '';
				$category       = '';
				$author         = get_the_author_meta( 'display_name', $download->post_author );
                $qty = is_array($item['quantity']) ? $item['quantity'][0] : $item['quantity'];
				$itemdetails = [];
                $itemdetails["item_id"]  = $item['id'];			// ID
				$itemdetails["item_name"]  = $item['name'];		// Name
				$itemdetails["item_category"]  = $category;		// Category
				$itemdetails["price"]  = $item['item_price'];	// Price
                $itemdetails["discount"]  = $item['discount'];	// Price
                $itemdetails["coupon"]  = ($item['discount']) > 0 ? $coupon : '';	// Price
				$itemdetails["quantity"]  = $qty;	// Quantity
				$itemdetails["item_variant"]  = $variation;			// Variation
				$itemdetails["currency"] = "USD";				// Author name
                $items[] = $itemdetails;
			endforeach;
		endif;
		return $items;
	}

    public function trigger_payment_complete_event_shortcode($atts, $content = null){
        global $edd_receipt_args;

        $edd_receipt_args = shortcode_atts(
            array(
                'error'          => __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads'),
                'price'          => true,
                'discount'       => true,
                'products'       => true,
                'date'           => true,
                'notes'          => true,
                'payment_key'    => false,
                'payment_method' => true,
                'payment_id'     => true,
            ),
            $atts,
            'edd_receipt'
        );

        if(empty($edd_receipt_args['id'])){
            $session = edd_get_purchase_session();
            if (isset($_GET['payment_key'])) {
                $payment_key = urldecode($_GET['payment_key']);
            } elseif ($session) {
                $payment_key = $session['purchase_key'];
            } elseif ($edd_receipt_args['payment_key']) {
                $payment_key = $edd_receipt_args['payment_key'];
            }
    
            // No key found
            if (! isset($payment_key)) {
                return '';
            }
            $edd_receipt_args['id'] = edd_get_purchase_id_by_key($payment_key);
        }

        $user_can_view = edd_can_view_receipt($payment_key);

        if ( $user_can_view && ! empty($payment_key) && !edd_is_guest_payment($edd_receipt_args['id']) ) {
            $payment = new \EDD_Payment( $edd_receipt_args['id'] );
            if(!empty($payment)){
                $this->trigger_purchase_event_from_payment($payment);
            }            
            return '';
        }
        return '';
    }

    function trigger_payment_complete_event($paydata){
        $payment = new \EDD_Payment( $paydata->ID );
        if(!empty($payment)){
            $this->trigger_purchase_event_from_payment($payment);
        }
    }

    function trigger_purchase_event_from_payment($payment=null){
        if(!empty($payment->ID)){
            $payment_id     = $payment->ID;
            $payment_meta = edd_get_payment_meta( $payment_id );
            $coupon     = $payment_meta['user_info']['discount'];
            $coupon     = $coupon != 'none' ? explode( ',', $coupon ) : '';
            $coupon     = is_array( $coupon ) ? reset( $coupon ) : $coupon;
            $items        = $this->prep_transaction_items( $payment_meta , $coupon);
            $tag_type = "event";
            $event_name = "purchase";
            $data = array (
                'coupon' => $coupon,
                'currency' => 'USD',
                "items" => $items,
                'transaction_id' => @$payment->ID,
                'shipping' => 0,
                'value' => edd_get_payment_amount( $payment_id ),
                'tax' => edd_use_taxes() ? edd_get_payment_tax( $payment_id ) : 0
            );
            $this->add_event($tag_type,$event_name,$data);
        }
    }

    function add_event($type,$value,$data){
        $edd_ga4_ecmmm_events = [];
        if(!empty($_SESSION['edd_ga4_ecmmm_events'])){
            $edd_ga4_ecmmm_events = json_decode(stripslashes($_SESSION['edd_ga4_ecmmm_events']),true);
        }
        $edd_ga4_ecmmm_events[] = [ $type, $value, $data];
        $_SESSION['edd_ga4_ecmmm_events'] = json_encode($edd_ga4_ecmmm_events);
    }

    function fetch_events(){
        if(isset($_SESSION['edd_ga4_ecmmm_events'])){
            return json_decode(stripslashes($_SESSION['edd_ga4_ecmmm_events']),true);
        }
        return [];
    }

    function add_ga4_code_section($settings){
        // x($_SESSION);
        $settings[] = array(
            'id'   => 'eddeet_ga4_code',
            'name' => __( 'GA4 Code', 'edd-enhanced-ecommerce-tracking' ),
            'desc' => __( 'Add the GA4 code copied from your Google Analytics settings page', 'edd-enhanced-ecommerce-tracking' ),
            'type' => 'text',
        );
        return $settings;
    }
    
    function fire_ga4_events(){
        if(!isset($_SESSION['edd_ga4_ecmmm_events'])){
            return;
        }
        ?>  
        <script type="module">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments); console.log('arguments',arguments);}
            gtag('config', '<?php echo $this->ga4_code;?>');
            <?php
                $edd_ga4_ecmmm_events = $this->fetch_events();
                if(!empty($edd_ga4_ecmmm_events)){
                    foreach ($edd_ga4_ecmmm_events as $key => $value) {
                        ?>
                        gtag('<?php echo $value[0]; ?>', '<?php echo $value[1]; ?>' , <?php echo json_encode($value[2]); ?>)
                        <?php
                    }
                }
                unset($_SESSION['edd_ga4_ecmmm_events']);
            ?>
        </script>
        <?php
    }
 
    function get_impresssion_data($eventdata){
        $prodno = 1;
        $products = [];
        while(isset($eventdata["il{$prodno}pi1id"])){
            $products[] = array(
                "item_id" => $eventdata["il{$prodno}pi1id"],
                "item_name" => $eventdata["il{$prodno}pi1nm"],
                "index" => $prodno,
                "item_category" => $eventdata["il{$prodno}pi1ca"],
            );
            $prodno++;
        }
        return $products;
    }

    function get_item_data($eventdata){
        $prodno = 1;
        $products = [];
        while(isset($eventdata["pr{$prodno}id"])){
            $qty = is_array($eventdata["pr{$prodno}qt"]) ? $eventdata["pr{$prodno}qt"][0] : $eventdata["pr{$prodno}qt"];
            $products[] = array(
                "item_id" => $eventdata["pr{$prodno}id"],
                "item_name" => $eventdata["pr{$prodno}nm"],
                "index" => $prodno,
                "item_category" => $eventdata["pr{$prodno}ca"],
                "item_variant" => $eventdata["pr{$prodno}va"],
                "price" => $eventdata["pr{$prodno}pr"],
                "currency" => "USD",
                "quantity" =>   $qty
            );
            $prodno++;
        }
        return $products;
    }
    function trigger_view_item_item_impression($eventdata){
        $tag_type = "event";
        $event_name = "view_item";
        $data = array(
            "currency" => "USD",
            "items" => $this->get_impresssion_data($eventdata)
        );
        $this->add_event($tag_type,$event_name,$data);
        return $eventdata;
    }

    function get_cart_total($items){
        $amount = 0;
        foreach ($items as $value) {
            $amount += ($value['price'] * $value['quantity']);
        }
        return $amount;
    }

    function trigger_add_to_cart_interaction($eventdata){
        $tag_type = "event";
        $event_name = "add_to_cart";
        $items = $this->get_item_data($eventdata);
        $amount = $this->get_cart_total($items);
        $data = array(
            "currency" => "USD",
            "items" => $items,
            "value" => $amount
        );
        $this->add_event($tag_type,$event_name,$data);
        return $eventdata;
    }

    function trigger_remove_from_cart_interaction($eventdata){
        $tag_type = "event";
        $event_name = "remove_from_cart";
        $items = $this->get_item_data($eventdata);
        $amount = $this->get_cart_total($items);
        $data = array(
            "currency" => "USD",
            "items" => $items,
            "value" => $amount
        );
        $this->add_event($tag_type,$event_name,$data);
        return $eventdata;
    }

    function trigger_update_cart($eventdata){
        if($eventdata['ea'] == 'add'){
            $this->trigger_add_to_cart_interaction($eventdata);
        } 
        if($eventdata['ea'] == 'remove'){
            $this->trigger_remove_from_cart_interaction($eventdata);
        } 
        return $eventdata;
    }

    function trigger_begin_checkout($eventdata){
        $tag_type = "event";
        $event_name = "begin_checkout";
        $items = $this->get_item_data($eventdata);
        $amount = $this->get_cart_total($items);
        $data = array(
            "currency" => "USD",
            "items" => $items,
            "value" => $amount
        );
        $this->add_event($tag_type,$event_name,$data);
        return $eventdata;
    }

    function trigger_purchase(){
        $tag_type = "event";
        $event_name = "purchase";
        $items = $this->get_item_data($eventdata);
        $amount = $this->get_cart_total($items);
        $data = array (
            'coupon' => @$eventdata['tcc'],
            'currency' => 'USD',
            "items" => $items,
            'transaction_id' => @$eventdata['ti'],
            'shipping' => @$eventdata['ts'],
            'value' => @$eventdata['tr'],
            'tax' => @$eventdata['tt']
        );
        $this->add_event($tag_type,$event_name,$data);
        return $eventdata;
    }

    function trigger_add_payment_info($eventdata){
        $tag_type = "event";
        $event_name = "add_payment_info";
        $items = $this->get_item_data($eventdata);
        $amount = $this->get_cart_total($items);
        $data = array(
            'coupon' => @$eventdata['tcc'],
            "currency" => "USD",
            "items" => $items,
            "payment_type" => 'Credit Card',
            'value' => @$eventdata['tr'],
            'shipping' => @$eventdata['ts'],
            'tax' => @$eventdata['tt']
        );
        $this->add_event($tag_type,$event_name,$data);
        return $eventdata;
    }

    function trigger_add_shipping_info($eventdata){
        $tag_type = "event";
        $event_name = "add_shipping_info";
        $items = $this->get_item_data($eventdata);
        $amount = $this->get_cart_total($items);
        $data = array(
            'coupon' => @$eventdata['tcc'],
            "currency" => "USD",
            "items" => $items,
            "payment_type" => 'Credit Card',
            "shipping_tier" => 'Ground',
            'value' => @$eventdata['tr'],
            'shipping' => @$eventdata['ts'],
            'tax' => @$eventdata['tt']
        );
        $this->add_event($tag_type,$event_name,$data);
        return $eventdata;
    }

    function trigger_full_refund($eventdata){
        $tag_type = "event";
        $event_name = "refund";
        $items = $this->get_item_data($eventdata);
        $amount = $this->get_cart_total($items);
        $data = array(
            'coupon' => @$eventdata['tcc'],
            "currency" => "USD",
            "items" => $items,
            "payment_type" => 'Credit Card',
            "shipping_tier" => 'Ground',
            'value' => @$eventdata['tr'],
            'shipping' => @$eventdata['ts'],
            'tax' => @$eventdata['tt']
        );
        $this->add_event($tag_type,$event_name,$data);
        return $eventdata;
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new EDD_Ecommerce_Tracking_GA4;
        }
        return self::$instance;
    }
}
