<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * WdmCart to create a new custom widget
 */
class WdmCart extends \Elementor\Widget_Base {
	public $cart_nonce;

	public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);
		$this->cart_nonce = wp_create_nonce( 'wdm-elem-cart' );
		
		// Script and locallize script
		wp_register_script( 'wdm-script-handle-cart', WDMELE_PLUGIN_URL.'assets/js/cart.js', [ 'elementor-frontend' ], '1.0.0', true );
		wp_localize_script( 'wdm-script-handle-cart', 'wdmAjax', array( 'asset_path' => WDMELE_PLUGIN_URL.'assets', 'ajaxurl' => admin_url( 'admin-ajax.php' )));

		// Style
		wp_register_style( 'wdm-style-handle-cart', WDMELE_PLUGIN_URL.'assets/css/cart.css');

		// Ajax calls handling
		add_action( 'wp_ajax_wdm_elem_cart_change_license', array($this,'wdm_elem_cart_change_license') );
		add_action( 'wp_ajax_nopriv_wdm_elem_cart_change_license', array($this,'wdm_elem_cart_change_license') );
	}

    public function get_name() {
		return 'wdmcart';
	}

	public function get_title() {
		return __( 'Wisdm Cart', 'wdm-elementor-addon-extension' );
	}

	public function get_icon() {
        return 'fa fa-shopping-cart';
	}

	public function get_categories() {
		return [ 'wdm-elementor-addon-extension' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'wdm_cart_content_section',
			[
				'label' => __( 'Content', 'wdm-elementor-addon-extension' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
        );
        
        $this->add_control(
			'wdm_cart_heading',
			[
				'label' => __( 'Heading', 'wdm-elementor-addon-extension' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Cart', 'wdm-elementor-addon-extension' ),
				'placeholder' => __( 'Type Cart Heading', 'wdm-elementor-addon-extension' )
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
			'wdm_cart_empty_message',
			[
				'label' => __( 'Cart Empty Message', 'wdm-elementor-addon-extension' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => __( 'Your cart is empty.', 'wdm-elementor-addon-extension' ),
				'placeholder' => __( 'Type cart empty message here', 'wdm-elementor-addon-extension' ),
			]
		);

		$this->end_controls_section();
    }

	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<div class="wdm-block-cart-table">
			<?php
			// $cart_contents = edd_get_cart_content_details();
			$cart_contents = edd_get_cart_contents();
			if ( ! empty( $cart_contents ) ) {
				if(!empty($settings['wdm_cart_heading'])){
					echo '<h3 class="wdm-block-cart-table-heading">'.$settings['wdm_cart_heading'].'</h3>';
				}else{
					echo '<h3 class="wdm-block-cart-table-heading">Cart</h3>';
				}
				?>
				<table class="">
					<thead>
						<tr class="wdm_edd_cart_header_row">
							<th class="wdm_edd_cart_item_name">Product Name</th>
							<th class="wdm_edd_cart_item_license">No. of License</th>
							<th class="wdm_edd_cart_item_price">Price</th>
							<th class="wdm_edd_cart_actions"></th>
						</tr>
					</thead>
				<?php
				echo '<tbody>';
				foreach ( $cart_contents as $item_key=>$item ) {
					$thumbnail = '';
					if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $item['id'] ) ) {
						$thumbnail .='<div class="wdm_edd_cart_item_image">';
						$thumbnail .= get_the_post_thumbnail( $item['id'], array( 40,40 ) );
						$thumbnail .= '</div>';
					}
					$renewal_data = $this->renewal_notice( $item );
					list($license_priod,$license_options) = $this->get_cart_table_details($item);
					$item_name = '<div class="wdm_edd_cart_item_name_div"><span class="product_item_name">'.$this->get_product_title($item['id']).'</span>'.(!empty($license_priod)?'<span class="recurring_license_period">'.$license_priod.'</span>':'').'</div>';
					$item_name .= $renewal_data;
					echo '<tr>';
					echo '<td class="wdm_edd_cart_item_name_value">'.$thumbnail.$item_name.'</td>';
					echo '<td class="wdm_edd_cart_item_licenses">'.$this->get_license_options_html($license_options,$item_key).'</td>';
					echo '<td class="wdm_edd_cart_item_price_value">'.edd_cart_item_price( $item['id'], $item['options'] ).'</td>';
					echo '<td class="wdm_edd_cart_item_action"><a data-nonce="'.$this->cart_nonce.'" data-cart-key="'.$item_key.'" href="#"><img src="'.WDMELE_PLUGIN_URL.'assets/images/remove_icon.svg"><span>Remove</span></a></td>';
					echo '</tr>';
				}
				echo '</tbody>';
				?>
					<tfoot>
						<tr class="wdm_edd_cart_footer_row">
							<th></th>
							<th class="edd_cart_total">Sub Total</th>
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

	public function get_product_title($download_id=0){
		$title = '';
		if($download_id){
			$title = get_the_title( $download_id );
		}
		return $title;
	}

	public function get_cart_table_details($cart_item){
		$return = array(false,'');
		$download_id = $cart_item['id'];
		$added_download			= new EDD_SL_Download( $download_id );
		$licensing_enabled		= $added_download->licensing_enabled();
		$has_variable_prices	= $added_download->has_variable_prices();
		$is_bundle				= $added_download->is_bundled_download();
		
		$return[0]      = $this->get_period($cart_item);
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

	public function get_license_options_html($options=array(),$item_key=0){
		$html = '';
		if($options){
			foreach ($options as $key => $value) {
				$radio_image = !empty($value['selected'])?WDMELE_PLUGIN_URL.'assets/images/selected_radio.svg':WDMELE_PLUGIN_URL.'assets/images/radio.svg';
				if($value['label']){
					$html .= '<span class="license_options'.(!empty($value['selected'])?' license_options_checked':'').'"><span class="license_options_radio"><img class="radio_button_img" src="'.$radio_image.'"><input style="display:none" data-nonce="'.$this->cart_nonce.'" type="radio" name="license_options_'.$item_key.'" value="'.$value['value'].'" '. (!empty($value['selected'])?'checked="true"':'') .'></span> <span class="license_options_quantity">'.$value['label'].'</span></span>';
				}
			}
		}
		return $html;
	}

	public function get_script_depends(){
		return ['wdm-script-handle-cart'];
	}

	public function get_style_depends(){
		return ['wdm-style-handle-cart'];
	}

	// public function render_plain_content() {
	// 	// In plain mode, render without shortcode
	// 	print_r($this->get_settings());
	// }

	protected function content_template() {}
}