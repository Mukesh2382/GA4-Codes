<?php
namespace Elementor;
class Minimal_checkout_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-minimal-checkout-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Minimal Checkout', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return array(
            'minimal-checkout-script',
            'edwiser-minimal-checkout-helper-js'
        );
    }

    public function get_style_depends() {
        return [ 'minimal-checkout-style'];
    }

    public function get_icon() {
        return 'eicon-counter-circle';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        // $downloads = \WisdmEW_Edd::get_downloads();

        $this->style_tab();
    }

    private function style_tab() {
        // $this->general_style();
        // $this->title_style();
        // $this->button_style();
    }

	public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);

		add_shortcode( 'wdm_minimal_checkout_reviews', array( $this, 'wdm_minimal_checkout_reviews' ) );

        wp_register_script( 'edwiser-minimal-checkout-helper-js', MYEW_PLUGIN_URL . 'assets/minimal-checkout/minimal-checkout-helper.js', array( 'jquery' ), filemtime( MYEW_PLUGIN_PATH . 'assets/minimal-checkout/minimal-checkout-helper.js' ), true );
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        ?>
        <div class="wdm-elementor-minimal-checkout" >
            <div class="checkout-login-form-container">
                <?php
                $show_login = 'none';
                $show_register = 'block';
        
                // Not logged in.
                if ( edd_get_cart_contents() || edd_cart_has_fees() ){
                ?>
                <?php echo $this->generate_heading($show_login=='flex'?'block':'none')?>
                <div class="wdm-block-checkout-container" style="display:<?php echo $show_register?>">
                    <?php 
                        echo do_shortcode( '[wdm_minimal_download_checkout]' );
                    ?>
                </div>
                <?php }else{
                    do_action( 'edd_cart_empty' );
                } ?>
            </div>
        </div>
        <?php
    }

    public function wdm_minimal_checkout_reviews() {
        $limit = 2;
		$filter = 'most-recent';
        $page = 1;
        $reviews = [];
		$cart_contents = edd_get_cart_contents();
		if($cart_contents && function_exists('wdmGetCourseReviews')){
			foreach( $cart_contents as $item ){
				$reviews = wdmGetCourseReviews( $limit, $filter, $item['id'] );
				if($reviews){
					break;
				}
			}
		}
		ob_start();
        
		foreach( $reviews as $review ){
			$gravatar = get_avatar($review->post_author, 30);
			$details  = '' . nl2br(( stripslashes($review->post_content) ));
			$author   = get_the_author_meta('display_name', $review->post_author);

			?>
			<div class="wdm-testimonial-wrapper">
				<div class="wdm-testimonial-content">
					<?php echo $details?>
				</div>
				<div class="wdm-testimonial-meta">
					<div class="wdm-testimonial-meta-inner">
						<div class="wdm-testimonial-meta-image">
							<?php echo $gravatar?>
						</div>
						<div class="wdm-testimonial-meta-details">
							<div class="wdm-testimonial-name">
								<?php echo $author?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		return ob_get_clean();
    }

    public function generate_heading($show_login=''){
		return '<h2 class="log-reg-heading" style="display:'.$show_login.'">To complete your transaction, you need to <b>Login or Register</b></h2>';
	}

    protected function _content_template() {

    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Minimal_checkout_Widget() );
