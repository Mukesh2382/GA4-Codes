<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * WdmCartRelatedProducts to create a new custom widget
 */
class WdmCartRelatedProducts extends \Elementor\Widget_Base {
	public $related_cart_nonce;

	public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);
		$this->related_cart_nonce = wp_create_nonce( 'wdm-elem-related-product' );

		wp_register_script( 'wdm-script-handle-related-prods', WDMELE_PLUGIN_URL.'assets/js/related.js', [ 'elementor-frontend' ], '1.0.0', true );
		wp_localize_script( 'wdm-script-handle-related-prods', 'wdmAjax', array( 'asset_path' => WDMELE_PLUGIN_URL.'assets', 'ajaxurl' => admin_url( 'admin-ajax.php' )));
      	wp_register_style( 'wdm-style-handle-related-prods', WDMELE_PLUGIN_URL.'assets/css/related.css');
	}

    public function get_name() {
		return 'wdmcartrelatedproducts';
	}

	public function get_title() {
		return __( 'Wisdm Cart Related Products', 'wdm-elementor-addon-extension' );
	}

	public function get_icon() {
        return 'fa fa-shopping-bag';
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
			'wdm_related_product_heading',
			[
				'label' => __( 'Related product label', 'wdm-elementor-addon-extension' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Related Product', 'wdm-elementor-addon-extension' ),
				'placeholder' => __( 'Related Product Heading', 'wdm-elementor-addon-extension' )
			]
		);
		$this->end_controls_section();
    }

	protected function render() {
		$settings = $this->get_settings_for_display();
		if (function_exists('edd_csau_get_products')) {
			$cart_contents = edd_get_cart_contents();
			if(!empty($cart_contents)){
				$products = $related_product_id = false;
				foreach ($cart_contents as $cart_item_key => $cart_item) {
					$products = edd_csau_get_products($cart_item['id'], 'upsell');
					if(!$products){
						continue;
					}else{
						$products = array_unique($products);
						foreach ($products as $related_product_key => $related_product) {
							if( edd_item_in_cart( $related_product ) ) {
								unset( $products[ $related_product_key ] );
							}
						}
						break;
					}
				}
				if($products){
					if(!empty($settings['wdm_related_product_heading'])){
					?>
						<h3 class="wdm-block-related-products-heading"><?php echo $settings['wdm_related_product_heading']?></h3>
					<?php
					} 
					foreach ($products as $related_product_key => $related_product) {
						# code...
						list($sales,$rating) = $this->get_sales_rating_data($related_product);
						?>
						<div class="wdm-block-related-products">
							<div class="wdm-block-related-product-image">
								<?php
									$thumbnail = '';
									if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $related_product ) ) {
										$thumbnail .='<div class="wdm_edd_related_products_img">';
										$thumbnail .= get_the_post_thumbnail( $related_product, array( 50,50 ) );
										$thumbnail .= '</div>';
									}
									echo $thumbnail; 
								?>
							</div>
							<div class="wdm-block-related-product-content">
								<h4 class="wdm-block-related-product-title">
									<?php echo get_the_title($related_product)?>
								</h4>
								<div class="wdm-block-related-product-rating">
									<?php if($sales){?>
										<span class="wdm-block-related-product-sale"><img src="<?php echo WDMELE_PLUGIN_URL.'assets/images/thumbs_up.svg'?>"> <?php echo '<span class="wdm-block-related-product-sale-count">'.$sales.'</span>'?> <span class="wdm-block-related-product-sale-label">Happy Customers</span></span>
									<?php } 
									if($rating){?>
										<span class="wdm-block-related-product-avg-rate"><img src="<?php echo WDMELE_PLUGIN_URL.'assets/images/star.svg'?>"> <?php echo '<span class="wdm-block-related-product-avg-rate-value">'.$rating.'</span>'?> <span class="wdm-block-related-product-avg-rate-label">Avg. Rating</span></span>
									<?php }?>
								</div>
								<div id="wdm-block-related-product-cta" class="wdm-block-related-product-cta">
									<a data-nonce="<?php echo $this->related_cart_nonce?>" data-download="<?php echo $related_product?>" href="<?php echo add_query_arg( array( 'edd_action' => 'add_to_cart', 'download_id' => $related_product ), edd_get_checkout_uri() ); ?>">Add to Cart</a>
								</div>
							</div>
						</div>
					<?php
						break;
					}
				}
			}
		}

	}
	
	/**
	 * get_sales_rating_data
	 *
	 * @param  int $productId  Product id
	 * @return array
	 */
	public function get_sales_rating_data($productId){
		$rating_details = wdmGetCourseRatingDetails($productId);
		$total_stars = count($rating_details['rating']);
		// $rating_HTML = wdmGetStarHTMLStruct($course_id, $rating_details['average_rating']);
		$totalCount = $rating_details['total_count'];
		$avgRating = $rating_details['average_rating'];

		$offSet = get_post_meta($productId, '_downlaod_offset', true);
		$sales = edd_get_download_sales_stats($productId);
		if ($offSet) {
			$sales = $sales + $offSet;
		}
		if ($productId=="127679") {
			// eLumine Bundle basic
			$sales += edd_get_download_sales_stats(162691);
			// eLumine: Treasure Chest
			$sales += edd_get_download_sales_stats(162694);
			// eLumine: Goldmine
			$sales += edd_get_download_sales_stats(162696);
			// LearnDash Starter Pack
			$sales += edd_get_download_sales_stats(64501);
			// Elumine LEAP
			$sales += edd_get_download_sales_stats(366221);
			// Elumine Advisor
			$sales += edd_get_download_sales_stats(366218);
		}elseif($productId=="6963"/*CSP*/){
			// 289453 SBP
			$sales += edd_get_download_sales_stats(289453);
		}elseif($productId=="11445"/*Scheduler*/){
			// 289453 SBP
			$sales += edd_get_download_sales_stats(289453);
		}elseif($productId=="44670"/*LDGR*/){
			// 366225 LDGR Advisor
			// 368742 LDGR LEAP
			// 341418 LPP Plus
			$sales += edd_get_download_sales_stats(366225);
			$sales += edd_get_download_sales_stats(368742);
			$sales += edd_get_download_sales_stats(341418);
		}elseif($productId=="109665"/*LDRRF*/){
			// 366227 LDRRF Advisor
			// 368744 LDRRF LEAP
			// 341418 LPP Plus
			$sales += edd_get_download_sales_stats(366227);
			$sales += edd_get_download_sales_stats(368744);
			$sales += edd_get_download_sales_stats(341418);
		}elseif($productId=="20277"/*IR*/){
			// 366223 IR Advisor
			// 366236 IR LEAP
			// 341418 LPP Plus
			$sales += edd_get_download_sales_stats(366223);
			$sales += edd_get_download_sales_stats(366236);
			$sales += edd_get_download_sales_stats(341418);
		}elseif($productId=="14995"/*QRE*/){
			// 366234 QRE Advisor
			// 368714 QRE LEAP
			// 341418 LPP Plus
			$sales += edd_get_download_sales_stats(366234);
			$sales += edd_get_download_sales_stats(368714);
			$sales += edd_get_download_sales_stats(341418);
		}elseif($productId=="34202"/*CC*/){
			// 368743 CC LEAP
			// 341418 LPP Plus
			$sales += edd_get_download_sales_stats(368743);
			$sales += edd_get_download_sales_stats(341418);
		}
		$output[0] = $sales;
		$output[1] = number_format(round($avgRating, 1), 1);
		return $output;
	}

	public function get_script_depends(){
		return ['wdm-script-handle-related-prods'];
	}

	public function get_style_depends(){
		return ['wdm-style-handle-related-prods'];
	}
    // public function render_plain_content() {
	// 	// In plain mode, render without shortcode
	// 	print_r($this->get_settings());
	// }

	protected function content_template() {}
}