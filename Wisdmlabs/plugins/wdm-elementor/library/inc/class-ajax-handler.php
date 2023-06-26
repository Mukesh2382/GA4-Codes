<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * WdmElementorAjaxHandler to handle ajax calls
 */
class WdmElementorAjaxHandler {
	
	// To store current class object
    private static $instance;

    public $related_cart_nonce;

    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        // Ajax calls handling
        // Chage license ajax
		add_action( 'wp_ajax_wdm_elem_cart_change_license', array($this,'wdm_elem_cart_change_license') );
        add_action( 'wp_ajax_nopriv_wdm_elem_cart_change_license', array($this,'wdm_elem_cart_change_license') );
        
        // Remove cart ajax
        add_action( 'wp_ajax_wdm_elem_cart_remove_item', array($this,'wdm_elem_cart_remove_item') );
        add_action( 'wp_ajax_nopriv_wdm_elem_cart_remove_item', array($this,'wdm_elem_cart_remove_item') );

        // Apply discount
        add_action( 'wp_ajax_wdm_edd_apply_discount', array($this,'wdm_edd_apply_discount') );
        add_action( 'wp_ajax_nopriv_wdm_edd_apply_discount', array($this,'wdm_edd_apply_discount') );
    
        // Remove Discount
        add_action( 'wp_ajax_wdm_edd_remove_discount', array($this,'wdm_edd_remove_discount') );
        add_action( 'wp_ajax_nopriv_wdm_edd_remove_discount', array($this,'wdm_edd_remove_discount') );
        
        // Add to cart from related products section
        add_action( 'wp_ajax_wdm_elem_related_product_add_to_cart', array($this,'wdm_elem_related_product_add_to_cart') );
        add_action( 'wp_ajax_nopriv_wdm_elem_related_product_add_to_cart', array($this,'wdm_elem_related_product_add_to_cart') );

        // Fetch Checkout fields and sections
        add_action( 'wp_ajax_wdm_elem_fetch_checkout_widgets', array($this,'wdm_elem_fetch_checkout_widgets') );
        add_action( 'wp_ajax_nopriv_wdm_elem_fetch_checkout_widgets', array($this,'wdm_elem_fetch_checkout_widgets') );

        add_action( 'wp_ajax_wdm_is_customer_registered', array($this,'wdm_is_customer_registered') );
        add_action( 'wp_ajax_nopriv_wdm_is_customer_registered', array($this,'wdm_is_customer_registered') );
        
