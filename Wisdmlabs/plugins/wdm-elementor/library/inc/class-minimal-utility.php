<?php
// use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * WdmElementorMinimalUtility to handle common functionalities
 */
class WdmElementorMinimalUtility {
	
	// To store current class object
    private static $instance;
    public static $current_url;
    public static $set_checkout;
    public $cart_nonce;

    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        $this->cart_nonce = wp_create_nonce( 'wdm-elem-cart' );
        // $this->set_vars();
        // add_action( 'setup_theme', array( $this, 'init' ), 999 );
        
        // add_action( 'admin_init', array( $this, 'init' ), 999 );

        // To remove default address fields and add our custom field
        add_action( 'edd_after_cc_fields', array( $this, 'edd_default_cc_address_fields' ), 9 );

        // do_action( 'edd_checkout_user_error_checks', $user, $valid_data, $_POST );
        // To remove default personal information fields and add our custom fields
        add_action( 'edd_purchase_form_after_user_info', array( $this, 'edd_user_info_fields' ), 9 );
        add_action( 'edd_register_fields_before', array( $this, 'edd_user_info_fields' ), 9 );
        
        // Phone number field is required
        add_filter( 'edd_purchase_form_required_fields', array( $this,'edd_purchase_form_required_fields' ) );        

        // Credit card field
        
        add_action('edd_stripe_cc_form', array( $this, 'edds_credit_card_form' ), 9);
        
        add_action('edd_stripe_new_card_form', array( $this, 'edd_stripe_new_card_form' ), 9);
        
        // add_action( 'edd_2checkout_onsite_cc_form', array( $this, 'card_form' ) );
        // add_action( 'edd_cc_form', array( $this, 'edd_get_cc_form'), 9 );

        // add_filter( 'edd_stripe_js_vars', array( $this, 'edd_stripe_js_vars' ), 10);

        add_filter( 'edd_get_checkout_button_purchase_label', array( $this, 'edd_get_checkout_button_purchase_label' ), 10, 2 );

        // Save / update customer's phone number
        add_action( 'edd_checkout_before_gateway', array( $this, 'edd_checkout_before_gateway' ), 10, 3 );

        // Show customer's phone number on edit customer page 
        add_action( 'edd_customer_before_stats', array( $this, 'edd_customer_before_stats' ) );
        
        // Save order note
        add_filter( 'edd_payment_meta', array( $this, 'edd_payment_order_notes_meta' ) );
        
        // // Show order additional meta
        // add_action( 'edd_payment_personal_details_list', array( $this, 'edd_payment_personal_details_list' ), 10, 2 );

        add_action( 'edd_view_order_details_billing_after', array( $this, 'edd_view_order_details_billing_after' ) );
        // add_action( 'edd_payment_mode_select', array( $this, 'edd_payment_mode_select' ), 9 );

        add_filter('wdm_elementor_edd_checkout_cart_item_show_more_option', array($this,'show_more_options'));

        // Checkout terms acceptance input field
        add_action('edd_purchase_form_before_submit', array( $this, 'wdm_checkout_terms' ), 8);

        // Remove social login links above the registration fields
        add_action('edd_checkout_login_fields_before', array( $this, 'wdm_edd_checkout_login_fields_after'), 0);
        
        // Remove social login links above the registration fields
        add_action('edd_register_fields_before', array( $this, 'wdm_edd_checkout_login_fields_after' ), 0);

        // To change the register and login form sequence
        add_action( 'edd_purchase_form', array( $this, 'wdm_edd_show_purchase_form' ), 9 );

        // To change the registration form fields structure
        add_action( 'edd_purchase_form_register_fields', array( $this, 'wdm_edd_get_register_fields' ), 9 );

        // To redirect user to checkout page only after login
        add_filter( 'login_redirect',  array( $this, 'login_redirect'  ), 9, 3 );

        // To remove old custom checkout css
        // add_action( 'wp_footer', array( $this, 'wisdmlabs_enqueue_custom_for_super_socializer' ), 9 );
   
        add_filter( 'edd_require_billing_address', array( $this, 'edd_require_billing_address' ), 999 );
        // add_filter( 'wp_nav_menu_items', array( $this, 'add_contact_us_item_to_nav_menu' ), 10, 2 );

        add_action( 'edd_after_checkout_cart', array( $this, 'wdm_edd_after_checkout_cart' ), 9 );
        
