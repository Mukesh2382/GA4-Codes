<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
use Elementor\Controls_Manager;
/**
 * WdmCartDiscount to create a new custom widget
 */
class WdmCartDiscount extends \Elementor\Widget_Base {
	public static $added_chart = 0;

	public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);
		wp_register_script( 'wdm-script-handle-discount', WDMELE_PLUGIN_URL.'assets/js/discount.js', [ 'elementor-frontend' ], '1.0.0', true );
		wp_localize_script( 'wdm-script-handle-discount', 'wdmDiscountAjax', array( 'decimal_separator' => edd_get_option( 'decimal_separator', '.' ),'currency_sign'=> edd_currency_filter(''),'enter_discount'=>'Enter discount', 'asset_path' => WDMELE_PLUGIN_URL.'assets', 'ajaxurl' => admin_url( 'admin-ajax.php' )));

      	wp_register_style( 'wdm-style-handle-discount', WDMELE_PLUGIN_URL.'assets/css/discount.css');
	}

    public function get_name() {
		return 'wdmcartdiscount';
	}

	public function get_title() {
		return __( 'Wisdm Cart Discount', 'wdm-elementor-addon-extension' );
	}

	public function get_icon() {
        return 'fa fa-tags';
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
		$settings = $this->get_settings_for_display();
		$current_user = wp_get_current_user();
		$email = $current_user?$current_user->user_email:'';
		$discounts = EDD()->cart->get_discounts();
		$discount = $rate = '';
		
		// $subtotal = filter_var( edd_cart_subtotal(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		// $carttotal = filter_var( edd_cart_total(0), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		// $total = $subtotal - $carttotal;
		$coupon_value = edd_get_cart_subtotal() - EDD()->cart->get_total();
		foreach ( $discounts as $discount ) {
			$discount_id = edd_get_discount_id_by_code( $discount );
			$rate        = edd_format_discount_rate( edd_get_discount_type( $discount_id ), edd_get_discount_amount( $discount_id ) );
		}
		?>
		<div class="wdm-block-discount-field">
			<p class="wdm-block-discount-field-p">Have a Coupon Code?</p>
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
		<div class="wdm-block-cart-price-details">
			<div class="wdm-block-cart-price-details-data">
				<?php if(!empty($settings['wdm_discount_price_details_heading'])){ ?>
				<h4 class="wdm-block-cart-price-details-heading"><?php echo $settings['wdm_discount_price_details_heading']?></h4>
				<?php }?>
				<table class="wdm-block-cart-price-details-table">
					<tr class="wdm-block-cart-price-details-subtotal">
						<td>Sub Total</td>
						<td class="cart-price-details-subtotal-value"><?php echo edd_cart_subtotal()?></td>
					</tr>
					<tr class="wdm-block-cart-price-details-discount">
						<td>Coupon Discount (-)</td>
						<td class="coupon-value"><?php echo !empty($coupon_value)?' - ' . edd_currency_symbol(edd_get_currency()) . number_format($coupon_value,2):''?></td>
					</tr>
					<tr class="wdm-block-cart-price-details-discount-rate">
						<td></td>
						<td class="coupon-rate"><?php echo $rate?'('.$rate.')':''?></td>
					</tr>
					<tr class="wdm-block-cart-price-details-grandtotal">
						<td>Grand Total</td>
						<td id="cart-grandtotal"><?php echo edd_cart_total(0)?></td>
					</tr>
				</table>
			</div>
			<div class="wdm-block-cart-read-accept-policy">
				<?php if(!empty($settings['wdm_discount_description'])){ ?>
					<p class="wdm-block-cart-read-accept-policy-text">
						<?php echo $settings['wdm_discount_description']?>
					</p>
				<?php }?>
				<?php if(!empty($settings['wdm_discount_privacy_policy'])){ ?>
					<p class="wdm-block-cart-read-accept-policy-check">
						<label for="cart_read_accept_policy_check" class="checkbox-container"> <?php echo $settings['wdm_discount_privacy_policy']?><input id="cart_read_accept_policy_check" type="checkbox" name="cart_read_accept_policy_check"/><span class="checkmark"></span></label>
					</p>
				<?php } ?>
				<?php if(!empty($settings['wdm_discount_checkout_btn_txt'])){ ?>
					<p class="wdm-block-cart-proceed-to-checkout">
						<a class="button disabled" href="<?php echo edd_get_checkout_uri()?>"><?php echo $settings['wdm_discount_checkout_btn_txt']?></a>
					</p>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	public function get_script_depends(){
		return ['wdm-script-handle-discount'];
	}

	public function get_style_depends(){
		return ['wdm-style-handle-discount'];
	}

    // public function render_plain_content() {
	// 	// In plain mode, render without shortcode
	// 	print_r($this->get_settings());
	// }

	protected function content_template() {}
}