        add_action( 'wp_ajax_nopriv_wdm_minimal_checkout_login', array($this,'wdm_minimal_checkout_login') );
    }

    public function wdm_elem_cart_change_license(){
		if(!empty($_POST['nonce']) && (wp_verify_nonce($_POST['nonce'], 'wdm-elem-cart') || wp_verify_nonce($_POST['nonce'], 'wdm-elem-related-product')) && !empty($_POST['license']) && isset($_POST['cart_key'])){
            
            // Get discount code, currently we are supporting only one coupon code at a time
            $discount_id = $amount = $discount_code = '';
            $discounts = EDD()->cart->get_discounts();
            foreach ( $discounts as $discount ) {
                $discount_id = edd_get_discount_id_by_code( $discount );
                $discount_code = $discount;
            }

            // update cart with new price option
            // Remove and add another option is not working due to cart item position using array key

            // Get cart item count before removing the item
            $cart_items_count = count(edd_get_cart_contents());
            $this->remove_item($_POST['cart_key']);

            // Get cart item count after removing the item to check if the item has been removed successfully
            $cart_items_count_after_remove = count(edd_get_cart_contents());
            $option_selected = explode('_',$_POST['license']);
            
            if(!empty($option_selected[0]) && !empty($option_selected[1])){
                edd_add_to_cart( $option_selected[0], array('price_id'=>$option_selected[1]) );
                $cart_items_count_after_add = count(edd_get_cart_contents());

                // Now apply the existing coupon again to avoid code removal in case of change license option
                if($discount_code){
                    $user = '';
                    if ( is_user_logged_in() ) {
                        $user = get_current_user_id();
                    } else {
                        if ( ! empty( $_POST['email'] ) ) {
                            $user = urldecode( $_POST['email'] );
                        }
                    }
                    $this->process_apply_discount($discount_code,$user);
                }
            }


            if($cart_items_count_after_remove < $cart_items_count && $cart_items_count==$cart_items_count_after_add){
                $amount    = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );
                $total = edd_get_cart_total();
                $subtotal = edd_get_cart_subtotal();
                $item_value = edd_get_price_option_amount( $option_selected[0], $option_selected[1] );
                // $coupon_value = filter_var( edd_cart_subtotal(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) - filter_var( edd_cart_total(0), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
                $coupon_value = edd_get_cart_subtotal() - EDD()->cart->get_total();
				$return = array(
                    'coupon_value'          => $coupon_value?html_entity_decode( edd_currency_symbol(edd_get_currency()) . number_format($coupon_value,2), ENT_COMPAT, 'UTF-8' ):'',
                    'subtotal_plain'        => $subtotal,
                    'subtotal'              => html_entity_decode( edd_currency_filter( edd_format_amount( $subtotal ) ), ENT_COMPAT, 'UTF-8' ),
                    'total_plain'           => $total,
                    'amount'                => $amount,
                    'code'                  => $discount,
                    'total'                 => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
                    'discounts'             => edd_get_cart_discounts(),
                    'item_value'            => html_entity_decode( edd_currency_filter( edd_format_amount( $item_value ) ), ENT_COMPAT, 'UTF-8' ),
                    'msg'                   => 'valid',
                    'is_renewal'            => EDD()->session->get( 'edd_is_renewal' )
                );
                echo json_encode($return);
                die();
            }
		}
        die();
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

    public function wdm_edd_apply_discount(){
		if ( isset( $_POST['code'] ) ) {
            $discount_code = sanitize_text_field( $_POST['code'] );
            $user = '';

            if ( is_user_logged_in() ) {
                $user = get_current_user_id();
            } else {
                if ( ! empty( $_POST['email'] ) ) {
                    $user = urldecode( $_POST['email'] );
                }
            }
			$return = $this->process_apply_discount($discount_code,$user);
			echo json_encode($return);
		}
		edd_die();
    }

    public function process_apply_discount($discount_code,$user){
        $return = array(
            'msg'  => '',
            'code' => $discount_code
        );

        if ( edd_is_discount_valid( $discount_code, $user ) ) {
            $discount  = edd_get_discount_by_code( $discount_code );
            $amount    = edd_format_discount_rate( edd_get_discount_type( $discount->ID ), edd_get_discount_amount( $discount->ID ) );
            $discounts = edd_set_cart_discount( $discount_code );
            $total     = edd_get_cart_total( $discounts );
            $subtotal = edd_get_cart_subtotal();
            
            // $coupon_value = filter_var( edd_cart_subtotal(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) - filter_var( edd_cart_total(0), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
            $coupon_value = edd_get_cart_subtotal() - EDD()->cart->get_total();


            $return = array(
                'coupon_value'          => html_entity_decode( edd_currency_symbol(edd_get_currency()) . number_format($coupon_value,2), ENT_COMPAT, 'UTF-8' ),
                'subtotal_plain'        => $subtotal,
                'subtotal'              => html_entity_decode( edd_currency_filter( edd_format_amount( $subtotal ) ), ENT_COMPAT, 'UTF-8' ),
                'msg'                   => 'valid',
                'amount'                => $amount,
                'total_plain'           => $total,
                'total'                 => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
                'code'                  => $discount_code,
                'html'                  => edd_get_cart_discounts_html( $discounts )
            );
        } else {
            $errors = edd_get_errors();
            $return['msg']  = $errors['edd-discount-error'];
            edd_unset_error( 'edd-discount-error' );
        }

        // Allow for custom discount code handling
        return apply_filters( 'edd_ajax_discount_response', $return );
    }
    
    /**
     * Removes a discount code from the cart via ajax
     *
     * @since 1.7
     * @return void
     */
    function wdm_edd_remove_discount() {
        if ( isset( $_POST['code'] ) ) {

            edd_unset_cart_discount( urldecode( $_POST['code'] ) );

            $total = edd_get_cart_total();
            $subtotal = edd_get_cart_subtotal();
                    
            $return = array(
                'subtotal'      => html_entity_decode( edd_currency_filter( edd_format_amount( $subtotal ) ), ENT_COMPAT, 'UTF-8' ),
                'subtotal_plain'=> $subtotal,
                'total_plain'   => $total,
                'total'         => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
                'code'          => $_POST['code'],
                'discounts'     => edd_get_cart_discounts(),
                'msg'           => 'valid',
                'html'          => edd_get_cart_discounts_html()
            );

            echo json_encode( $return );
        }
        edd_die();
    }

    public function wdm_elem_related_product_add_to_cart(){
		if(!empty($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'wdm-elem-related-product') && !empty($_POST['download'])){
            
            // Get discount code, currently we are supporting only one coupon code at a time
            $html_tr = $discount_id = $amount = $discount_code = '';
            $discounts = EDD()->cart->get_discounts();
            foreach ( $discounts as $discount ) {
                $discount_id = edd_get_discount_id_by_code( $discount );
                $discount_code = $discount;
            }

            // update cart with new price option
            // Remove and add another option is not working due to cart item position using array key

            // Get cart item count before removing the item
            $cart_items_count = count(edd_get_cart_contents());
            $download = $_POST['download'];
            
            if(!empty($download)){
                edd_add_to_cart( $download );
                $cart_items_count_after_add = count(edd_get_cart_contents());

                // Now apply the existing coupon again to avoid code removal in case of change license option
                if($discount_code){
                    $user = '';
                    if ( is_user_logged_in() ) {
                        $user = get_current_user_id();
                    } else {
                        if ( ! empty( $_POST['email'] ) ) {
                            $user = urldecode( $_POST['email'] );
                        }
                    }
                    $this->process_apply_discount($discount_code,$user);
                }
            }


            if($cart_items_count < $cart_items_count_after_add){
                $amount    = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );
                $total = edd_get_cart_total();
                $subtotal = edd_get_cart_subtotal();
                $item_value = edd_get_price_option_amount( $option_selected[0], $option_selected[1] );
                // $coupon_value = filter_var( edd_cart_subtotal(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) - filter_var( edd_cart_total(0), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
                $coupon_value = edd_get_cart_subtotal() - EDD()->cart->get_total();
                $current_cart = edd_get_cart_contents();
                if($current_cart){
                    $this->related_cart_nonce = $_POST['nonce'];
                    $html_tr = $this->generate_tr_html($download,count($current_cart)-1,$current_cart[count($current_cart)-1],$_POST['nonce']); 
                }

				$return = array(
                    'coupon_value'          => $coupon_value?html_entity_decode( edd_currency_symbol(edd_get_currency()) . number_format($coupon_value,2), ENT_COMPAT, 'UTF-8' ):'',
                    'subtotal_plain'        => $subtotal,
                    'subtotal'              => html_entity_decode( edd_currency_filter( edd_format_amount( $subtotal ) ), ENT_COMPAT, 'UTF-8' ),
                    'total_plain'           => $total,
                    'amount'                => $amount,
                    'code'                  => $discount,
                    'total'                 => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
                    'discounts'             => edd_get_cart_discounts(),
                    'item_value'            => html_entity_decode( edd_currency_filter( edd_format_amount( $item_value ) ), ENT_COMPAT, 'UTF-8' ),
                    'tr'                    => $html_tr,
                    'msg'                   => 'valid'
                );
                echo json_encode($return);
                die();
            }
		}
        die();
    }

    public function generate_tr_html($download,$item_key,$item,$nonce){
        $thumbnail = '';
        if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $item['id'] ) ) {
            $thumbnail .='<div class="wdm_edd_cart_item_image">';
            $thumbnail .= get_the_post_thumbnail( $item['id'], array( 40,40 ) );
            $thumbnail .= '</div>';
        }
        list($license_priod,$license_options) = $this->get_cart_table_details($item);
        $item_name = '<div class="wdm_edd_cart_item_name_div"><span class="product_item_name">'.$this->get_product_title($item['id']).'</span>'.(!empty($license_priod)?'<span class="recurring_license_period">'.$license_priod.'</span>':'').'</div>';
        $html .='<tr>';
        $html .='<td class="wdm_edd_cart_item_name_value">'.$thumbnail.$item_name.'</td>';
        $html .='<td class="wdm_edd_cart_item_licenses">'.$this->get_license_options_html($license_options,$item_key).'</td>';
        $html .='<td class="wdm_edd_cart_item_price_value">'.edd_cart_item_price( $item['id'], $item['options'] ).'</td>';
        $html .='<td class="wdm_edd_cart_item_action"><a data-nonce="'.$nonce.'" data-cart-key="'.$item_key.'" href="#"><img src="'.WDMELE_PLUGIN_URL.'assets/images/remove_icon.svg"><span>Remove</span></a></td>';
        $html .='</tr>';

        return $html;
    }

    public function get_cart_table_details($cart_item){
		$return = array(false,'');
		$download_id = $cart_item['id'];
		$added_download			= new EDD_SL_Download( $download_id );
		$licensing_enabled		= $added_download->licensing_enabled();
		$has_variable_prices	= $added_download->has_variable_prices();
		$is_bundle				= $added_download->is_bundled_download();
		
		$return[0] = str_replace( 'Billed once per year until cancelled with a 15 day free trial', 'Billed yearly until cancelled', $this->get_period($cart_item) );
		if ( ! $licensing_enabled ) {
			$return[1] = '';
		}else{
			$return[1] = $this->get_license_options($added_download,$cart_item,$has_variable_prices);
		}
		return $return;
    }

    public function get_period($cart_item){
		$period = $cart_item['options']['recurring']['period'];
		if(empty($period)){
			return '';
		} 
		$times  = $cart_item['options']['recurring']['times'];
		if ( ! empty( $cart_item['options']['recurring']['trial_period']['unit'] ) && ! empty( $cart_item['options']['recurring']['trial_period']['quantity'] ) && ( ! edd_get_option( 'recurring_one_time_trials' ) || ! edd_recurring()->has_trialed( $download_id ) ) ) {
			$free_trial = $cart_item['options']['recurring']['trial_period']['quantity'] . ' ' . strtolower( edd_recurring()->get_pretty_singular_subscription_frequency( $cart_item['options']['recurring']['trial_period']['unit'] ) );
		}

		if ( empty( $times ) ) {
			if ( empty( $free_trial ) ) {
				$output = sprintf( __( 'Billed once per %s until cancelled', 'edd-recurring' ), strtolower( $period ) );
			} else {
				$output = sprintf( __( 'Billed once per %s until cancelled with a %s free trial', 'edd-recurring' ), strtolower( $period ), $free_trial );
			}

		}else{

			if ( empty( $free_trial ) ) {
				$output = sprintf( __( 'Billed once per %s, %d times', 'edd-recurring' ), strtolower( edd_recurring()->get_pretty_singular_subscription_frequency( $period ) ), $times );
			}else {
				$output = sprintf( __( 'Billed once per %s until cancelled with a %s free trial', 'edd-recurring' ), strtolower( $period ), $free_trial );
			}

		}
		return $output;
    }
    
    public function get_product_title($download_id=0){
		$title = '';
		if($download_id){
			$title = get_the_title( $download_id );
		}
		return $title;
	}
    
    public function get_license_options($added_download,$cart_item,$has_variable_prices){
		$i = 0;
		
		if($has_variable_prices){
			$added_download_period = 'lifetime';
			if(!empty($cart_item['options']['recurring']['period'])){
				$added_download_period = $cart_item['options']['recurring']['period'];
			}
			$added_download_license_type = '';
			
			$added_download_prices = $added_download->get_prices();
			
			$add_download_activation_limit = 0;
			foreach ($added_download_prices as $key => $price) {
				if($cart_item['options']['price_id']==$key){
					$add_download_activation_limit = $added_download->get_activation_limit($key);
					$added_download_license_type = (strpos(strtolower($price['name']), 'business') !== false)?'business':((strpos(strtolower($price['name']), 'single') !== false)?'single':'');
				}
			}
			// echo '<pre>';
			// print_r($added_download_prices);
			// echo '</pre>';
			foreach ($added_download_prices as $key => $price) {

				// To check lifetime and recurring license match
				$added_download_price_period = 'lifetime';
				if(!empty($price['period'])){
					$added_download_price_period = $price['period'];
				}
				if($added_download_period!==$added_download_price_period){
					continue;
				}
				
				// To compare single and business licenses match
				$added_download_price_license_type = (strpos(strtolower($price['name']), 'business') !== false)?'business':((strpos(strtolower($price['name']), 'single') !== false)?'single':'');
				if($added_download_price_license_type!==$added_download_license_type){
					continue;
				}
				
				if($cart_item['options']['price_id']==$key){
					$output[$i]['selected'] = 1;
				}
				
				$activation_limit = $added_download->get_activation_limit($key);

				
				// To show only 1, 5, 10 and related activation limits
				if(
					($added_download_price_license_type=='business' && (in_array($activation_limit,array(2,10,20)))) 
					||
					($added_download_price_license_type=='single' && (in_array($activation_limit,array(1,5,10))))
				){
					$output[$i]['value'] = $added_download->ID . '_' . $key;
					$output[$i]['label'] = ($added_download_price_license_type=='business'?($activation_limit/2). ' Business':$activation_limit) . ' ' . _n( 'License', 'Licenses', $activation_limit);
				}
				$i++;
			}
		}else{
			$output[$i]['value'] = $added_download->ID;
			$activation_limit = $added_download->get_activation_limit();
			if(!empty($activation_limit)){
				$output[$i]['label'] = ($added_download_price_license_type=='business'?($activation_limit/2). ' Business':$activation_limit) . ' ' . _n( 'License', 'Licenses', $activation_limit);
			}
			$output[$i]['selected'] = 1;
		}
		return $output;
	}
	
	/**
	 * get_license_options_html generate select options html with license data
	 *
	 * @param  array $options Array containing license options data
	 * @param  int $item_key To keep the cart items key
	 * @return string
	 */
	public function get_license_options_html($options=array(),$item_key=0){
		$html = '';
		if($options){
			foreach ($options as $key => $value) {
				$radio_image = !empty($value['selected'])?WDMELE_PLUGIN_URL.'assets/images/selected_radio.svg':WDMELE_PLUGIN_URL.'assets/images/radio.svg';
				$html .= '<span class="license_options'.(!empty($value['selected'])?' license_options_checked':'').'"><span class="license_options_radio"><img class="radio_button_img" src="'.$radio_image.'"><input style="display:none" data-nonce="'.$this->related_cart_nonce.'" type="radio" name="license_options_'.$item_key.'" value="'.$value['value'].'" '. (!empty($value['selected'])?'checked="true"':'') .'></span> <span class="license_options_quantity">'.$value['label'].'</span></span>';
			}
		}
		return $html;
    }
    
    public function wdm_elem_fetch_checkout_widgets(){
        if(empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wdm-elem-register')){
            return json_encode(array(''));
        }
        if($_POST['customer_blng_wdgt']=='1'){
            $blng = $this->getBillingSection();
        }
        if($_POST['chkt_cart_table']=='1'){
            $chkt_cart = $this->getCartTableSection();
        }
        if($_POST['pyt_section']=='1'){
            $pyt_section = $this->getPaymentSection();
        }
    }

    public function wdm_is_customer_registered(){
        if(!empty($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'wdm-email-exists-nonce') && !empty($_POST['email'])){
            $return = array();
			if ( email_exists( $_POST['email'] ) ) {
                $return = array(
                    'message' => '<span class="edd-email-error">Email already exists, please <a class="show-login" href="javascript:void(0);">login</a></span>'
                );
            }
            echo json_encode($return);
            die();
        }
        die();
    }

    public function wdm_minimal_checkout_login(){
        check_ajax_referer( 'ajax-login-nonce', 'wdm-security' );
        // Nonce is checked, get the POST data and sign user on
        $info = array();
        $info['user_login'] = $_POST['email'];
        $info['user_password'] = $_POST['pass'];
        $info['remember'] = $_POST['remember'];

        $user_signon = wp_signon( $info, false );
        if ( is_wp_error($user_signon) ){
            echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
        } else {
            echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
        }

        die();
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new WdmElementorAjaxHandler;
        }
        return self::$instance;
    }
}
