<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Wisdm_Edd_Checkout {
    public $cart_nonce;
	// To store current class object
    private static $instance;

    private function __construct(){

        add_shortcode( 'wdm_minimal_download_checkout', array( $this, 'edd_checkout_form_shortcode' ) );

        add_filter( 'tml_shortcode', array( $this, 'wdm_tml_shortcode' ) , 10, 2 );

        // Change lost password text and remove login page title
		add_filter( 'tml_title', array($this,'tml_title'), 999, 2 );
		
		// Remove default edd_payment_mode_select
		remove_action( 'edd_payment_mode_select', 'edd_payment_mode_select', 10 );
		add_action( 'edd_payment_mode_select', array( $this, 'edd_payment_mode_select') );
		
		remove_action( 'edd_cart_empty', 'edd_csau_display_on_checkout_page', 10 );
		remove_all_actions( 'edd_before_purchase_form' );
		
		// add_action('edd_before_purchase_form', array($this, 'edd_before_purchase_form'));
		// wp_dequeue_script( 'wdm-edd-checkout-more-option' );


        // Chage license ajax
        add_action( 'wp_ajax_wdm_elem_cart_change_license', array($this,'wdm_elem_cart_change_license') );
        add_action( 'wp_ajax_nopriv_wdm_elem_cart_change_license', array($this,'wdm_elem_cart_change_license') );

        // Remove cart ajax
        add_action( 'wp_ajax_wdm_elem_cart_remove_item', array($this,'wdm_elem_cart_remove_item') );
        add_action( 'wp_ajax_nopriv_wdm_elem_cart_remove_item', array($this,'wdm_elem_cart_remove_item') );
        
        // Apply discount
        add_action( 'wp_ajax_wdm_edd_apply_discount', array($this,'wdm_edd_apply_discount') );
        add_action( 'wp_ajax_nopriv_wdm_edd_apply_discount', array($this,'wdm_edd_apply_discount') );

        // Add to cart from related products section
        add_action( 'wp_ajax_wdm_elem_related_product_add_to_cart', array($this,'wdm_elem_related_product_add_to_cart') );
        add_action( 'wp_ajax_nopriv_wdm_elem_related_product_add_to_cart', array($this,'wdm_elem_related_product_add_to_cart') );

        // Remove Discount
        add_action( 'wp_ajax_wdm_edd_remove_discount', array($this,'wdm_edd_remove_discount') );
        add_action( 'wp_ajax_nopriv_wdm_edd_remove_discount', array($this,'wdm_edd_remove_discount') );

        // Fetch Checkout fields and sections
        add_action( 'wp_ajax_wdm_elem_fetch_checkout_widgets', array($this,'wdm_elem_fetch_checkout_widgets') );
        add_action( 'wp_ajax_nopriv_wdm_elem_fetch_checkout_widgets', array($this,'wdm_elem_fetch_checkout_widgets') );

        // Check if customer is registered
        add_action( 'wp_ajax_wdm_is_customer_registered', array($this,'wdm_is_customer_registered') );
        add_action( 'wp_ajax_nopriv_wdm_is_customer_registered', array($this,'wdm_is_customer_registered') );

        // Checkout Login
        add_action( 'wp_ajax_nopriv_wdm_minimal_checkout_login', array($this,'wdm_minimal_checkout_login') );

        // Remore Old Discount field
        remove_action( 'edd_checkout_form_top', 'edd_aad_discount_field', -1 );

        add_action( 'edd_purchase_form_after_user_info', array( $this, 'edd_user_info_fields' ), 9 );
        add_action( 'edd_register_fields_before', array( $this, 'edd_user_info_fields' ), 9 );
        
        // To change the register and login form sequence
        remove_action('edd_purchase_form', 'edd_show_purchase_form', 10);
        add_action( 'edd_purchase_form', array( $this, 'wdm_edd_show_purchase_form' ), 9 );
        remove_action('edd_purchase_form_register_fields', 'edd_get_register_fields', 10);
        add_action( 'edd_purchase_form_register_fields', array( $this, 'wdm_edd_get_register_fields' ), 9 );

        remove_action('edd_checkout_form_top', 'edd_agree_to_terms_js');

        add_filter( 'edd_get_checkout_button_purchase_label', array( $this, 'edd_get_checkout_button_purchase_label' ), 10, 2 );

        remove_all_actions('login_form');

        add_action('plugins_loaded', array($this, 'wdm_remove_actions'), 50);

    }

    public function wdm_remove_actions() {
        remove_action( 'edd_payment_mode_select', 'edd_payment_mode_select', 10 );
        remove_action( 'edd_cart_empty', 'edd_csau_display_on_checkout_page', 10 );
        remove_all_actions( 'edd_before_purchase_form' );
        remove_action( 'edd_checkout_form_top', 'edd_aad_discount_field', -1 );
        remove_action('edd_purchase_form', 'edd_show_purchase_form', 10);
        remove_action('edd_purchase_form_register_fields', 'edd_get_register_fields', 10);
        remove_action('edd_checkout_form_top', 'edd_agree_to_terms_js');
        remove_all_actions('login_form');
    }
        
    public function edd_get_checkout_button_purchase_label( $complete_purchase, $label ){
     
        $is_checkout_active_widget = $this->is_widget_active('wisdm-minimal-checkout-widget-id');
        if(!$is_checkout_active_widget){
            return $complete_purchase;
        }
        if ( edd_get_cart_total() ) {
            $complete_purchase = __( 'Proceed to buy', 'easy-digital-downloads' );
        } else {
            $label             = edd_get_option( 'free_checkout_label', '' );
            $complete_purchase = ! empty( $label ) ? $label : __( 'Free Download', 'easy-digital-downloads' );
        }
        return $complete_purchase;
    }
    public function tml_title($title, $action){
		if($action=='lostpassword'){
			$title = __( 'Forgot Password?', 'wdm-elementor-addon-extension' );
		}
		if($action=='' || $action='login'){
			$title = '';
		}
		return $title;
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

	public function edd_payment_mode_select() {
		// if(WdmMinimalCheckout::$edd_payment_mode_select_called!=0){
		// 	return;
		// }
		// WdmMinimalCheckout::$edd_payment_mode_select_called = 1;
		$gateways = edd_get_enabled_payment_gateways( true );
		$page_URL = edd_get_current_page_url();
		$chosen_gateway = edd_get_chosen_gateway();
		?>
		<div id="edd_payment_mode_select_wrap">
			<?php do_action('edd_payment_mode_top'); ?>
			<?php if( edd_is_ajax_disabled() ) { ?>
			<form id="edd_payment_mode" action="<?php echo $page_URL; ?>" method="GET">
			<?php } ?>
				<fieldset id="edd_payment_mode_select">
					<legend><?php _e( 'Select Payment Method', 'easy-digital-downloads' ); ?></legend>
					<?php do_action( 'edd_payment_mode_before_gateways_wrap' ); ?>
					<div id="edd-payment-mode-wrap">
						<?php
	
						do_action( 'edd_payment_mode_before_gateways' );
	
						foreach ( $gateways as $gateway_id => $gateway ) :
	
							$label         = apply_filters( 'edd_gateway_checkout_label_' . $gateway_id, $gateway['checkout_label'] );
							$checked       = checked( $gateway_id, $chosen_gateway, false );
							$checked_class = $checked ? ' edd-gateway-option-selected' : '';
							$nonce         = ' data-' . esc_attr( $gateway_id ) . '-nonce="' . wp_create_nonce( 'edd-gateway-selected-' . esc_attr( $gateway_id ) ) .'"';
							$checked_class_span = $checked ? ' checked-radio-span' : '';

							echo '<label for="edd-gateway-' . esc_attr( $gateway_id ) . '" class="edd-gateway-option' . $checked_class . '" id="edd-gateway-option-' . esc_attr( $gateway_id ) . '">';
								echo '<span class="radio-span' . $checked_class_span . '"></span><input type="radio" name="payment-mode" class="edd-gateway" id="edd-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . $nonce . '>' . esc_html( $label );
							echo '</label>';
	
						endforeach;
	
						do_action( 'edd_payment_mode_after_gateways' );
	
						?>
					</div>
					<?php do_action( 'edd_payment_mode_after_gateways_wrap' ); ?>
				</fieldset>
				<fieldset id="edd_payment_mode_submit" class="edd-no-js">
					<p id="edd-next-submit-wrap">
						<?php echo edd_checkout_button_next(); ?>
					</p>
				</fieldset>
			<?php if( edd_is_ajax_disabled() ) { ?>
			</form>
			<?php } ?>
		</div>
		<div id="edd_purchase_form_wrap"></div><!-- the checkout fields are loaded into this-->
	
		<?php do_action('edd_payment_mode_bottom');
	}
    // To change the string "Need to create an account?" to the bottom of the form
    function wdm_edd_get_login_fields()
    {
        $color = edd_get_option('checkout_color', 'gray');
        $color = ($color == 'inherit') ? '' : $color;
        $edd_no_gst_chkot = edd_no_guest_checkout();
        // $style = edd_get_option('button_style', 'button');

        $show_register_form = edd_get_option('show_register_form', 'none');

        ob_start(); ?>
            <fieldset id="edd_login_fields">
                <?php do_action('edd_checkout_login_fields_before'); ?>
                <p id="edd-user-login-wrap">
                    <label class="edd-label" for="edd_user_login">
                        <?php _e('Username or Email', 'easy-digital-downloads'); ?>
        <?php
        if ($edd_no_gst_chkot) {
            ?>
                        <span class="edd-required-indicator">*</span>
            <?php
        }
        ?>
                    </label>
                    <input class="<?php
                    if ($edd_no_gst_chkot) {
                        echo 'required ';
                    } ?>edd-input" type="text" name="edd_user_login" id="edd_user_login" value="" placeholder="<?php _e('Your username or email address', 'easy-digital-downloads'); ?>"/>
                </p>
                <p id="edd-user-pass-wrap" class="edd_login_password">
                    <label class="edd-label" for="edd_user_pass">
                        <?php _e('Password', 'easy-digital-downloads'); ?>
                        <?php
                        if ($edd_no_gst_chkot) {
                            ?>
                        <span class="edd-required-indicator">*</span>
                            <?php
                        } ?>
                    </label>
                    <input class="<?php
                    if ($edd_no_gst_chkot) {
                        echo 'required ';
                    } ?>edd-input" type="password" name="edd_user_pass" id="edd_user_pass" placeholder="<?php _e('Your password', 'easy-digital-downloads'); ?>"/>
                    <?php if ($edd_no_gst_chkot) : ?>
                        <input type="hidden" name="edd-purchase-var" value="needs-to-login"/>
                    <?php endif; ?>
                </p>
                <p id="edd-user-login-submit">
                    <input type="submit" class="edd-submit button <?php echo $color; ?>" name="edd_login_submit" value="<?php _e('Login', 'easy-digital-downloads'); ?>"/>
                    <?php wp_nonce_field('edd-login-form', 'edd_login_nonce', false, true); ?>
                </p>
                <?php
                if ($show_register_form == 'both') {
                    ?>
                    <p id="edd-new-account-wrap">
                        <?php _e('Need to create an account?', 'easy-digital-downloads'); ?>
                        <a href="<?php echo esc_url(remove_query_arg('login')); ?>" class="edd_checkout_register_login" data-action="checkout_register" data-nonce="<?php echo wp_create_nonce('edd_checkout_register'); ?>">
                            <?php
                            _e('Register', 'easy-digital-downloads');
                            if (!$edd_no_gst_chkot) {
                                echo ' ' . __('or checkout as a guest.', 'easy-digital-downloads');
                            } ?>
                        </a>
                    </p>
                    <?php
                } ?>
                <?php do_action('edd_checkout_login_fields_after'); ?>
            </fieldset><!--end #edd_login_fields-->
        <?php
        echo ob_get_clean();
    }

    public function wdm_edd_show_purchase_form(){
        $is_checkout_active_widget = $this->is_widget_active('wisdm-minimal-checkout-widget-id');
        if(!$is_checkout_active_widget){
            return;
        }
        // remove_action( 'edd_purchase_form', 'wdm_edd_show_purchase_form', 10 );
        $payment_mode = edd_get_chosen_gateway();
        /**
         * Hooks in at the top of the purchase form
         *
         * @since 1.4
         */
        do_action('edd_purchase_form_top');

        if (edd_can_checkout()) {
            // do_action('edd_purchase_form_before_register_login');
            $show_register_form = edd_get_option('show_register_form', 'none');
            if (($show_register_form === 'registration' || ($show_register_form === 'both' && ! isset($_GET['login']))) && ! is_user_logged_in()) : ?>
                <div id="edd_checkout_login_register">
                    <?php do_action('edd_purchase_form_register_fields'); ?>
                </div>
            <?php elseif (($show_register_form === 'login' || ($show_register_form === 'both' && isset($_GET['login']))) && ! is_user_logged_in()) : ?>
                <div id="edd_checkout_login_register">
                    <?php do_action('edd_purchase_form_login_fields'); ?>
                </div>
            <?php endif; ?>
            <?php
            if ((! isset($_GET['login']) && is_user_logged_in()) || ! isset($show_register_form) || 'none' === $show_register_form || 'login' === $show_register_form) {
                do_action('edd_purchase_form_after_user_info');
            }
            /**
             * Hooks in before Credit Card Form
             *
             * @since 1.4
             */
            do_action('edd_purchase_form_before_cc_form');
            if (edd_get_cart_total() > 0) {
            // Load the credit card form and allow gateways to load their own if they wish
                if (has_action('edd_' . $payment_mode . '_cc_form')) {
                    do_action('edd_' . $payment_mode . '_cc_form');
                } else {
                    do_action('edd_cc_form');
                }
            }

            /**
             * Hooks in after Credit Card Form
             *
             * @since 1.4
             */
            do_action('edd_purchase_form_after_cc_form');
        } else {
            // Can't checkout
            do_action('edd_purchase_form_no_access');
        }

        /**
         * Hooks in at the bottom of the purchase form
         *
         * @since 1.4
         */
        do_action('edd_purchase_form_bottom');
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
    
    public function is_widget_active($widget_name=''){
        return true;
        $current_url = edd_get_current_page_url();
        $set_checkout = '';
        if(function_exists('get_field')){
            if(!empty(get_field('wisdm_minimal_elementor_checkout_page','option'))){
                $set_checkout = get_field('wisdm_minimal_elementor_checkout_page','option');
            }
        }
        if( trailingslashit($set_checkout)==trailingslashit($current_url) ){
            return 1;
        }
        if($widget_name){
            global $post;
            $url     = wp_get_referer();
            $post_id = url_to_postid( $url );
            if(empty($url) && !empty($post)){
                $post_id = $post->ID;
            }
            $document = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( $post_id );
            if($document){
                $data = $document->get_elements_data();
                return $this->search_widget($data,'widgetType',$widget_name);
            }
		}
		return;
	}

    public function search_widget($array, $key, $value)
	{
		$results = array();
	
		if (is_array($array)) {
			if (isset($array[$key]) && $array[$key] == $value) {
				$results[] = $array;
			}
	
			foreach ($array as $subarray) {
				$results = array_merge($results, $this->search_widget($subarray, $key, $value));
			}
		}
	
		return $results;
	}

    /**
	 * Shows the User Info fields in the Personal Info box, more fields can be added
	 * via the hooks provided.
	 *
	 * @since 1.3.3
	 * @return void
	 */
	public function edd_user_info_fields() {
        $is_checkout_active_widget = $this->is_widget_active('wisdm-minimal-checkout-widget-id');
        if(!$is_checkout_active_widget){
            return;
        }
        remove_action( 'edd_purchase_form_after_user_info', 'edd_user_info_fields', 10 );
        remove_action( 'edd_register_fields_before', 'edd_user_info_fields', 10 );
        $nonce = wp_create_nonce('wdm-email-exists-nonce');
        $customer = EDD()->session->get( 'customer' );
		$customer = wp_parse_args( $customer, array( 'first_name' => '', 'last_name' => '', 'email' => '' ) );

		if( is_user_logged_in() ) {
			$user_data = get_userdata( get_current_user_id() );
			foreach( $customer as $key => $field ) {

				if ( 'email' == $key && empty( $field ) ) {
					$customer[ $key ] = $user_data->user_email;
				} elseif ( empty( $field ) ) {
					$customer[ $key ] = $user_data->$key;
				}

			}
		}

        $customer = array_map( 'sanitize_text_field', $customer );
        if(isset($user_data) && !empty($user_data)){
            $customer_obj = new EDD_Customer( $user_data->user_email );
            if(!empty($customer_obj)){
                if($customer_obj->get_meta('_phone_number', true)){
                    $customer[ '_phone_number' ] = $customer_obj->get_meta('_phone_number', true);
                }
                unset($customer_obj);
            }
        }
		?>
        <fieldset id="edd_checkout_user_login" style="display:none">
            <legend>
				<span class="wdm-box-heading text-left">Account login</span>
				<span class="wdm-login-toggler text-right"> Need to create an account?<a id="wdm_login_tab" href="javascript:void(0);" target="_self"> Register </a> </span>
            </legend>
            <?php // echo do_shortcode( '[theme-my-login action="login" login_template="wdm-minimal-login-form.php"]' );?>
            <?php echo do_shortcode( '[theme-my-login action="login" login_template="wisdm-checkout-template.php"]' );?>
		</fieldset>
		<fieldset id="edd_checkout_user_info" style="<?php echo is_user_logged_in()?'display:none':''?>">
            <legend>
                <span class="wdm-box-heading text-left"><?php echo apply_filters( 'edd_checkout_personal_info_text', esc_html__( 'Create account to complete your purchase', 'easy-digital-downloads' ) ); ?>
                </span>
                <span class="wdm-register-toggler text-right">
                    <?php echo esc_html('Already registered?'); ?>
                    <a class="show-login" href="javascript:void(0);">
                        <?php echo esc_html('Login'); ?>
                    </a>
                </span>
            </legend>
            <?php do_action( 'edd_purchase_form_before_email' ); ?>
            <p id="edd-user-login-wrap" style="display:none;">
                <label for="edd_user_login">
                    <?php _e('Username', 'easy-digital-downloads'); ?>
                    <span class="edd-required-indicator">*</span>
                </label>
                <input name="edd_user_login" id="edd_user_login" class="required edd-input" type="text"/>
            </p>
			<p id="edd-email-wrap">
				<label class="edd-label" for="edd-email">
					<?php esc_html_e( 'Your Email', 'easy-digital-downloads' ); ?>
					<?php if( edd_field_is_required( 'edd_email' ) ) { ?>
						<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<input data-nonce="<?php echo $nonce?>" class="edd-input required" type="email" name="edd_email" id="edd-email" value="<?php echo esc_attr( $customer['email'] ); ?>" aria-describedby="edd-email-description"<?php if( edd_field_is_required( 'edd_email' ) ) {  echo ' required '; } ?>/>
			</p>
            <p id="edd-user-pass-wrap" style="display:none;">
                <label class="edd-label" for="edd_user_pass">
                    <?php _e('Password', 'easy-digital-downloads'); ?>
                </label>
                <input name="edd_user_pass" id="edd_user_pass" class="edd-input" placeholder="<?php _e('Password', 'easy-digital-downloads'); ?>" type="password"/>
            </p>
            <p id="edd-user-pass-confirm-wrap" class="edd_register_password">
                <label class="edd-label" for="edd_user_pass_confirm">
                    <?php _e('Set Your Password', 'easy-digital-downloads'); ?>
                    <span class="edd-required-indicator">*</span>
                </label>
                <input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="required edd-input" type="password"/>
            </p>
            <?php
            $defaultFirstName = $customer['first_name']; 
                if(empty($customer['first_name'])){
                    if ( is_user_logged_in() ) {
                        $userData = wp_get_current_user();
                        $defaultFirstName = $userData->user_login;
                        $defaultFirstName = $userData->user_login;
                        $defaultFirstName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $defaultFirstName); 
                    }
                } 
            ?>
            <p id="edd-first-name-wrap">
				<label class="edd-label" for="edd-first">
					<?php esc_html_e( 'First Name', 'easy-digital-downloads' ); ?>
					<?php if( edd_field_is_required( 'edd_first' ) ) { ?>
						<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<input class="edd-input required" type="text" name="edd_first" id="edd-first" value="<?php echo esc_attr( $defaultFirstName ); ?>"<?php if( edd_field_is_required( 'edd_first' ) ) {  echo '  required'; } ?> aria-describedby="edd-first-description" />
			</p>
			<p id="edd-last-name-wrap">
				<label class="edd-label" for="edd-last">
					<?php esc_html_e( 'Last Name', 'easy-digital-downloads' ); ?>
					<?php if( edd_field_is_required( 'edd_last' ) ) { ?>
						<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<input class="edd-input<?php if( edd_field_is_required( 'edd_last' ) ) { echo ' required'; } ?>" type="text" name="edd_last" id="edd-last" value="<?php echo esc_attr( $customer['last_name'] ); ?>"<?php if( edd_field_is_required( 'edd_last' ) ) {  echo ' required '; } ?> aria-describedby="edd-last-description"/>
			</p>
            <?php do_action( 'edd_purchase_form_after_email' ); ?>
			<?php do_action( 'edd_purchase_form_user_info' ); ?>
			<?php do_action( 'edd_purchase_form_user_info_fields' ); ?>
		</fieldset>
        <fieldset id="edd_cc_address" class="edwiser-minimal-checkout-cc-address-wrapper cc-address">
            <p id="edd-card-address-wrap">
                <label for="card_address" class="edd-label">
                    <?php _e( 'Address', 'easy-digital-downloads' ); ?>
                    <span class="edd-required-indicator">*</span>
                </label>
                <textarea id="card_address" name="card_address" class="card-address edd-input required" placeholder="<?php _e( 'Address line 1', 'easy-digital-downloads' ); ?>" value="<?php echo empty( $customer['address']['line1'] ) ? '' : $customer['address']['line1']; ?>" rows="1" required></textarea>
            </p>
            <p id="edd-card-zip-wrap">
                <label for="card_zip" class="edd-label">
                    <?php _e( 'Pin code', 'easy-digital-downloads' ); ?>
                    <span class="edd-required-indicator">*</span>
                </label>
                <input type="text" size="4" id="card_zip" name="card_zip" class="card-zip edd-input required" placeholder="<?php _e( 'Zip / Postal Code', 'easy-digital-downloads' ); ?>" value="<?php echo empty( $customer['address']['zip'] ) ? '' : $customer['address']['zip']; ?>" required>
            </p>
            <p id="edd-card-country-wrap">
                <label for="billing_country" class="edd-label">
                    <?php _e( 'Country', 'easy-digital-downloads' ); ?>
                    <span class="edd-required-indicator">*</span>
                </label>
                <select name="billing_country" id="billing_country" data-nonce="<?php echo wp_create_nonce( 'edd-country-field-nonce' ); ?>" class="billing_country edd-select required" required>
                    <?php
                    $selected_country = edd_get_shop_country();

                    if( ! empty( $customer['address']['country'] ) && '*' !== $customer['address']['country'] ) {
                        $selected_country = $customer['address']['country'];
                    }

                    $countries = edd_get_country_list();
                    foreach( $countries as $country_code => $country ) {
                        echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
                    }
                    ?>
                </select>
            </p>
        </fieldset>
		<?php
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

    public function wdm_edd_get_register_fields(){
        $is_checkout_active_widget = $this->is_widget_active('wisdm-minimal-checkout-widget-id');
        if(!$is_checkout_active_widget){
            return;
        }
        remove_action('edd_purchase_form_register_fields', 'wdm_edd_get_register_fields', 10);
        $show_register_form = edd_get_option('show_register_form', 'none');
        $edd_no_gst_chkot = edd_no_guest_checkout();
        if (!$edd_no_gst_chkot) {
            ob_start(); ?>
            <fieldset id="edd_register_fields">

                <?php do_action('edd_register_fields_before'); ?>

                <fieldset id="edd_register_account_fields">
                    <legend><?php
                    _e('Create an account', 'easy-digital-downloads');
                        echo ' ' . __('(optional)', 'easy-digital-downloads');
                    ?></legend>
                    <?php do_action('edd_register_account_fields_before'); ?>
                    <p id="edd-user-email-wrap">
                        <label for="edd_user_email">
                            <?php _e('Email', 'easy-digital-downloads'); ?>
                        </label>
                        <span class="edd-description"><?php _e('The email you will use to log into your account.', 'easy-digital-downloads'); ?></span>
                        <input name="edd_user_email" id="edd_user_email" class="edd-input" type="text" placeholder="<?php _e('Email', 'easy-digital-downloads'); ?>"/>
                    </p>
                    <p id="edd-user-pass-confirm-wrap" class="edd_register_password">
                        <label for="edd_user_pass_confirm">
                            <?php _e('Password', 'easy-digital-downloads'); ?>
                        </label>
                        <span class="edd-description"><?php _e('Password.', 'easy-digital-downloads'); ?></span>
                        <input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="edd-input" placeholder="<?php _e('Password', 'easy-digital-downloads'); ?>" type="password"/>
                    </p>
                    <?php do_action('edd_register_account_fields_after'); ?>
                    <?php
                    if ($show_register_form == 'both') {
                        ?>
                        <p id="edd-login-account-wrap">
                            <?php _e('Already have an account?', 'easy-digital-downloads'); ?>
                             <a href="<?php echo esc_url(add_query_arg('login', 1)); ?>" class="edd_checkout_register_login" data-action="checkout_login" data-nonce="<?php echo wp_create_nonce('edd_checkout_login'); ?>">
                                    <?php _e('Login', 'easy-digital-downloads'); ?>
                             </a>
                        </p>
                        <?php
                    } ?>
                </fieldset>
                <?php do_action('edd_register_fields_after'); ?>

                <input type="hidden" name="edd-purchase-var" value="needs-to-register"/>

                <?php do_action('edd_purchase_form_user_info'); ?>
                <?php do_action('edd_purchase_form_user_register_fields'); ?>

            </fieldset>
            <?php
        } else {
            ob_start(); ?>
            <fieldset id="edd_register_fields">
                <?php do_action('edd_register_fields_before'); ?>
                <?php do_action('edd_register_account_fields_before'); ?>
                <?php do_action('edd_register_account_fields_after'); ?>
                <?php do_action('edd_register_fields_after'); ?>
                <input type="hidden" name="edd-purchase-var" value="needs-to-register"/>
                <?php do_action('edd_purchase_form_user_info'); ?>
                <?php do_action('edd_purchase_form_user_register_fields'); ?>
            </fieldset>
            <?php
        }
        echo ob_get_clean();
    
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

    public function edd_checkout_form_shortcode( $atts, $content = null ) {
        $this->cart_nonce = wp_create_nonce( 'wdm-elem-cart' );

		return $this->edd_checkout_form();
	}

    // To override TML Login template
    function wdm_tml_shortcode($content, $action_name)
    {
        // if NSL_PATH_FILE is defined it means nextend facebook connect social login plugin is active
        if (defined('NSL_PATH_FILE') && $action_name=='login') {
            // $checkout_page_url = function_exists('get_field') ? get_field('wisdm_minimal_elementor_checkout_page','option') : '';
            // $checkout_page_id  = url_to_postid($checkout_page_url);

            global $wp;
    		$checkout_page_id = edd_get_option( 'purchase_page', '' );

            // For checkout page only
            if (edd_is_checkout()) {
                ob_start();
                require_once MYEW_PLUGIN_PATH . '/includes/wisdm-checkout-template.php';
                $content = ob_get_clean();
            }
        }

        return $content;
    }
    public function edd_checkout_form() {
        $payment_mode = edd_get_chosen_gateway();
        $form_action  = esc_url( edd_get_checkout_uri( 'payment-mode=' . $payment_mode ) );

        ob_start();
            echo '<div id="edd_checkout_wrap">';
            if ( edd_get_cart_contents() || edd_cart_has_fees() ) :
                ?>
                <div id="edd_checkout_form_wrap" class="edd_clearfix">
                    <?php
                        $this->generate_cart_section();
                        $this->generate_discount_section();
                        $this->edd_checkout_cart();
                        do_action( 'edd_before_purchase_form' );
                    ?>
                    <form id="edd_purchase_form" class="edd_form" action="<?php echo $form_action; ?>" method="POST">
                        <?php
                        /**
                         * Hooks in at the top of the checkout form
                         *
                         * @since 1.0
                         */
                        do_action( 'edd_checkout_form_top' );
    
                        // wdm code commented
                        // if ( edd_is_ajax_disabled() && ! empty( $_REQUEST['payment-mode'] ) ) {
                        //     do_action( 'edd_purchase_form' );
                        // } elseif ( edd_show_gateways() ) {
                        //     do_action( 'edd_payment_mode_select'  );
                        // } else {
                        //     do_action( 'edd_purchase_form' );
                        //     // do_action( 'edd_payment_mode_select'  );
                        // }
                        // wdm code commented

                        do_action( 'edd_purchase_form' );

    
                        /**
                         * Hooks in at the bottom of the checkout form
                         *
                         * @since 1.0
                         */
                        do_action( 'edd_checkout_form_bottom' );
                        ?>
                    </form>
                    <?php //do_action( 'edd_after_purchase_form' ); ?>
                </div><!--end #edd_checkout_form_wrap-->
            <?php
            else:
                /**
                 * Fires off when there is nothing in the cart
                 *
                 * @since 1.0
                 */
                do_action( 'edd_cart_empty' );
            endif;
            echo '</div><!--end #edd_checkout_wrap-->';
        return ob_get_clean();
    }
    public function remove_item($key){
        return edd_remove_from_cart($key);
    }
    public function wdm_elem_cart_remove_item(){
        
        if(!empty($_POST['nonce']) && (wp_verify_nonce($_POST['nonce'], 'wdm-elem-cart') || wp_verify_nonce($_POST['nonce'], 'wdm-elem-related-product')) && isset($_POST['cart_key'])){
            $cart_items_count = count(edd_get_cart_contents());
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
                    'amount'                => $amount
                );
                echo json_encode($return);
                die();
            }
        }
        echo 0;
        die();
    }
    

    public function generate_cart_section(){
        $cart_contents = edd_get_cart_contents();
        ?>
		<span class="cart-table-heading-span">You Are Purchasing</span>
        <table id="wdm_edd_checkout_cart">
            <tbody>
                <?php 
                    foreach ( $cart_contents as $item_key=>$item ) {
                        $renewal_data = $this->renewal_notice( $item );
                        list($license_priod,$license_options) = $this->get_cart_table_details($item);
                        $license_options_html = '<div class="wdm_edd_cart_item_licenses" data-nonce="'.$this->cart_nonce.'" data-cart-key="'.$item_key.'" >'.$this->get_license_options_html($license_options,$item_key).'</div>';
                        $remove_item_link = '<a href="javascript:void(0);" title="Remove Product" target="_self" data-nonce="'.$this->cart_nonce.'" class="wdm_remove_product"><i class="fa fa-times-circle" aria-hidden="true"></i></a>';
                        $item_name = '<div class="wdm_edd_cart_item_name_div"><span class="product_item_name">'.$this->get_product_title($item['id']). ' ' .$remove_item_link.'</span>'.(!empty($license_priod)?'<span class="recurring_license_period">'.$license_priod.'</span>':'').'</div>';
                        $item_name .= $renewal_data;
                        $item_name .= $license_options_html;
					
                        echo '<tr>';
                        echo '<td class="wdm_edd_cart_item_name_value">'.$item_name.'</td>';
                        echo '<td class="wdm_edd_cart_item_price_value">'.edd_cart_item_price( $item['id'], $item['options'] ).'</td>';
                        echo '</tr>';
                    }
                ?>
            </tbody>
            <tfoot>
                <?php
                $discounts = EDD()->cart->get_discounts();
                $title = !empty($discounts)?'Sub Total':'Total'; 
                ?>
                <tr class="wdm_edd_cart_footer_row sub-total-row">
                    <th class="edd_cart_total"><?php echo $title?></th>
                    <!-- <th></th> -->
                    <th class="edd_cart_amount_row"><span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal()?>" data-total="<?php echo edd_get_cart_total()?>"><?php echo edd_cart_subtotal()?></span></th>
                    <!-- <th></th> -->
                </tr>
                <?php
                $style = 'display:none';
                if($discounts){
                    $style = '';
                }
                ?>
                <tr class="wdm_edd_cart_footer_row sub-subtotal-row" style="<?php echo $style?>">
                    <th class="edd_cart_total">Total</th>
                    <!-- <th></th> -->
                    <th class="edd_cart_amount_row"><span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal()?>" data-total="<?php echo edd_get_cart_total()?>"><span class="regular-price"><?php echo edd_cart_subtotal()?></span> <span class="discounted-price"><?php echo edd_cart_total()?></span></span></th>
                    <!-- <th></th> -->
                </tr>
                <?php
                unset($discounts);
                ?>
            </tfoot>
        </table>
        <?php
    }


    public function generate_discount_section(){
        $discounts = EDD()->cart->get_discounts();
        $discount = $rate = '';
        
        $subtotal = edd_get_cart_subtotal();
        $carttotal = EDD()->cart->get_total();
        $total = $subtotal - $carttotal;
    
        foreach ( $discounts as $discount ) {
            $discount_id = edd_get_discount_id_by_code( $discount );
            $rate        = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );
        }
        ?>
        <div id="wdm_discount_coupon">
            <a href="javascript:void(0);" target="_self" class="wdm_edd_discount_link" style="<?php echo !$discount?'':'display:none'?>"> Have a discount code? </a>

            <div id="wdm_edd_discount_wrap" style="">
                <span href="javascript:void(0);" target="_self" class="wdm_edd_discount_applied wdm_edd_discount_remove"
                    style="<?php echo $discount?'':'display:none'?>">
                    <span id="wdm_coupon_amount"> <?php echo $rate?> </span>
                    <span id="wdm_coupon_amount_text"> discount applied successfully! </span>
                    <a href="javascript:void(0);" target="_self" data-code="<?php echo $discount?>" class="wdm_remove_coupon_field"><i class="fa fa-times-circle" aria-hidden="true"></i></a>
                </span>
                <span id="wdm_coupon_error_wrap" style="display:none">
                    <span class="wdm_coupon_error"></span>
                    <a href="javascript:void(0);" target="_self" class="wdm_try_again_coupon">Try again?</a>
                </span>
            </div>
            <div id="wdm_coupon_field" style="display:none">
                <input placeholder="Enter Your Discount Code" class="edd-input" type="text" id="wdm-edd-discount"
                    name="edd-discount">
                <input id="wdm-edd-discount-button" type="button" class="edd-apply-discount edd-submit button"
                    value="Apply">
                <input id="wdm-edd-discount-cancel-button" type="button" class="edd-cancel-discount edd-submit edd-cancel button"
                    value="Cancel">
            </div>
        </div>
        <?php
    }

    public function edd_checkout_cart() {

		// Check if the Update cart button should be shown
		if( edd_item_quantities_enabled() ) {
			add_action( 'edd_cart_footer_buttons', 'edd_update_cart_button' );
		}
	
		// Check if the Save Cart button should be shown
		if( ! edd_is_cart_saving_disabled() ) {
			add_action( 'edd_cart_footer_buttons', 'edd_save_cart_button' );
		}
	
		do_action( 'edd_before_checkout_cart' );
		echo '<form id="edd_checkout_cart_form" method="post">';
			echo '<div id="edd_checkout_cart_wrap" style="display:none">';
				do_action( 'edd_checkout_cart_top' );
				edd_get_template_part( 'wdm_minimal_checkout_cart' );
				// $this->generate_cart_table();
				do_action( 'edd_checkout_cart_bottom' );
			echo '</div>';
		echo '</form>';
		do_action( 'edd_after_checkout_cart' );
	}
    public function get_license_options_html($options=array(),$item_key=0){
		$html = '';
		if($options){
			foreach ($options as $key => $value) {
				$radio_image = !empty($value['selected'])?WDMELE_PLUGIN_URL.'assets/images/selected_radio.svg':WDMELE_PLUGIN_URL.'assets/images/radio.svg';
				$html .= '<span class="license_options'.(!empty($value['selected'])?' license_options_checked':'').'"><span class="license_options_radio"><img class="radio_button_img" src="'.$radio_image.'"><input style="display:none" data-nonce="'.$this->cart_nonce.'" type="radio" name="license_options_'.$item_key.'" value="'.$value['value'].'" '. (!empty($value['selected'])?'checked="true"':'') .'></span> <span class="license_options_quantity">'.$value['label'].'</span></span>';
			}
		}
		return $html;
	}
    public function get_product_title($download_id=0){
		$title = '';
		if($download_id){
			$title = get_the_title( $download_id );
		}
		return $title;
	}
	public function renewal_notice( $item ){
		global $edd_sl_cart_item_quantity_removed;
		if( empty( $item['options']['is_renewal'] ) || empty( $item['options']['license_key'] ) ) {
			return;
		}
		ob_start();
		?>
			<div class="edd-sl-renewal-details edd-sl-renewal-details-cart">
					<span class="edd-sl-renewal-label"><?php _e( 'Renewing', 'edd_sl' ); ?>:</span>
					<span class="edd-sl-renewal-key"><?php echo $item['options']['license_key']; ?></span>
			</div>
		<?php
		$edd_sl_cart_item_quantity_removed = true;
		add_filter( 'edd_item_quantities_enabled', '__return_false' );
		return ob_get_clean();
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
		$period = isset($cart_item['options']['recurring']['period']) ? $cart_item['options']['recurring']['period'] : null;
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
					$output[$i]['label'] = (@$added_download_price_license_type=='business'?($activation_limit/2). ' Business':$activation_limit) . ' ' . _n( 'License', 'Licenses', (in_array($activation_limit,array(2,10,20))?intval($activation_limit/2):$activation_limit));
				}
				$i++;
			}
		}else{
			$output[$i]['value'] = $added_download->ID;
			$activation_limit = $added_download->get_activation_limit();
			if(!empty($activation_limit)){
				$output[$i]['label'] = ($added_download_price_license_type=='business'?($activation_limit/2). ' Business':$activation_limit) . ' ' . _n( 'License', 'Licenses', (in_array($activation_limit,array(2,10,20))?intval($activation_limit/2):$activation_limit));
			}
			$output[$i]['selected'] = 1;
		}
		return $output;
	}


    // To get object of the current class
    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new Wisdm_Edd_Checkout;
        }
        return self::$instance;
    }


}

Wisdm_Edd_Checkout::getInstance();