        // add_action( 'edd_insert_user', array( $this, 'edd_update_user_details' ), 10, 2 );
        add_filter( 'edd_update_payment_meta__edd_payment_meta', array( $this, 'save_contry_fields' ), 99, 2 );
    }

    function save_contry_fields($meta_value, $payment_id ){
        if(!isset($meta_value['user_info']['address']['country']) || empty($meta_value['user_info']['address']['country']) ){
            if(! empty( $_POST['edd_cust_country'] )){
                $meta_value['user_info']['address']['country'] = ! empty( $_POST['edd_cust_country'] ) ? sanitize_text_field( $_POST['edd_cust_country'] ) : '';
                $meta_value['user_info']['address']['state'] = ! empty( $_POST['edd_cust_state'] ) ? sanitize_text_field( $_POST['edd_cust_state'] ) : '';
            }
        }
        return $meta_value;
    }

    public function edd_update_user_details($user_id, $user_data ){
        $address = array();
        $address['line1']   = ! empty( $_POST['card_address']    ) ? sanitize_text_field( $_POST['card_address']    ) : '';
        $address['line2']   = ! empty( $_POST['card_address_2']  ) ? sanitize_text_field( $_POST['card_address_2']  ) : '';
        $address['city']    = ! empty( $_POST['card_city']       ) ? sanitize_text_field( $_POST['card_city']       ) : '';
        $address['state']   = ! empty( $_POST['card_state']      ) ? sanitize_text_field( $_POST['card_state']      ) : '';
        $address['country'] = ! empty( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
        $address['zip']     = ! empty( $_POST['card_zip']        ) ? sanitize_text_field( $_POST['card_zip']        ) : '';

        if(empty($address['country'])){
            $_POST['billing_country'] = ! empty( $_POST['edd_cust_country'] ) ? sanitize_text_field( $_POST['edd_cust_country'] ) : '';
            if($_POST['billing_country'] == 'US'){
                $_POST['card_state']   = ! empty( $_POST['edd_cust_state'] ) ? sanitize_text_field( $_POST['edd_cust_state'] ) : '';
            }
        }
    }

    public function set_vars(){
        global $wp;
        self::$current_url = home_url( $wp->request );
        if(function_exists('get_field')){
            if(!empty(get_field('wisdm_minimal_elementor_checkout_page','option'))){
                self::$set_checkout = get_field('wisdm_minimal_elementor_checkout_page','option');
			}
		}
    }
    
    /**
     * init to setup current url and checkout url static variables which we will use to check if the current page is
     * minimal checkout page
     *
     * @return void
     */
    public function init(){
        global $wp;
        self::$current_url = home_url( $wp->request );
        if(function_exists('get_field')){
            if(!empty(get_field('wisdm_minimal_elementor_checkout_page','option'))){
				self::$set_checkout = get_field('wisdm_minimal_elementor_checkout_page','option');
			}
		}
    }

    /**
     * Outputs the default credit card address fields
     *
     * @since 1.0
     * @return void
     */
    function edd_default_cc_address_fields() {
        // if(!$this->is_elementor_checkout()){
        //     return;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
        remove_action( 'edd_after_cc_fields', 'edd_default_cc_address_fields', 10 );
        return;

        $logged_in = is_user_logged_in();
        $customer  = EDD()->session->get( 'customer' );
        $customer  = wp_parse_args( $customer, array( 'address' => array(
            'line1'   => '',
            'line2'   => '',
            'city'    => '',
            'zip'     => '',
            'state'   => '',
            'country' => ''
        ) ) );
        $customer['address'] = array_map( 'sanitize_text_field', $customer['address'] );

        if( $logged_in ) {

            $user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );

            foreach( $customer['address'] as $key => $field ) {

                if ( empty( $field ) && ! empty( $user_address[ $key ] ) ) {
                    $customer['address'][ $key ] = $user_address[ $key ];
                } else {
                    $customer['address'][ $key ] = '';
                }

            }
            $user_data = get_userdata( get_current_user_id() );
            $customer_obj = new EDD_Customer( $user_data->user_email );
            if(!empty($customer_obj)){
                if($customer_obj->get_meta('_company_name', true)){
                    $customer[ 'address' ][ '_company_name' ] = $customer_obj->get_meta('_company_name', true);
                }
                unset($customer_obj);
            }
        }

        /**
         * Billing Address Details.
         *
         * Allows filtering the customer address details that will be pre-populated on the checkout form.
         *
         * @since 2.8
         *
         * @param array $address The customer address.
         * @param array $customer The customer data from the session
         */
        $customer['address'] = apply_filters( 'edd_checkout_billing_details_address', $customer['address'], $customer );
        

        ob_start(); ?>
        <fieldset id="edd_cc_address" class="cc-address">
            <legend><?php _e( 'Billing Information', 'easy-digital-downloads' ); ?></legend>
            <?php do_action( 'edd_cc_billing_top' ); ?>
            <p id="edd-card-company-name-wrap">
                <label for="card_company_name" class="edd-label">
                    <?php _e( 'Company Name', 'easy-digital-downloads' ); ?>
                    <?php if( edd_field_is_required( 'card_company_name' ) ) { ?>
                        <span class="edd-required-indicator">*</span>
                    <?php } ?>
                </label>
                <input type="text" id="card_company_name" name="card_company_name" class="card-company-name edd-input<?php if( edd_field_is_required( 'card_company_name' ) ) { echo ' required'; } ?>" value="<?php echo $customer['address']['_company_name']; ?>"<?php if( edd_field_is_required( 'card_company_name' ) ) {  echo ' required '; } ?>/>
            </p>
            <p id="edd-card-address-wrap">
                <label for="card_address" class="edd-label">
                    <?php _e( 'Billing Address', 'easy-digital-downloads' ); ?>
                    <?php if( edd_field_is_required( 'card_address' ) ) { ?>
                        <span class="edd-required-indicator">*</span>
                    <?php } ?>
                </label>
                <textarea id="card_address" name="card_address" class="card-address edd-input<?php if( edd_field_is_required( 'card_address' ) ) { echo ' required'; } ?>" <?php if( edd_field_is_required( 'card_address' ) ) {  echo ' required '; } ?> rows="4"><?php echo $customer['address']['line1']; ?></textarea>
            </p>
            <p id="edd-card-country-wrap">
                <label for="billing_country" class="edd-label">
                    <?php _e( 'Country', 'easy-digital-downloads' ); ?>
                    <?php if( edd_field_is_required( 'billing_country' ) ) { ?>
                        <span class="edd-required-indicator">*</span>
                    <?php } ?>
                </label>
                <select name="billing_country" id="billing_country" data-nonce="<?php echo wp_create_nonce( 'edd-country-field-nonce' ); ?>" class="billing_country edd-select<?php if( edd_field_is_required( 'billing_country' ) ) { echo ' required'; } ?>"<?php if( edd_field_is_required( 'billing_country' ) ) {  echo ' required '; } ?>>
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
            <p id="edd-card-state-wrap">
                <label for="card_state" class="edd-label">
                    <?php _e( 'State / Province', 'easy-digital-downloads' ); ?>
                    <?php if( edd_field_is_required( 'card_state' ) ) { ?>
                        <span class="edd-required-indicator">*</span>
                    <?php } ?>
                </label>
                <?php
                $selected_state = edd_get_shop_state();
                $states         = edd_get_shop_states( $selected_country );

                if( ! empty( $customer['address']['state'] ) ) {
                    $selected_state = $customer['address']['state'];
                }

                if( ! empty( $states ) ) : ?>
                <select name="card_state" id="card_state" class="card_state edd-select<?php if( edd_field_is_required( 'card_state' ) ) { echo ' required'; } ?>">
                    <?php
                        foreach( $states as $state_code => $state ) {
                            echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
                        }
                    ?>
                </select>
                <?php else : ?>
                <?php $customer_state = ! empty( $customer['address']['state'] ) ? $customer['address']['state'] : ''; ?>
                <input type="text" size="6" name="card_state" id="card_state" class="card_state edd-input" value="<?php echo esc_attr( $customer_state ); ?>"/>
                <?php endif; ?>
            </p>
            <p id="edd-card-city-wrap">
                <label for="card_city" class="edd-label">
                    <?php _e( 'Billing City', 'easy-digital-downloads' ); ?>
                    <?php if( edd_field_is_required( 'card_city' ) ) { ?>
                        <span class="edd-required-indicator">*</span>
                    <?php } ?>
                </label>
                <input type="text" id="card_city" name="card_city" class="card-city edd-input<?php if( edd_field_is_required( 'card_city' ) ) { echo ' required'; } ?>" value="<?php echo $customer['address']['city']; ?>"<?php if( edd_field_is_required( 'card_city' ) ) {  echo ' required '; } ?>/>
            </p>
            <p id="edd-card-zip-wrap">
                <label for="card_zip" class="edd-label">
                    <?php _e( 'Zip Code', 'easy-digital-downloads' ); ?>
                    <?php if( edd_field_is_required( 'card_zip' ) ) { ?>
                        <span class="edd-required-indicator">*</span>
                    <?php } ?>
                </label>
                <input type="text" size="4" id="card_zip" name="card_zip" class="card-zip edd-input<?php if( edd_field_is_required( 'card_zip' ) ) { echo ' required'; } ?>" value="<?php echo $customer['address']['zip']; ?>"<?php if( edd_field_is_required( 'card_zip' ) ) {  echo ' required '; } ?>/>
            </p>
            
            <?php do_action( 'edd_cc_billing_bottom' ); ?>
            <?php wp_nonce_field( 'edd-checkout-address-fields', 'edd-checkout-address-fields-nonce', false, true ); ?>
        </fieldset>
        <fieldset id="edd_order_additional" class="edd-order-additional">
            <legend><?php _e( 'Additional Information', 'easy-digital-downloads' ); ?></legend>
            <p id="edd-order-additional-info-wrap">
                <label for="order_additional" class="edd-label">
                    <?php _e( 'Order Notes', 'easy-digital-downloads' ); ?>
                    <?php if( edd_field_is_required( 'order_additional' ) ) { ?>
                        <span class="edd-required-indicator">*</span>
                    <?php } ?>
                </label>
                <textarea id="order_additional" name="order_additional" class="order-additional edd-input<?php if( edd_field_is_required( 'order_additional' ) ) { echo ' required'; } ?>" <?php if( edd_field_is_required( 'order_additional' ) ) {  echo ' required '; } ?> rows="4"></textarea>
            </p>
            <?php do_action( 'edd_order_additional_information_bottom' ); ?>
        </fieldset>
        <?php
        echo ob_get_clean();
    }
    /**
	 * Shows the User Info fields in the Personal Info box, more fields can be added
	 * via the hooks provided.
	 *
	 * @since 1.3.3
	 * @return void
	 */
	public function edd_user_info_fields() {
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
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
        $customer_obj = new EDD_Customer( $user_data->user_email );
        if(!empty($customer_obj)){
            if($customer_obj->get_meta('_phone_number', true)){
                $customer[ '_phone_number' ] = $customer_obj->get_meta('_phone_number', true);
            }
            unset($customer_obj);
        }
		?>
        <fieldset id="edd_checkout_user_login" style="display:none">
            <legend>
				<span class="wdm-box-heading text-left">Account login</span>
				<span class="wdm-login-toggler text-right"> Need to create an account?<a id="wdm_login_tab" href="javascript:void(0);" target="_self"> Register </a> </span>
            </legend>
            <?php echo do_shortcode( '[theme-my-login action="login" login_template="wdm-minimal-login-form.php"]' );?>
		</fieldset>
		<fieldset id="edd_checkout_user_info" style="<?php echo is_user_logged_in()?'display:none':''?>">
			<legend><?php echo apply_filters( 'edd_checkout_personal_info_text', esc_html__( 'Create account to complete your purchase', 'easy-digital-downloads' ) ); ?></legend>
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
            <p id="edd-first-name-wrap">
				<label class="edd-label" for="edd-first">
					<?php esc_html_e( 'First Name', 'easy-digital-downloads' ); ?>
					<?php if( edd_field_is_required( 'edd_first' ) ) { ?>
						<span class="edd-required-indicator">*</span>
					<?php } ?>
				</label>
				<input class="edd-input required" type="text" name="edd_first" id="edd-first" value="<?php echo esc_attr( $customer['first_name'] ); ?>"<?php if( edd_field_is_required( 'edd_first' ) ) {  echo ' required '; } ?> aria-describedby="edd-first-description" />
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
        <fieldset id="edd_checkout_form_country">
            <p id="edd-country-wrap">
                <label class="edd-label" for="edd_cust_country">
                    <?php esc_html_e( 'Country', 'easy-digital-downloads' ); ?>
                    <span class="edd-required-indicator">*</span>
                </label>
                <select style="width:100%"  name="edd_cust_country" id="edd_cust_country" data-nonce="<?php echo wp_create_nonce( 'edd-country-field-nonce' ); ?>" class="billing_country edd-select required" required>
                    <?php

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
            <p id="edd-state-wrap" style="display:none;">
                <label class="edd-label" for="edd_cust_state">
                    <?php esc_html_e( 'State', 'easy-digital-downloads' ); ?>
                    <span class="edd-required-indicator">*</span>
                </label>
                <?php
                    $selected_state = "";
                    $states         = edd_get_shop_states( "US" );

                    if( ! empty( $customer['address']['state'] ) ) {
                        $selected_state = $customer['address']['state'];
                    }

                ?>
                <select style="width:100%" name="edd_cust_state" id="edd_cust_state" class="card_state edd-select required" >
                    <?php
                        foreach( $states as $state_code => $state ) {
                            echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
                        }
                    ?>
                </select>
            </p>
        </fieldset>
		<?php
    }
    
    /**
     * get_cart_table_details To generate cart table details
     *
     * @param  mixed $cart_item
     * @return void
     */
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
    
    /**
     * renewal_notice To show renewal notice in case of renewal purchases
     *
     * @param  mixed $item
     * @return void
     */
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

    public function get_license_options_html($options=array(),$item_key=0){
		$html = '';
		if($options){
			foreach ($options as $key => $value) {
				$radio_image = !empty($value['selected'])?WDMELE_PLUGIN_URL.'assets/images/selected_radio.svg':WDMELE_PLUGIN_URL.'assets/images/radio.svg';
				$html .= '<div class="license_options'.(!empty($value['selected'])?' license_options_checked':'').'"><span class="license_options_radio"><img class="radio_button_img" src="'.$radio_image.'"><input style="display:none" data-nonce="'.$this->cart_nonce.'" type="radio" name="license_options_'.$item_key.'" value="'.$value['value'].'" '. (!empty($value['selected'])?'checked="true"':'') .'></span> <span class="license_options_quantity">'.$value['label'].'</span></div>';
			}
		}
		return $html;
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
					$output[$i]['label'] = ($added_download_price_license_type=='business'?($activation_limit/2). ' Business':$activation_limit) . ' License';
				}
				$i++;
			}
		}else{
			$output[$i]['value'] = $added_download->ID;
			$activation_limit = $added_download->get_activation_limit();
			if(!empty($activation_limit)){
				$output[$i]['label'] = ($added_download_price_license_type=='business'?($activation_limit/2). ' Business':$activation_limit) . ' License';
			}
			$output[$i]['selected'] = 1;
		}
        if(count($output)<=1){
            return '';
        }
		return $output;
	}
    
    /**
     * get_period To get purchase period details
     *
     * @param  mixed $cart_item
     * @return void
     */
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
    
    /**
     * get_product_title Downloads product title
     *
     * @param  mixed $download_id
     * @return void
     */
    public function get_product_title($download_id=0){
		$title = '';
		if($download_id){
			$title = get_the_title( $download_id );
		}
		return $title;
	}

    public function edd_require_billing_address(){
        // if(!$this->is_elementor_checkout()){
        //     return $required_fields;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget/*  || !$this->is_elementor_checkout() */){
            return;
        }
        
        return false;
    }

    public function wdm_edd_after_checkout_cart(){
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
        remove_action( 'edd_after_checkout_cart', 'wdm_edd_after_checkout_cart', 10 );
        return;
    }

    public function edd_purchase_form_required_fields($required_fields){
        // if(!$this->is_elementor_checkout()){
        //     return $required_fields;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget/*  || !$this->is_elementor_checkout() */){
            return $required_fields;
        }
        // $is_checkout_active_widget = $this->is_widget_active('wdmcartloginregister');
        // if(!$is_checkout_active_widget){
            //     return $required_fields;
            // }
            // $required_fields['phone_number'] = array(
                // 	'error_id' => 'invalid_phone_number',
                // 	'error_message' => __( 'Please enter your phone number', 'easy-digital-downloads' )
                // );
        $require_address = apply_filters( 'edd_require_billing_address', edd_use_taxes() && edd_get_cart_total() );
        if ( $require_address ) {
            $required_fields['card_company_name'] = array(
                'error_id' => 'invalid_company_name',
                'error_message' => __( 'Please enter your company name', 'easy-digital-downloads' )
            );
        }
        
        // if(!empty($required_fields['card_city'])){
        //     unset($required_fields['card_city']);
        // }
		return $required_fields;
    }

    public function edds_credit_card_form($echo = true){
        // if(!$this->is_elementor_checkout()){
        //     return;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return false;
        }
        // $is_checkout_active_widget = $this->is_widget_active('wdmcartloginregister');
        // if(!$is_checkout_active_widget){
        //     return;
        // }
        remove_action('edd_stripe_cc_form', 'wdm_edds_credit_card_form', 10);
        global $edd_options;

        if (edd_stripe()->rate_limiting->has_hit_card_error_limit()) {
            edd_set_error('edd_stripe_error_limit', __('We are unable to process your payment at this time, please try again later or contact support.', 'edds'));
            return;
        }

        ob_start(); ?>

        <?php if (! wp_script_is('edd-stripe-js')) : ?>
            <?php edd_stripe_js(true); ?>
        <?php endif; ?>

        <?php do_action('edd_before_cc_fields'); ?>

        <fieldset id="edd_cc_fields" class="edd-do-validate">
            <legend><?php _e('Enter Card Details', 'edds'); ?></legend>
            <?php if (0/* is_ssl() */) : ?>
                <div id="edd_secure_site_wrapper">
                    <span class="padlock">
                        <svg class="edd-icon edd-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
                            <path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z"/>
                        </svg>
                    </span>
                    <span><?php _e('This is a secure SSL encrypted payment.', 'edds'); ?></span>
                </div>
            <?php endif; ?>

            <?php
            $existing_cards = edd_stripe_get_existing_cards(get_current_user_id());
            ?>
            <?php if (! empty($existing_cards)) {
                edd_stripe_existing_card_field_radio(get_current_user_id());
            } ?>

            <div class="edd-stripe-new-card" <?php if (! empty($existing_cards)) {
                echo 'style="display: none;"';
                                            } ?>>
                <?php do_action('edd_stripe_new_card_form'); ?>
                <?php do_action('edd_after_cc_expiration'); ?>
            </div>

        </fieldset>
        <?php

        do_action('edd_after_cc_fields');

        $form = ob_get_clean();

        if (false !== $echo) {
            echo $form;
        }

        return $form;
    }

    public function edd_stripe_new_card_form(){
        // if(!$this->is_elementor_checkout()){
        //     return;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
        remove_action( 'edd_stripe_new_card_form', 'wdm_edd_stripe_new_card_form', 10 );

        if ( edd_stripe()->rate_limiting->has_hit_card_error_limit() ) {
            edd_set_error( 'edd_stripe_error_limit', __( 'Adding new payment methods is currently unavailable.', 'edds' ) );
            edd_print_errors();
            return;
        }
    ?>
    <div id="edd_secure_site_wrapper">
        <span class="padlock">
                            <svg class="edd-icon edd-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
                <path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z"></path>
            </svg>
                            </span>
        <span class="secure-message">This is a secure SSL encrypted payment.</span>
    </div>
    <p id="edd-card-name-wrap">
        <label for="card_name" class="edd-label">
            <?php _e( 'Name on Credit Card', 'edds' ); ?>
            <span class="edd-required-indicator">*</span>
        </label>
        <input type="text" name="card_name" id="card_name" class="card-name edd-input required" autocomplete="cc-name" />
    </p>

    <div id="edd-card-wrap">
        <label for="edd-card-element" class="edd-label">
            <?php _e( 'Credit Card', 'edds' ); ?>
            <span class="edd-required-indicator">*</span>
        </label>

        <div id="edd-stripe-card-element"></div>
        <div id="edd-stripe-card-errors" role="alert"></div>

        <p></p><!-- Extra spacing -->
    </div>

    <?php
        /**
         * Allow output of extra content before the credit card expiration field.
         *
         * This content no longer appears before the credit card expiration field
         * with the introduction of Stripe Elements.
         *
         * @deprecated 2.7
         * @since unknown
         */
        do_action( 'edd_before_cc_expiration' );

    }

    public function edd_stripe_js_vars( $options ){
        // echo '<pre>';
		// print_r($options['elementsOptions']);
		// echo '</pre>';
        // die('Testing');
		return $options;
    }
    
    public function edd_get_checkout_button_purchase_label( $complete_purchase, $label ){
        // if(!$this->is_elementor_checkout()){
        //     return $complete_purchase;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
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

    public function edd_checkout_before_gateway( $post, $user_info, $valid_data ){
        if(isset($_POST['phone_number'])){
            $customer = new EDD_Customer( $user_info['email'] );
            if(empty($customer)){
                return;
            }
            if(empty($_POST['phone_number'])){
                $customer->delete_meta( '_phone_number' );
            }else{
                $customer->update_meta( '_phone_number', $_POST['phone_number'] );
            }
            unset($customer);
        }
        if(isset($_POST['card_company_name'])){
            $customer = new EDD_Customer( $user_info['email'] );
            if(empty($customer)){
                return;
            }
            if(empty($_POST['card_company_name'])){
                $customer->delete_meta( '_company_name' );
            }else{
                $customer->update_meta( '_company_name', $_POST['card_company_name'] );
            }
            unset($customer);
        }
    }

    public function edd_customer_before_stats( $customer ){
        if($customer->get_meta('_phone_number', true)){
            echo '<div style="border-bottom: 1px solid #eee;padding-bottom: 2%;"><div class="customer-phone-number">Phone: '.$customer->get_meta('_phone_number', true).'</div></div>';
        }
        if($customer->get_meta('_company_name', true)){
            echo '<div style="border-bottom: 1px solid #eee;padding-bottom: 2%;"><div class="customer-company-name">Company Name: '.$customer->get_meta('_company_name', true).'</div></div>';
        }
    }

    public function edd_payment_order_notes_meta( $payment_meta ) {

        if ( 0 !== did_action('edd_pre_process_purchase') ) {
            $payment_meta['_order_additional'] = isset( $_POST['order_additional'] ) ? sanitize_text_field( $_POST['order_additional'] ) : '';
        }
        return $payment_meta;
    }

    public function edd_payment_personal_details_list( $payment_meta, $user_info ){
        $order_additional = isset( $payment_meta['_order_additional'] ) ? $payment_meta['_order_additional'] : '';
        ?>
            <div class="column-container">
                <div class="column">
                    <strong>Order Notes: </strong>
                    <?php echo $order_additional; ?>
                </div>
            </div>
        <?php
    }

    public function edd_view_order_details_billing_after( $payment_id ){
        $payment    = new EDD_Payment( $payment_id );
        $payment_meta_order_additional   = $payment->get_meta('_order_additional',true);
        ?>
            <div id="edd-payment-notes" class="postbox">
                <h3 class="hndle"><span><?php _e( 'Order Notes', 'easy-digital-downloads' ); ?></span></h3>
                <div class="inside">
                    <div id="edd-payment-additional-notes-inner">
                        Additional Order Notes
                    </div>
                    <p>
                        <?php echo $payment_meta_order_additional?>
                    </p>
                    <div class="clear"></div>
                </div><!-- /.inside -->
            </div><!-- /#edd-payment-notes -->
        <?php
        unset($payment);
    }
    
    public function show_more_options($item){
		$random_string = rand(1,10);
        $edd_var = edd_get_variable_prices($item['id']);
        $dropdown_options = $radio_options = '';
        $nonce = wp_create_nonce('wdm-more-options-checkout-nonce');
        if (!empty($edd_var[$item['options']['price_id']])) {
            $current_option = $edd_var[$item['options']['price_id']];
            unset($edd_var[$item['options']['price_id']]);
            $cur_is_single = strpos(strtolower($current_option['name']), 'single') !== false;
            $cur_is_business = strpos(strtolower($current_option['name']), 'business') !== false;
            foreach ($edd_var as $price_id => $var_option) {
                $var_option_name = strtolower($var_option['name']);
                $limit = '';
                if ((!empty($var_option['recurring']) && $current_option['recurring']==$var_option['recurring']) || (!empty($var_option['is_lifetime']) && $current_option['is_lifetime']==$var_option['is_lifetime'])) {
                    if ($cur_is_business && strpos($var_option_name, 'business') !== false) {
                        $limit = ((int)$var_option['license_limit']/2) . ' Business License (Staging+Production)';
                    } elseif ($cur_is_single && strpos($var_option_name, 'single') !== false) {
                        $limit = $var_option['license_limit'] . ' Single Site License';
                    }
                    if ($limit) {
                        $radio_options .= '<input data-nonce="'.$nonce.'" data-parent-option-id="'.$item['options']['price_id'].'" data-option-id="'.$price_id.'" data-download-id="'.$item['id'].'" type="radio" id="'.$item['id'].'_'.$price_id.'" class="selected_more_options" name="selected_more_options_'.$item['id'].'_'.$random_string.'" value="'.$item['id'].'_'.$price_id.'"><label for="'.$item['id'].'_'.$price_id.'">'.$limit.'</label><br>';
                        // $dropdown_options .= '<option data-nonce="'.$nonce.'" data-parent-option-id="'.$item['options']['price_id'].'" data-option-id="'.$price_id.'" data-download-id="'.$item['id'].'" value="'.$item['id'].'_'.$price_id.'">'.$limit.'</option>';
                    }
                }
            }
        }
        if (!empty($current_option)) {
            $limit = $current_option['license_limit'];
            if (strpos(strtolower($current_option['name']), 'business') !== false) {
                $limit = ((int)$current_option['license_limit']/2) . ' Business License (Staging+Production)';
            } elseif (strpos(strtolower($current_option['name']), 'single') !== false) {
                $limit = $current_option['license_limit'] . ' Single Site License';
            } else {
                $limit = $current_option['name'];
            }
            // $item = '<select name="selected_more_options"><option disabled="disabled" value="" selected>'.$limit.'</option>'.$dropdown_options.'</select>';
            $item = '<input type="radio" id="selected_option_id_'.$item['id'].'" class="selected_more_options" name="selected_more_options_'.$item['id'].'_'.$random_string.'" value="" checked="checked"><label for="selected_option_id">'.$limit.'</label><br>'.$radio_options;
        } else {
            $item = '';
        }
		
		unset($edd_var);
        unset($current_option);
        return $item;
    }

    /**
     * Renders the payment mode form by getting all the enabled payment gateways and
     * outputting them as radio buttons for the user to choose the payment gateway. If
     * a default payment gateway has been chosen from the EDD Settings, it will be
     * automatically selected.
     *
     * @since 1.2.2
     * @return void
     */
    function edd_payment_mode_select() {
        // if(!$this->is_elementor_checkout()){
        //     return;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
        remove_action( 'edd_payment_mode_select', 'edd_payment_mode_select', 10 );

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
                    <legend><?php _e( 'Select Payment Method Testing', 'easy-digital-downloads' ); ?></legend>
                    <?php do_action( 'edd_payment_mode_before_gateways_wrap' ); ?>
                    <div id="edd-payment-mode-wrap">
                        <?php

                        do_action( 'edd_payment_mode_before_gateways' );

                        foreach ( $gateways as $gateway_id => $gateway ) :

                            $label         = apply_filters( 'edd_gateway_checkout_label_' . $gateway_id, $gateway['checkout_label'] );
                            $checked       = checked( $gateway_id, $chosen_gateway, false );
                            $checked_class = $checked ? ' edd-gateway-option-selected' : '';
                            $nonce         = ' data-' . esc_attr( $gateway_id ) . '-nonce="' . wp_create_nonce( 'edd-gateway-selected-' . esc_attr( $gateway_id ) ) .'"';

                            echo '<label for="edd-gateway-' . esc_attr( $gateway_id ) . '" class="edd-gateway-option' . $checked_class . '" id="edd-gateway-option-' . esc_attr( $gateway_id ) . '">';
                                echo '<span class="radio-span"></span><input type="radio" name="payment-mode" class="edd-gateway" id="edd-gateway-' . esc_attr( $gateway_id ) . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . $nonce . '>' . esc_html( $label );
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

    public function is_widget_active($widget_name=''){
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

    public function wdm_checkout_terms()
	{
        // if(!$this->is_elementor_checkout()){
        //     return;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
		// printf('<p style="font-size:small;">'.__('Checking the below checkbox confirms that you have read all plugin documentation related to your purchase and it also confirms that you agree to the Terms & Conditions mentioned and comply with it. Terms are subject to change without any prior notification.', 'wisdmlabs').'</p>');
		remove_action('edd_purchase_form_before_submit', 'wdm_product_terms', 9);
		remove_action( 'edd_purchase_form_before_submit', 'edd_privacy_agreement', 10 );
		$show_privacy_policy_checkbox = edd_get_option( 'show_agree_to_privacy_policy', false );
		$show_privacy_policy_text     = edd_get_option( 'show_privacy_policy_on_checkout', false );
        /**
		 * Privacy Policy output has dual functionality, unlike Agree to Terms output:
		 *
		 * 1. A checkbox (and associated label) can show on checkout if the 'Agree to Privacy Policy' setting
		 *    is checked. This is because a Privacy Policy can be agreed upon without displaying the policy
		 *    itself. Keep in mind the label field supports anchor tags, so the policy can be linked to.
		 *
		 * 2. The Privacy Policy text, which is post_content pulled from the WP core Privacy Policy page when
		 *    you have the 'Show the Privacy Policy on checkout' setting checked, can be displayed on checkout
		 *    regardless of whether or not the customer has to explicitly agreed to the policy by checking the
		 *    checkbox from point #1 above.
		 *
		 * Because these two display options work independently, having either setting checked triggers output.
		 */
		if ( '1' === $show_privacy_policy_checkbox || '1' === $show_privacy_policy_text ) {

			$agree_label  = edd_get_option( 'privacy_agree_label', __( 'Agree to Privacy Policy?', 'easy-digital-downloads' ) );
			$privacy_page = get_option( 'wp_page_for_privacy_policy' );
			$privacy_text = get_post_field( 'post_content', $privacy_page );

			ob_start();
			?>

			<fieldset id="edd-privacy-policy-agreement">

				<?php
				// Show Privacy Policy text if the setting is checked, the WP Privacy Page is set, and content exists.
				if ( '1' === $show_privacy_policy_text && ( $privacy_page && ! empty( $privacy_text ) ) ) {
					?>
					<div id="edd-privacy-policy" class="edd-terms" style="display:none;">
						<?php
						do_action( 'edd_before_privacy_policy' );
						echo wpautop( do_shortcode( stripslashes( $privacy_text ) ) );
						do_action( 'edd_after_privacy_policy' );
						?>
					</div>
                    <?php
				}

				// Show Privacy Policy checkbox and label if the setting is checked.
				if ( '1' === $show_privacy_policy_checkbox ) {
					?>
					<div class="edd-privacy-policy-agreement">
						<label class="checkbox-container" for="edd-agree-to-privacy-policy"><?php echo stripslashes( $agree_label ); ?><input name="edd_agree_to_privacy_policy" class="required" type="checkbox" id="edd-agree-to-privacy-policy" value="1"/><span class="checkmark"></span></label>
					</div>
					<?php
				}
				?>

			</fieldset>

			<?php
			$html_output = ob_get_clean();

			echo apply_filters( 'edd_checkout_privacy_policy_agreement_html', $html_output );
		}
	}

    public function wdm_edd_checkout_login_fields_after(){
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
        // if(!$this->is_elementor_checkout()){
        //     return;
        // }
        remove_action('edd_checkout_login_fields_before', 'wdm_edd_checkout_login_fields_after', 1);
        remove_action('edd_register_fields_before', 'wdm_edd_checkout_login_fields_after', 1);
    }

    public function wdm_edd_show_purchase_form(){

        // if(!$this->temp_is_elementor_checkout()){
        //     return;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
        remove_action( 'edd_purchase_form', 'wdm_edd_show_purchase_form', 10 );

        $payment_mode = edd_get_chosen_gateway();

        /**
         * Hooks in at the top of the purchase form
         *
         * @since 1.4
         */
        do_action('edd_purchase_form_top');

        if (edd_can_checkout()) {
            do_action('edd_purchase_form_before_register_login');

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

    public function wdm_edd_get_register_fields(){
        // if(!$this->is_elementor_checkout()){
        //     return;
        // }
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
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
                    <!-- <p id="edd-user-pass-wrap"> -->
                        <!-- <label for="edd_user_pass"> -->
                            <!-- <?php //_e('Password', 'easy-digital-downloads'); ?> -->
                        <!-- </label> -->
                        <!-- <span class="edd-description"><?php //_e('The password used to access your account.', 'easy-digital-downloads'); ?></span> -->
                        <!-- <input name="edd_user_pass" id="edd_user_pass" class="edd-input" placeholder="<?php //_e('Password', 'easy-digital-downloads'); ?>" type="password"/> -->
                    <!-- </p> -->
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

                <!-- <fieldset id="edd_register_account_fields"> -->
                    <!-- <legend> -->
                        <?php
                    // _e('Create an account', 'easy-digital-downloads');
                    ?>
                    <!-- </legend> -->
                    <?php do_action('edd_register_account_fields_before'); ?>
                    <!-- <p id="edd-user-login-wrap" style="display:none;"> -->
                        <!-- <label for="edd_user_login"> -->
                            <?php //_e('Username', 'easy-digital-downloads'); ?>
                            <!-- <span class="edd-required-indicator">*</span> -->
                        <!-- </label> -->
                        <!-- <input name="edd_user_login" id="edd_user_login" class="required edd-input" type="text"/> -->
                    <!-- </p> -->
                    <!-- <p id="edd-email-wrap"> -->
                        <!-- <label for="edd_email"> -->
                            <!-- <?php //_e('Email', 'easy-digital-downloads'); ?> -->
                            <!-- <span class="edd-required-indicator">*</span> -->
                        <!-- </label> -->
                        <!-- <input name="edd_email" id="edd_email" class="required edd-input" type="text"/> -->
                    <!-- </p> -->
                    <!-- <p id="edd-user-pass-wrap">
                        <label for="edd_user_pass">
                            <?php //_e('Password', 'easy-digital-downloads'); ?>
                            <span class="edd-required-indicator">*</span>
                        </label>
                        <input name="edd_user_pass" id="edd_user_pass" class="required edd-input" type="password"/>
                    </p> -->
                    <!-- <p id="edd-user-pass-confirm-wrap" class="edd_register_password"> -->
                        <!-- <label for="edd_user_pass_confirm"> -->
                            <?php //_e('Password', 'easy-digital-downloads'); ?>
                            <!-- <span class="edd-required-indicator">*</span> -->
                        <!-- </label> -->
                        <!-- <input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="required edd-input" type="password"/> -->
                    <!-- </p> -->
                    <?php do_action('edd_register_account_fields_after'); ?>
                    <?php
                    // if ($show_register_form == 'both') {
                        ?>
                        <!-- <p id="edd-login-account-wrap"> -->
                            <?php //_e('Already have an account?', 'easy-digital-downloads'); ?>
                             <!-- <a href="<?php //echo esc_url(add_query_arg('login', 1)); ?>" class="edd_checkout_register_login" data-action="checkout_login" data-nonce="<?php echo wp_create_nonce('edd_checkout_login'); ?>"> -->
                                    <!-- <?php //_e('Login', 'easy-digital-downloads'); ?> -->
                             <!-- </a> -->
                        <!-- </p> -->
                        <?php
                    // } ?>
                </fieldset>
                <?php do_action('edd_register_fields_after'); ?>

                <input type="hidden" name="edd-purchase-var" value="needs-to-register"/>

                <?php do_action('edd_purchase_form_user_info'); ?>
                <?php do_action('edd_purchase_form_user_register_fields'); ?>

            </fieldset>
            <?php
        }
        echo ob_get_clean();
    
    }

    public function is_elementor_checkout(){
        if( trailingslashit(self::$set_checkout)==trailingslashit(self::$current_url) ){
            return 1;
        }
        return 0;
    }
    public function temp_is_elementor_checkout(){
        // echo trailingslashit($set_checkout);
        // echo trailingslashit($current_url);
        if(wp_doing_ajax()){
            echo '<pre>';
            print_r(self::$set_checkout);
            echo '</pre>';
            echo '<pre>';
            print_r(self::$current_url);
            echo '</pre>';
        } 
        if( trailingslashit(self::$set_checkout)==trailingslashit(self::$current_url) ){
            return 1;
        }
        return 0;
    }

    public function login_redirect( $redirect_to, $request, $user ){
        
        global $wp;
        $current_url = home_url( $wp->request );
        if(function_exists('get_field')){
            if(!empty(get_field('wisdm_minimal_elementor_checkout_page','option'))){
                $set_checkout = get_field('wisdm_minimal_elementor_checkout_page','option');
            }
        }
        
        if( trailingslashit($set_checkout)!==trailingslashit($current_url) ){
            return $redirect_to;
        }
        
        if( class_exists('Theme_My_Login_Custom_Redirection') ){
            remove_filter( 'login_redirect',  array( Theme_My_Login_Custom_Redirection::get_object(), 'login_redirect' ), 10, 3 );
            return $set_checkout;
        }
        return $redirect_to;
    }
    
    public function wisdmlabs_enqueue_custom_for_super_socializer(){
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
        // if(!$this->is_elementor_checkout()){
        //     return;
        // }
        remove_action( 'wp_footer', 'wisdmlabs_enqueue_custom_for_super_socializer', 10 );
        wp_dequeue_script( 'wdm-edd-checkout' );
    }

    public function add_contact_us_item_to_nav_menu( $items, $args ) {
        if($this->is_elementor_checkout()){
            if ($args->menu == 531) {
                $items = '<li id="menu-item-351954" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-351954"><a href="/contact-us/" itemprop="url">Contact Us</a></li>'.$items; 
            }
        }
        return $items;
    }

    /**
     * wdm_edd_stripe_new_card_form
     *
     * @return void
     */
    public function wdm_edd_stripe_new_card_form()
    {
        $is_checkout_active_widget = $this->is_widget_active('wdmminimalcheckout');
        if(!$is_checkout_active_widget){
            return;
        }
        remove_action( 'edd_stripe_new_card_form', 'wdm_edd_stripe_new_card_form', 10 );
        if (edd_stripe()->rate_limiting->has_hit_card_error_limit()) {
            edd_set_error('edd_stripe_error_limit', __('Adding new payment methods is currently unavailable.', 'edds'));
            edd_print_errors();
            return;
        }
        ?>
    <div id="edd_secure_site_wrapper">
        <span class="padlock">
                            <svg class="edd-icon edd-icon-lock" xmlns="http://www.w3.org/2000/svg" width="18" height="28" viewBox="0 0 18 28" aria-hidden="true">
                <path d="M5 12h8V9c0-2.203-1.797-4-4-4S5 6.797 5 9v3zm13 1.5v9c0 .828-.672 1.5-1.5 1.5h-15C.672 24 0 23.328 0 22.5v-9c0-.828.672-1.5 1.5-1.5H2V9c0-3.844 3.156-7 7-7s7 3.156 7 7v3h.5c.828 0 1.5.672 1.5 1.5z"></path>
            </svg>
                            </span>
        <span>This is a secure SSL encrypted payment.</span>
    </div>
    <p id="edd-card-name-wrap">
        <label for="card_name" class="edd-label">
            <?php _e('Name on the Card', 'edds'); ?>
            <span class="edd-required-indicator">*</span>
        </label>
        <!-- <span class="edd-description"><?php //_e('The name printed on the front of your credit / debit card.', 'edds'); ?></span> -->
        <input type="text" name="card_name" id="card_name" class="card-name edd-input required" autocomplete="cc-name" />
    </p>

    <div id="edd-card-wrap">
        <label for="edd-card-element" class="edd-label">
            <?php _e('Credit / Debit Card', 'edds'); ?>
            <span class="edd-required-indicator">*</span>
        </label>

        <div id="edd-stripe-card-element"></div>
        <div id="edd-stripe-card-errors" role="alert"></div>

        <p></p><!-- Extra spacing -->
    </div>

        <?php
        /**
         * Allow output of extra content before the credit card expiration field.
         *
         * This content no longer appears before the credit card expiration field
         * with the introduction of Stripe Elements.
         *
         * @deprecated 2.7
         * @since unknown
         */
        do_action('edd_before_cc_expiration');
    }
    

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            // global $wp;
            self::$instance = new WdmElementorMinimalUtility;
            // self::$current_url = home_url( $wp->request );
            // if(function_exists('get_field')){
            //     if(!empty(get_field('wisdm_minimal_elementor_checkout_page','option'))){
            //         self::$set_checkout = get_field('wisdm_minimal_elementor_checkout_page','option');
            //     }
            // }
        }
        return self::$instance;
    }
}