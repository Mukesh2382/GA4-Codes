<?php
// use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * WdmCheckoutLogin to create a new custom widget
 */
class WdmCheckoutLogin extends \Elementor\Widget_Base {
	public $login_register_nonce;
	public $cart_nonce;
	public static $edd_payment_mode_select_called = 0;

	public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);

		$this->cart_nonce = wp_create_nonce( 'wdm-elem-cart' );
		$this->login_register_nonce = wp_create_nonce( 'wdm-elem-register' );
		$in_footer = edd_scripts_in_footer();		
		wp_deregister_script( 'edd-ajax' );
		wp_register_script( 'edd-ajax', WDMELE_PLUGIN_URL.'assets/js/edd-ajax.js', array( 'jquery', 'wdm-script-handle-login' ), EDD_VERSION, $in_footer );
		// wp_register_script( 'edd-ajax', WDMELE_PLUGIN_URL.'assets/js/edd-ajax.js', array( 'jquery', 'wdm-script-handle-login' ), NULL, true );
		wp_localize_script( 'edd-ajax', 'edd_scripts', apply_filters( 'edd_ajax_script_vars', array(
			'ajaxurl'                 => edd_get_ajax_url(),
			'position_in_cart'        => isset( $position ) ? $position : -1,
			'has_purchase_links'      => $has_purchase_links,
			'already_in_cart_message' => __('You have already added this item to your cart','easy-digital-downloads' ), // Item already in the cart message
			'empty_cart_message'      => __('Your cart is empty','easy-digital-downloads' ), // Item already in the cart message
			'loading'                 => __('Loading','easy-digital-downloads' ) , // General loading message
			'select_option'           => __('Please select an option','easy-digital-downloads' ) , // Variable pricing error with multi-purchase option enabled
			'is_checkout'             => edd_is_checkout() ? '1' : '0',
			'default_gateway'         => edd_get_default_gateway(),
			'redirect_to_checkout'    => ( edd_straight_to_checkout() || edd_is_checkout() ) ? '1' : '0',
			'checkout_page'           => edd_get_checkout_uri(),
			'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0',
			'quantities_enabled'      => edd_item_quantities_enabled(),
			'taxes_enabled'           => edd_use_taxes() ? '1' : '0', // Adding here for widget, but leaving in checkout vars for backcompat
		) ) );

		wp_register_script( 'wdm-script-handle-login', WDMELE_PLUGIN_URL.'assets/js/checkout.js', [ 'elementor-frontend' ], '1.0.0', true );
		wp_localize_script( 'wdm-script-handle-login', 'wdmCheckoutAjax', array( 'decimal_separator' => edd_get_option( 'decimal_separator', '.' ),'currency_sign'=> edd_currency_filter(''),'enter_discount'=>'Enter discount', 'asset_path' => WDMELE_PLUGIN_URL.'assets', 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		// wp_localize_script( 'wdm-script-handle-login', 'wdmAjax', array( 'asset_path' => WDMELE_PLUGIN_URL.'assets', 'ajaxurl' => admin_url( 'admin-ajax.php' )));
		wp_register_style( 'wdm-style-handle-login', WDMELE_PLUGIN_URL.'assets/css/checkout.css');
		  
		// Change lost password text and remove login page title
		add_filter( 'tml_title', array($this,'tml_title'), 999, 2 );
		add_shortcode( 'wdm_download_checkout', array( $this, 'edd_checkout_form_shortcode' ) );

		// Remove default edd_payment_mode_select
		remove_action( 'edd_payment_mode_select', 'edd_payment_mode_select', 10 );
		// add_action( 'plugins_loaded', array( $this, 'remove_edd_payment_mode_select' ), 999 );
		add_action( 'edd_payment_mode_select', array( $this, 'edd_payment_mode_select') );
		
		remove_action( 'edd_cart_empty', 'edd_csau_display_on_checkout_page', 10 );

		remove_all_actions( 'edd_before_purchase_form' );
		
		add_action('edd_before_purchase_form', array($this, 'edd_before_purchase_form'));

		if( class_exists('MoreEddCartPurchaseOptions') ){

		}
		wp_dequeue_script( 'wdm-edd-checkout-more-option' );
		// add_action('wp_enqueue_scripts', array($this,'wp_enqueue_scripts'),10);
		remove_action('wp_enqueue_scripts', array(WDMCommonFunctions\MoreEddCartPurchaseOptions::getInstance(),'wdmEddLoadScripts'), 10);
		// Stripe credit card field styling
		// https://docs.woocommerce.com/document/stripe-styling-fields/
		// $elements_styles = apply_filters( 'edds_stripe_elements_styles', $elements_styles );
	}
	
	public function wp_enqueue_scripts(){
	}

	public function remove_edd_payment_mode_select( $options ){
	}

	public function get_name() {
		return 'wdmcartloginregister';
	}

	public function get_title() {
		return __( 'Wisdm Checkout Login/Register', 'wdm-elementor-addon-extension' );
	}

	public function get_icon() {
        return 'fa fa-sign-in';
	}

	public function get_categories() {
		return [ 'wdm-elementor-addon-extension' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'wdm_discount_content_section',
			[
				'label' => __( 'Content', 'wdm-elementor-addon-extension' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
        );
        
        $this->add_control(
			'wdm_discount_description',
			[
				'label' => __( 'Description', 'wdm-elementor-addon-extension' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => __( 'Default description', 'wdm-elementor-addon-extension' ),
				'placeholder' => __( 'Type your description here', 'wdm-elementor-addon-extension' ),
			]
        );

		$this->add_control(
			'hr',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
				'condition' => ['fzgallery_style' => array('gallery_image_n_text')]
			]
		);

		$this->add_control(
			'wdm_discount_privacy_policy',
			[
				'label' => __( 'Privacy Policy Details', 'wdm-elementor-addon-extension' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => __( 'Default description', 'wdm-elementor-addon-extension' ),
				'placeholder' => __( 'Type your description here', 'wdm-elementor-addon-extension' ),
			]
		);

		$this->add_control(
			'hr',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
				'condition' => ['fzgallery_style' => array('gallery_image_n_text')]
			]
		);

		$this->add_control(
			'wdm_discount_checkout_btn_txt',
			[
				'label' => __( 'Checkout button label', 'wdm-elementor-addon-extension' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Checkout', 'wdm-elementor-addon-extension' ),
				'placeholder' => __( 'Checkout button label', 'wdm-elementor-addon-extension' )
			]
		);

		$this->add_control(
			'hr',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
				'condition' => ['fzgallery_style' => array('gallery_image_n_text')]
			]
		);

		$this->add_control(
			'wdm_discount_price_details_heading',
			[
				'label' => __( 'Price Details Table Heading', 'wdm-elementor-addon-extension' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Price Details', 'wdm-elementor-addon-extension' ),
				'placeholder' => __( 'Price Details Table Heading', 'wdm-elementor-addon-extension' )
			]
		);
		$this->end_controls_section();
    }

	protected function render() {
		// $settings = $this->get_settings_for_display();
		$current_user = wp_get_current_user();
		?>
		<div class="checkout-login-form-container">
			<?php
			$show_login = $show_register = 'none';
			if ( 0 == $current_user->ID ) {
				$show_login = 'flex';
			}else{
				$show_register = 'block';
			}
			// Not logged in.
			if ( edd_get_cart_contents() || edd_cart_has_fees() ){
			?>
			<?php echo $this->generate_heading($show_login=='flex'?'block':'none')?>
			<div class="wdm-block-login-register-container" style="display:<?php echo $show_login?>">
				<div class="wdm-block-login-register">
					<div class="wdm-block-login">
						<?php $this->generate_login_links_form()?>
					</div>
					<div class="wdm-block-register">
						<?php $this->generate_register_request_form()?>
					</div>
				</div>
			</div>
			<div class="wdm-block-checkout-container" style="display:<?php echo $show_register?>">
				<?php 
					echo do_shortcode( '[wdm_download_checkout]' );
				?>
				<!-- <div class="wdm-block-checkout-container"> -->
					<!-- <div class="wdm-block-checkout-customer-details"> -->
						<?php //echo $this->personal_information_fields()?>
						<?php //echo $this->address_fields()?>
						<?php //echo $this->additional_info_fields()?>
					</div>
					<!-- <div class="wdm-block-checkout"> -->
						<?php //$this->generate_cart_table()?>
						<?php //$this->generate_discount_fields()?>
						<?php //$this->generate_payment_gateways()?>
					<!-- </div> -->
				<!-- </div> -->
			</div>
			<?php }else{
				do_action( 'edd_cart_empty' );
			} ?>
		</div>
		<?php
	}

	public function edd_checkout_form_shortcode( $atts, $content = null ) {
		return $this->edd_checkout_form();
	}

	public function og_edd_checkout_form() {
		$payment_mode = edd_get_chosen_gateway();
		$form_action  = esc_url( edd_get_checkout_uri( 'payment-mode=' . $payment_mode ) );
	
		ob_start();
			echo '<div id="edd_checkout_wrap">';
			if ( edd_get_cart_contents() || edd_cart_has_fees() ) :
	
				$this->edd_checkout_cart();
	?>
				<div id="edd_checkout_form_wrap" class="edd_clearfix">
					<?php do_action( 'edd_before_purchase_form' ); ?>
					<form id="edd_purchase_form" class="edd_form" action="<?php echo $form_action; ?>" method="POST">
						<?php
						/**
						 * Hooks in at the top of the checkout form
						 *
						 * @since 1.0
						 */
						do_action( 'edd_checkout_form_top' );
	
						if ( edd_is_ajax_disabled() && ! empty( $_REQUEST['payment-mode'] ) ) {
							do_action( 'edd_purchase_form' );
						} elseif ( edd_show_gateways() ) {
							do_action( 'edd_payment_mode_select'  );
						} else {
							do_action( 'edd_purchase_form' );
						}
	
						/**
						 * Hooks in at the bottom of the checkout form
						 *
						 * @since 1.0
						 */
						do_action( 'edd_checkout_form_bottom' )
						?>
					</form>
					<?php do_action( 'edd_after_purchase_form' ); ?>
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

	// Changed name for testing
	public function edd_checkout_form() {
		$payment_mode = edd_get_chosen_gateway();
		$form_action  = esc_url( edd_get_checkout_uri( 'payment-mode=' . $payment_mode ) );
	
		ob_start();
			echo '<div id="edd_checkout_wrap">';
			if ( edd_get_cart_contents() || edd_cart_has_fees() ) :
				?>
				<div id="edd_checkout_form_wrap" class="edd_clearfix">
					<h3 class="billing-details-heading">Billing Details</h3>
					<form id="edd_purchase_form" class="edd_form" action="<?php echo $form_action; ?>" method="POST">
						<?php
						/**
						 * Hooks in at the top of the checkout form
						 *
						 * @since 1.0
						 */
						do_action( 'edd_checkout_form_top' );
	
						if ( edd_is_ajax_disabled() && ! empty( $_REQUEST['payment-mode'] ) ) {
							do_action( 'edd_purchase_form' );
						} elseif ( edd_show_gateways() ) {
							do_action( 'edd_payment_mode_select'  );
						} else {
							do_action( 'edd_purchase_form' );
						}
	
						/**
						 * Hooks in at the bottom of the checkout form
						 *
						 * @since 1.0
						 */
						do_action( 'edd_checkout_form_bottom' )
						?>
					</form>
					<?php //do_action( 'edd_after_purchase_form' ); ?>
				</div><!--end #edd_checkout_form_wrap-->
				<div id="edd_checkout_form_wrap_cart">
			<?php
				$this->edd_checkout_cart();
				do_action( 'edd_before_purchase_form' );
			?>
				</div>
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
			echo '<div id="edd_checkout_cart_wrap">';
				do_action( 'edd_checkout_cart_top' );
				edd_get_template_part( 'wdm_checkout_cart' );
				// $this->generate_cart_table();
				do_action( 'edd_checkout_cart_bottom' );
			echo '</div>';
		echo '</form>';
		do_action( 'edd_after_checkout_cart' );
	}

	public function edd_before_purchase_form(){
		$this->generate_discount_fields();
	}

	public function edd_payment_mode_select() {
		if(WdmCheckoutLogin::$edd_payment_mode_select_called!=0){
			return;
		}
		WdmCheckoutLogin::$edd_payment_mode_select_called = 1;
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
	

	public function generate_cart_table(){
		?>
		<div class="wdm-block-cart-table">
			<?php
			// $cart_contents = edd_get_cart_content_details();
			$cart_contents = edd_get_cart_contents();
			if ( ! empty( $cart_contents ) ) {
				?>
				<table class="">
					<thead>
						<tr class="wdm_edd_cart_header_row">
							<th class="wdm_edd_cart_item_name">Product Name</th>
							<th class="wdm_edd_cart_item_license">No. of License</th>
							<th class="wdm_edd_cart_item_price">Price</th>
						</tr>
					</thead>
				<?php
				echo '<tbody>';
				foreach ( $cart_contents as $item_key=>$item ) {
					$renewal_data = $this->renewal_notice( $item );
					list($license_priod,$license_options) = $this->get_cart_table_details($item);
					$item_name = '<div class="wdm_edd_cart_item_name_div"><span class="product_item_name">'.$this->get_product_title($item['id']).'</span>'.(!empty($license_priod)?'<span class="recurring_license_period">'.$license_priod.'</span>':'').'</div>';
					$item_name .= $renewal_data;
					echo '<tr>';
					echo '<td class="wdm_edd_cart_item_name_value">'.$item_name.'</td>';
					echo '<td class="wdm_edd_cart_item_licenses" data-nonce="'.$this->cart_nonce.'" data-cart-key="'.$item_key.'" >'.$this->get_license_options_html($license_options,$item_key).'</td>';
					echo '<td class="wdm_edd_cart_item_price_value">'.edd_cart_item_price( $item['id'], $item['options'] ).'</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				?>
					<tfoot>
						<tr class="wdm_edd_cart_footer_row">
							<th class="edd_cart_total">Sub Total</th>
							<th></th>
							<th class="edd_cart_amount"><span data-subtotal="<?php echo edd_get_cart_subtotal()?>" data-total="<?php echo edd_get_cart_total()?>"><?php echo edd_cart_subtotal()?></span></th>
							<th></th>
						</tr>
					</tfoot>
				</table>
				<?php		
			}else{
				if(!empty($settings['wdm_cart_empty_message'])){
					echo '<div class="wdm-empty-cart">'.$settings['wdm_cart_empty_message'].'</div>';
				}
			} 
			?>
		</div>
		<?php
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
		
		$return[0] = $this->get_period($cart_item);
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

	public function generate_discount_fields(){
		$settings = $this->get_settings_for_display();
		$current_user = wp_get_current_user();
		$email = $current_user?$current_user->user_email:'';
		$discounts = EDD()->cart->get_discounts();
		$discount = $rate = '';
		
		$subtotal = edd_get_cart_subtotal();
		$carttotal = EDD()->cart->get_total();
		$total = $subtotal - $carttotal;

		foreach ( $discounts as $discount ) {
			$discount_id = edd_get_discount_id_by_code( $discount );
			$rate        = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );
		}
		$is_renewal = EDD()->session->get( 'edd_is_renewal' );
		?>
		<div class="wdm-block-cart-price-details">
			<div class="wdm-block-cart-price-details-data">
				<table class="wdm-block-cart-price-details-table">
					<tr class="wdm-block-cart-price-details-discount-check">
						<td><?php $this->generate_apply_coupon_check($discount)?></td>
						<td></td>
					</tr>
					<tr class="wdm-block-cart-price-details-discount" style="<?php echo $discount?'':'display:none'?>">
						<td><?php $this->generate_discount_field_input($discount)?></td>
						<td><span class="coupon-value"><?php echo !empty($total)?' - ' . edd_currency_symbol(edd_get_currency()) . number_format($total,2):''?></span><?php echo $rate?'<span class="coupon-rate">('.$rate.')</span>':''?></td>
					</tr>
					<tr class="wdm-block-cart-price-details-renewal-check">
						<td><?php $this->generate_apply_renewal_check($is_renewal)?></td>
						<td></td>
					</tr>
					<tr class="wdm-block-cart-price-details-renewal" style="<?php echo $is_renewal?'':'display:none'?>">
						<td colspan="2"><?php $this->generate_renewal_field_input($is_renewal)?></td>
					</tr>
					<tr class="wdm-block-cart-price-details-grandtotal">
						<td>Grand Total</td>
						<td id="cart-grandtotal"><?php echo edd_cart_total(0)?></td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}

	public function generate_payment_gateways(){

	}

	public function generate_apply_coupon_check($discount=''){
		?>
		<label class="apply_check_label checkbox-container" for="apply_discount_check"> Apply Coupon<input type="checkbox" id="apply_discount_check" class="apply_discount_check" name="apply_discount_check" value="1" <?php echo $discount?'checked':''?>><span class="checkmark"></span></label>
		<?php
	}

	public function generate_discount_field_input($discount=''){
		$email = $current_user?$current_user->user_email:'';
		?>
		<div class="wdm-block-discount-field">
			<div class="coupon-fields">
				<label class="wdm-block-discount-field-label">Enter a Coupon Code</label>
				<input type="text" class="wdm-block-discount-text-field" id="edd-discount" value="<?php echo $discount?>" />
				<input type="hidden" id="edd-user-email" value="<?php echo $email?>" />
			</div>
			<div class="applied-message-div">
				<button class="wdm-block-discount-apply" id="wdm-block-discount-apply" style="<?php echo $discount?'display:none':''?>">Apply</button>
				<span class="applied-message" id="applied-message" style="<?php echo $discount?'':'display:none'?>">
				<img src="<?php echo WDMELE_PLUGIN_URL.'assets/images/applied_tick.svg'?>"/> Applied</span>
				<a href="#" data-code="<?php echo $discount?>" id="wdm-edd-remove-discount" class="wdm-edd-remove-discount" style="<?php echo $discount?'':'display:none'?>">Remove</a>
			</div>
			<span id="edd-discount-error-wrap" class="edd_error edd-alert edd-alert-error" aria-hidden="true" style="display:none;"></span>
		</div>
		<?php
	}

	public function generate_apply_renewal_check($is_renewal=''){
		?>
		<label class="apply_check_label checkbox-container" for="apply_renewal_check"> Is Renewal<input type="checkbox" id="apply_renewal_check" class="apply_renewal_check" name="apply_renewal_check" value="1" <?php echo $is_renewal?'checked':''?>><span class="checkmark"></span></label>
		<?php
	}

	public function generate_renewal_field_input($renewal='') {

		if( ! edd_sl_renewals_allowed() ) {
			return;
		}
	
		$renewal_keys = edd_sl_get_renewal_keys();
		$preset_key   = ! empty( $_GET['key'] ) ? esc_html( urldecode( $_GET['key'] ) ) : '';
		$error        = ! empty( $_GET['edd-sl-error'] ) ? sanitize_text_field( $_GET['edd-sl-error'] ) : '';
		$color        = edd_get_option( 'checkout_color', 'blue' );
		$color        = ( $color == 'inherit' ) ? '' : $color;
		$style        = edd_get_option( 'button_style', 'button' );
		ob_start(); ?>
		<form method="post" id="edd_sl_renewal_form">
			<fieldset id="edd_sl_renewal_fields">
				<p id="edd-license-key-container-wrap" class="edd-cart-adjustment">
					<span class="edd-description"><?php _e( 'Enter the license key you wish to renew. Leave blank to purchase a new one.', 'edd_sl' ); ?></span>
				</p>
					<div class="renewal-fields">
						<label id="edd_license_key_label">Enter your license key</label>
						<input class="edd-input required" type="text" name="edd_license_key" autocomplete="off" id="edd-license-key" value="<?php echo $preset_key; ?>"/>
						<input type="hidden" name="edd_action" value="apply_license_renewal"/>
					</div>
				<p class="edd-sl-renewal-actions">
					<input type="submit" id="edd-add-license-renewal" disabled="disabled" class="edd-submit button <?php echo $color . ' ' . $style; ?>" value="<?php _e( 'Apply', 'edd_sl' ); ?>"/>&nbsp;<span><a href="#" id="edd-cancel-license-renewal"><?php _e( 'Cancel', 'edd_sl' ); ?></a></span>
				</p>
	
				<?php if( ! empty( $renewal ) && ! empty( $renewal_keys ) ) : ?>
					<p id="edd-license-key-container-wrap" class="edd-cart-adjustment">
						<span class="edd-description"><?php _e( 'You may renew multiple license keys at once.', 'edd_sl' ); ?></span>
					</p>
				<?php endif; ?>
			</fieldset>
			<?php if( ! empty( $error ) ) : ?>
				<div class="edd_errors">
						<p class="edd_error"><?php echo urldecode( sanitize_text_field( $_GET['message'] ) ); ?></p>
				</div>
			<?php endif; ?>
		</form>
		<?php if( ! empty( $renewal ) && ! empty( $renewal_keys ) ) : ?>
		<form method="post" id="edd_sl_cancel_renewal_form">
			<p>
				<input type="hidden" name="edd_action" value="cancel_license_renewal"/>
				<input type="submit" class="edd-submit button" value="<?php _e( 'Cancel License Renewal', 'edd_sl' ); ?>"/>
			</p>
		</form>
		<?php
		endif;
		echo ob_get_clean();
	}

	public function generate_heading($show_login=''){
		return '<h2 class="log-reg-heading" style="display:'.$show_login.'">To complete your transaction, you need to <b>Login or Register</b></h2>';
	}

	public function generate_login_links_form($provider='google'){
		echo do_shortcode( '[theme-my-login action="login" login_template="wdm-login-form.php"]' );
	}

	public function generate_register_request_form(){
		?>
		<div class="wdm-cart-register"><p>Don’t have an account?<br/>Register one!</p><a href="#" class="">Get Registered</a></div>
		<?php
	}

	public function get_login_fields(){
		$html = '';
		
	}

	// TML Lost password title change
	public function tml_title($title, $action){
		if($action=='lostpassword'){
			$title = __( 'Forgot Password?', 'wdm-elementor-addon-extension' );
		}
		if($action=='' || $action='login'){
			$title = '';
		}
		return $title;
	}

	/**
	 * get_sales_rating_data
	 *
	 * @param  int $productId  Product id
	 * @return array
	 */
	public function get_sales_rating_data($productId){}

	public function get_script_depends(){
		return ['wdm-script-handle-login'];
	}

	public function get_style_depends(){
		return ['wdm-style-handle-login'];
	}

	public function is_widget_active($widget_name=''){
		if($widget_name){
			global $post;
			$document = \Elementor\Plugin::instance()->documents->get_doc_for_frontend( $post->ID );
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

	// public function render_plain_content() {
	// 	// In plain mode, render without shortcode
	// 	print_r($this->get_settings());
	// }

	protected function content_template() {}
}