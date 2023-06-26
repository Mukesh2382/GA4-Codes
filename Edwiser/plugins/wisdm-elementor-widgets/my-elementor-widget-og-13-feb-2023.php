<?php
/**
 * Plugin Name: Wisdm Elementor Widgets
 * Plugin URI: https://wisdmlabs.com/
 * Description: Custom elementor widgets for Wisdm and edwiser sites
 * Version: 1.0.0
 * Author: Swapnil Mahadik
 * Author URI: https://johndoe.me
 * Text Domain: wisdm-elementor-widgets
 */

 if( ! defined( 'ABSPATH' ) ) exit();

define("WDM_WIDGETS_PLUGIN_PATH", plugin_dir_url(__FILE__));
define('WDM_WIDGETS_DEFAULT_TITLE','Lorem Ipsum');
define('WDM_WIDGETS_DEFAULT_DESC','Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat');
define('WDMELE_PLUGIN_URL', trailingslashit(plugins_url('/', __FILE__)));

/**
 * Elementor Extension main CLass
 * @since 1.0.0
 */
final class MY_Elementor_Widget {

    // Plugin version
    const VERSION = '1.0.0';

    // Minimum Elementor Version
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

    // Minimum PHP Version
    const MINIMUM_PHP_VERSION = '7.0';

    // Instance
    private static $_instance = null;

    /**
    * SIngletone Instance Method
    * @since 1.0.0
    */
    public static function instance() {
        if( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
    * Construct Method
    * @since 1.0.0
    */
    public function __construct() {
        // Call Constants Method
        $this->define_constants();

        add_action( 'init', [ $this, 'i18n' ] );
        add_action( 'plugins_loaded', [ $this, 'init' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'scripts_styles' ],99999 );
        add_action( 'elementor/frontend/before_enqueue_scripts', [ $this, 'edit_scripts' ] ,99999);
        // Load scripts only on Elementor Editor.
        add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_scripts_on_editor_only' ], 99999);
        $this->load_dependencies();
   }

    /**
    * Define Plugin Constants
    * @since 1.0.0
    */
    public function define_constants() {
        define( 'MYEW_PLUGIN_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
        define( 'MYEW_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
        // site specific constants
        require_once MYEW_PLUGIN_PATH . '/includes/constants.php';
    }

    public function load_pricing_tables_script(){
        $elementor_page = get_post_meta( get_the_ID(), '_elementor_edit_mode', true );
        if($elementor_page){
            if(wp_script_is('better-pricing-table-js')){
                wp_dequeue_script( 'better-pricing-table-js' );
            }
            // Pricing Table
            wp_register_script( 'wdm-pricing-table-script', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table/script.js', [ ], rand(), true );
            wp_register_script( 'wdm-pricing-table-style', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table/script.js', [ ], rand(), true );
            wp_localize_script( 'wdm-pricing-table-script', "free_download_ajax", array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('free-download-ajax-security')
            ));
            wp_enqueue_script( 'wdm-pricing-table-script' );
            wp_enqueue_style( 'wdm-pricing-table-style' );
        }
    }
    /**
    * Load Scripts & Styles
    * @since 1.0.0
    */
    public function scripts_styles() {
        
        wp_register_style( 'myew-owl-carousel', MYEW_PLUGIN_URL . 'assets/vendor/owl-carousel/css/owl.carousel.min.css', [], rand(), 'all' );
        wp_register_style( 'myew-owl-carousel-theme', MYEW_PLUGIN_URL . 'assets/vendor/owl-carousel/css/owl.theme.default.min.css', [], rand(), 'all' );
        wp_register_script( 'myew-owl-carousel', MYEW_PLUGIN_URL . 'assets/vendor/owl-carousel/js/owl.carousel.min.js', [ 'jquery' ], rand(), true );
        wp_register_style( 'myew-style', MYEW_PLUGIN_URL . 'assets/dist/css/public.min.css', [], rand(), 'all' );
        wp_register_script( 'myew-script', MYEW_PLUGIN_URL . 'assets/dist/js/public.min.js', [ 'jquery' ], rand(), true );

        wp_enqueue_style( 'myew-owl-carousel' );
        wp_enqueue_style( 'myew-owl-carousel-theme' );
        wp_enqueue_script( 'myew-owl-carousel' );
        wp_enqueue_style( 'myew-style' );
        wp_enqueue_script( 'myew-script' );

        // slick
        wp_register_style( 'slick-style', MYEW_PLUGIN_URL . 'assets/slick/slick.css', [], rand(), 'all' );
        wp_register_script( 'slick-script', MYEW_PLUGIN_URL . 'assets/slick/slick.min.js', [ 'jquery' ], rand(), true );
        wp_register_script( 'slick-slider-script', MYEW_PLUGIN_URL . 'assets/slick/slider.js', [ 'jquery','slick-script' ], rand(), true );
        wp_enqueue_style( 'slick-style' );
        wp_enqueue_script( 'slick-script' );
        wp_enqueue_script( 'slick-slider-script' );

        // sneakpeak
        wp_register_style( 'sneakpeak-style', MYEW_PLUGIN_URL . 'assets/sneakpeek/style.css', [], rand(), 'all' );
        wp_register_script( 'sneakpeak-script', MYEW_PLUGIN_URL . 'assets/sneakpeek/script.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'sneakpeak-style' );
        wp_enqueue_script( 'sneakpeak-script' );

        // Advance Features
        wp_register_style( 'adv-features-style', MYEW_PLUGIN_URL . 'assets/adv-features/style.css', [], rand(), 'all' );
        wp_register_script( 'adv-features-script', MYEW_PLUGIN_URL . 'assets/adv-features/script.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'adv-features-style' );
        wp_enqueue_script( 'adv-features-script' );

        // Slider Popup
        wp_register_style( 'slider-popup-style', MYEW_PLUGIN_URL . 'assets/slider-popup/slider-popup.css', [], rand(), 'all' );
        wp_register_script( 'slider-popup-script', MYEW_PLUGIN_URL . 'assets/slider-popup/slider-popup.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'slider-popup-style' );
        wp_enqueue_script( 'slider-popup-script' );

        // Pricing table
        wp_register_style( 'wdm-pricing-table-style', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table/style.css', [], rand(), 'all' );
        wp_register_script( 'wdm-pricing-table-script', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table/script.js', [ 'jquery','slick-script'], rand(), true );      
       
        // FAQs
        wp_register_style( 'wdm-faqs-style', MYEW_PLUGIN_URL . 'assets/faqs/style.css',  [], rand(), 'all' );
        wp_enqueue_style( 'wdm-faqs-style' );

        // Plugin statistics
        wp_register_style( 'wdm-plugin-stats-style', MYEW_PLUGIN_URL . 'assets/wdm-plugin-stats/style.css',  [], rand(), 'all' );
        wp_register_script( 'wdm-plugin-stats-script', MYEW_PLUGIN_URL . 'assets/wdm-plugin-stats/script.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'wdm-plugin-stats-style' );
        wp_enqueue_script( 'wdm-plugin-stats-script' );

         // Demoslider
         wp_register_style( 'wdm-demoslider-style', MYEW_PLUGIN_URL . 'assets/demoslider/style.css',  [], rand(), 'all' );
        //  wp_register_script( 'wdm-demoslider-script', MYEW_PLUGIN_URL . 'assets/demoslider/script.js', [ 'jquery','slick-script' ], rand(), true );
         wp_enqueue_style( 'wdm-demoslider-style' );
        //  wp_enqueue_script( 'wdm-demoslider-script' );

         //testimonials
         wp_register_style( 'wdm-testimonials-style', MYEW_PLUGIN_URL . 'assets/testimonials/style.css',  [], rand(), 'all' );
        //  wp_register_script( 'wdm-testimonials-script', MYEW_PLUGIN_URL . 'assets/testimonials/script.js', [ 'jquery','slick-script' ], rand(), true );
         wp_enqueue_style( 'wdm-testimonials-style' );
        //  wp_enqueue_script( 'wdm-testimonials-script' );

        // product slider
        wp_register_style( 'wdm-product-slider-style', MYEW_PLUGIN_URL . 'assets/product-slider/style.css',  [], rand(), 'all' );
        wp_register_script( 'wdm-product-slider-script', MYEW_PLUGIN_URL . 'assets/product-slider/script.js', [ 'jquery','slick-script' ], rand(), true );

        // Client's Speak
        wp_register_style( 'wdm-clients-speak-style', MYEW_PLUGIN_URL . 'assets/clients-speak/style.css',  [], rand(), 'all' );
        // wp_register_script( 'wdm-clients-speak-script', MYEW_PLUGIN_URL . 'assets/clients-speak/script.js', [ 'jquery','slick-script' ], rand(), true );

        // Client's High Fives
        wp_register_style( 'wdm-clients-high-fives-style', MYEW_PLUGIN_URL . 'assets/clients-high-fives/style.css',  [], rand(), 'all' );
        wp_register_script( 'wdm-clients-high-fives-script', MYEW_PLUGIN_URL . 'assets/clients-high-fives/script.js', [ 'jquery','slick-script' ], rand(), true );
        
        // Sendy Newsletter
        wp_register_style( 'sendy-newsletter-style', MYEW_PLUGIN_URL . 'assets/sendy-newsletter/style.css',  [], rand(), 'all' );
        wp_register_script( 'sendy-newsletter-script', MYEW_PLUGIN_URL . 'assets/sendy-newsletter/script.js', [ 'jquery'], rand(), true );
        wp_localize_script( 'sendy-newsletter-script', 'sendyAjax', array(  'ajaxurl' => admin_url( 'admin-ajax.php' )));

        // Cart Timer
        wp_register_style( 'cart-timer-style', MYEW_PLUGIN_URL . 'assets/cart-timer/style.css',  [], rand(), 'all' );
        // wp_register_script( 'cart-timer-script', MYEW_PLUGIN_URL . 'assets/cart-timer/script.js', [ 'jquery'], rand(), true );
        
        // SS Carousel
        wp_register_style( 'wdm-screenshot-carousel-style', MYEW_PLUGIN_URL . 'assets/screenshot-carousel/style.css',  [], rand(), 'all' );
        wp_register_script( 'wdm-screenshot-carousel-script', MYEW_PLUGIN_URL . 'assets/screenshot-carousel/script.js', [ 'jquery' ], rand(), true );
        
        // pricing table 2
        wp_register_style( 'wdm-pricing-table2-style', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table2/style.css', [], rand(), 'all' );
        wp_register_script( 'wdm-pricing-table2-script', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table2/script.js', [ 'jquery' ], rand(), true );
        wp_localize_script( 'wdm-pricing-table2-script', "free_download_ajax", array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('free-download-ajax-security')
        ));

        // Pricing Table 3
        wp_register_style( 'wdm-pricing-table3-style', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table3/style.css', [], rand(), 'all' );

        // Features screenshot
        wp_register_style( 'wisdm-swiper-style', MYEW_PLUGIN_URL . 'assets/swiper/swiper-bundle.min.css', [], rand(), 'all' );
        wp_register_script( 'wisdm-swiper-script', MYEW_PLUGIN_URL . 'assets/swiper/swiper-bundle.min.js', [ 'jquery' ], rand(), true );
	
        // Features screenshot
        wp_register_style( 'wisdm-features-screenshots-style', MYEW_PLUGIN_URL . 'assets/wisdm-features-screenshots/style.css', [], rand(), 'all' );
        wp_register_script( 'wisdm-features-screenshots-script', MYEW_PLUGIN_URL . 'assets/wisdm-features-screenshots/script.js', [ 'jquery' ], rand(), true );
     
        $this->load_pricing_tables_script();

        $this->load_slider_before_after('register_scripts_styles');
        $this->load_minimal_checkout('register_scripts_styles');
    }

    function add_elementor_widget_categories( $elements_manager ) {
        $elements_manager->add_category(
            'myew-for-elementor',
            [
                'title' => __( 'Wisdm Widgets', 'wisdm-elementor-widgets' ),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    public function edit_scripts(){ 
        $this->load_pricing_tables_script();
        
        wp_register_style( 'slick-style', MYEW_PLUGIN_URL . 'assets/slick/slick.css', [], rand(), 'all' );
        wp_register_script( 'slick-script', MYEW_PLUGIN_URL . 'assets/slick/slick.min.js', [ 'jquery' ], rand(), true );
        wp_register_script( 'slick-slider-script', MYEW_PLUGIN_URL . 'assets/slick/slider.js', [ 'jquery','slick-script' ], rand(), true );
        wp_enqueue_style( 'slick-style' );
        wp_enqueue_script( 'slick-script' );
        wp_enqueue_script( 'slick-slider-script' );

        //testimonials
        wp_register_style( 'wdm-testimonials-style', MYEW_PLUGIN_URL . 'assets/testimonials/style.css',  [], rand(), 'all' );
        // wp_register_script( 'wdm-testimonials-script', MYEW_PLUGIN_URL . 'assets/testimonials/script.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'wdm-testimonials-style' );
        // wp_enqueue_script( 'wdm-testimonials-script' );

        // product slider
        wp_register_style( 'wdm-product-slider-style', MYEW_PLUGIN_URL . 'assets/product-slider/style.css',  [], rand(), 'all' );
        wp_register_script( 'wdm-product-slider-script', MYEW_PLUGIN_URL . 'assets/product-slider/script.js', [ 'jquery','slick-script' ], rand(), true );
        wp_enqueue_style( 'wdm-product-slider-style' );
        wp_enqueue_script( 'wdm-product-slider-script' );

        // Client's Speak
        wp_register_style( 'wdm-clients-speak-style', MYEW_PLUGIN_URL . 'assets/clients-speak/style.css',  [], rand(), 'all' );
        // wp_register_script( 'wdm-clients-speak-script', MYEW_PLUGIN_URL . 'assets/clients-speak/script.js', [ 'jquery','slick-script' ], rand(), true );
        wp_enqueue_style( 'wdm-clients-speak-style' );
        wp_enqueue_script( 'wdm-clients-speak-script' );

        // Client's High Fives
        wp_register_style( 'wdm-clients-high-fives-style', MYEW_PLUGIN_URL . 'assets/clients-high-fives/style.css',  [], rand(), 'all' );
        wp_register_script( 'wdm-clients-high-fives-script', MYEW_PLUGIN_URL . 'assets/clients-high-fives/script.js', [ 'jquery','slick-script' ], rand(), true );
        wp_enqueue_style( 'wdm-clients-high-fives-style' );
        wp_enqueue_script( 'wdm-clients-high-fives-script' );

         // Sendy Newsletter
         wp_register_style( 'sendy-newsletter-style', MYEW_PLUGIN_URL . 'assets/sendy-newsletter/style.css',  [], rand(), 'all' );
         wp_register_script( 'sendy-newsletter-script', MYEW_PLUGIN_URL . 'assets/sendy-newsletter/script.js', [ 'jquery'], rand(), true );
         wp_enqueue_style( 'sendy-newsletter-style' );
         wp_enqueue_script( 'sendy-newsletter-script' );

        // Cart Timer
        wp_register_style( 'cart-timer-style', MYEW_PLUGIN_URL . 'assets/cart-timer/style.css',  [], rand(), 'all' );
        // wp_register_script( 'cart-timer-script', MYEW_PLUGIN_URL . 'assets/cart-timer/script.js', [ 'jquery'], rand(), true );
        wp_enqueue_style( 'cart-timer-style' );
        wp_enqueue_script( 'cart-timer-script' );


        // SS Carousel
        wp_register_style( 'wdm-screenshot-carousel-style', MYEW_PLUGIN_URL . 'assets/screenshot-carousel/style.css',  [], rand(), 'all' );
        wp_register_script( 'wdm-screenshot-carousel-script', MYEW_PLUGIN_URL . 'assets/screenshot-carousel/script.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'wdm-screenshot-carousel-style' );
        wp_enqueue_script( 'wdm-screenshot-carousel-script' );

        // Features screenshot
        wp_register_style( 'wisdm-features-screenshots-style', MYEW_PLUGIN_URL . 'assets/wisdm-features-screenshots/style.css', [], rand(), 'all' );
        wp_register_script( 'wisdm-features-screenshots-script', MYEW_PLUGIN_URL . 'assets/wisdm-features-screenshots/script.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'wisdm-features-screenshots-style' );
        wp_enqueue_script( 'wisdm-features-screenshots-script' );

        // Features screenshot
        wp_register_style( 'wisdm-swiper-style', MYEW_PLUGIN_URL . 'assets/swiper/swiper-bundle.min.css', [], rand(), 'all' );
        wp_register_script( 'wisdm-swiper-script', MYEW_PLUGIN_URL . 'assets/swiper/swiper-bundle.min.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'wisdm-swiper-style' );
        wp_enqueue_script( 'wisdm-swiper-script' );
        // Slider Before After
        $this->load_slider_before_after('register_scripts_styles');
        $this->load_slider_before_after('enqueue_scripts_styles');
        // Minimal Checkout
        $this->load_minimal_checkout('register_scripts_styles');
        $this->load_minimal_checkout('enqueue_scripts_styles');
    }

    /**
     * Enqueue the styles and scripts on backend Elementor editor only.
     */
    public function enqueue_scripts_on_editor_only() {
        // Pricing Table 2
        wp_register_style( 'wdm-pricing-table2-style', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table2/style.css', [], rand(), 'all' );
        wp_register_script( 'wdm-pricing-table2-script', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table2/script.js', [ 'jquery' ], rand(), true );
        wp_enqueue_style( 'wdm-pricing-table2-style' );
        wp_enqueue_script( 'wdm-pricing-table2-script' );

        // Pricing Table 3 style
        wp_register_style( 'wdm-pricing-table3-style', MYEW_PLUGIN_URL . 'assets/wdm-pricing-table3/style.css', [], rand(), 'all' );
        wp_enqueue_style( 'wdm-pricing-table3-style' );
    }

    /**
    * Load Text Domain
    * @since 1.0.0
    */
    public function i18n() {
       load_plugin_textdomain( 'wisdm-elementor-widgets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
    * Initialize the plugin
    * @since 1.0.0
    */
    public function init() {
        add_action( 'wp_footer' , function(){
            ?>
             <div class="feature-box-modal" id="feature-box-modal" >
                <div class="modal-content">
                    <div class="wdm-fb-controls">
                        <span class="close">&times;</span>
                    </div>
                    <div class="wdm-lightbox-wrap">
                        <div class="wdm-lb-content">
                            <h4 class="wdm-lb-title"></h4>
                            <h4 class="wdm-lb-subtitle"></h4>
                            <div class="wdm-lb-para"></div>
                        </div>
                        <div class="wdm-lb-screenshot">
                            <img class="wdm-lb-screenshot-img" src="" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <?php
        });
        // Check if the ELementor installed and activated
        if( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
            return;
        }

        if( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return;
        }

        if( ! version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return;
        }

        add_action( 'elementor/init', [ $this, 'init_category' ] );
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
    }

    /**
    * Init Widgets
    * @since 1.0.0
    */
    public function init_widgets() {
        require_once MYEW_PLUGIN_PATH . '/widgets/preview-card.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/pricing-table.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/logo-carousel.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/sneakpeek.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/adv-features.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/wisdm-pricing-table.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/wisdm-faqs.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/plugin-statistics.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/demoslider.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/testimonials.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/product-slider.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/clients-speak.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/clients-high-fives.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/sendy-newsletter.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/cart-timer.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/edd-upgrades.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/screenshot-carousel.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/slider-before-after.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/wisdm-pricing-table2.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/wisdm-pricing-table3.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/wisdm-features-screenshots.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/wisdm-feature-box.php';

        $this->load_slider_before_after('widget');
        $this->load_minimal_checkout('widget');
    }


    public function load_slider_before_after($option){
        switch($option){
            case 'widget':
                require_once MYEW_PLUGIN_PATH . '/widgets/slider-before-after.php';
                break;
            case 'register_scripts_styles':
                wp_register_style( 'slider-before-after-style', MYEW_PLUGIN_URL . 'assets/slider-before-after/style.css',  [], rand(), 'all' );
                wp_register_script( 'slider-before-after-script', MYEW_PLUGIN_URL . 'assets/slider-before-after/script.js', [ 'jquery'], rand(), true );
                break;
            case 'enqueue_scripts_styles':
                wp_enqueue_style( 'slider-before-after-style' );
                wp_enqueue_script( 'slider-before-after-script' );
                break;
            default:break;
        }
    }

    public function load_minimal_checkout($option){
        switch($option){
            case 'widget':
                require_once MYEW_PLUGIN_PATH . '/widgets/edw-minimal-checkout.php';
                break;
            case 'register_scripts_styles':
                wp_deregister_script( 'edd-ajax' );
                wp_deregister_script( 'minimal-checkout-edd-script' );
                wp_dequeue_script( 'wdm-edd-checkout-more-option' );

                wp_register_style( 'minimal-checkout-style', MYEW_PLUGIN_URL . 'assets/minimal-checkout/style.css',  [], rand(), 'all' );
                wp_register_script( 'minimal-checkout-script', MYEW_PLUGIN_URL . 'assets/minimal-checkout/script.js', [ 'jquery'], rand(), true );
                wp_register_script( 'edd-ajax', MYEW_PLUGIN_URL . 'assets/minimal-checkout/edd-ajax.js', [ 'jquery'], rand(), true );
                
                wp_localize_script( 'edd-ajax', 'edd_scripts', apply_filters( 'edd_ajax_script_vars', array(
                    'ajaxurl'                 => edd_get_ajax_url(),
                    'position_in_cart'        => isset( $position ) ? $position : -1,
                    'has_purchase_links'      => @$has_purchase_links,
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
                    'edd_paypal_img'		  => get_stylesheet_directory_uri().'/images/paypal-icon.png'
                ) ) );

                wp_localize_script( 'minimal-checkout-script', 'wdmCheckoutAjax', array( 'decimal_separator' => edd_get_option( 'decimal_separator', '.' ),'currency_sign'=> edd_currency_filter(''),'enter_discount'=>'Enter discount', 'asset_path' => WDMELE_PLUGIN_URL.'assets', 'ajaxurl' => admin_url( 'admin-ajax.php' )));

                break;
            case 'enqueue_scripts_styles':
                wp_enqueue_style( 'minimal-checkout-style' );
                wp_enqueue_script( 'minimal-checkout-script' );
                wp_enqueue_script( 'edd-ajax' );
                break;
            default:break;
        }
    }
    public function load_dependencies(){
        require_once MYEW_PLUGIN_PATH . '/widgets/common-controls/slick-slider.php';
        require_once MYEW_PLUGIN_PATH . '/widgets/common-controls/styles.php';
        require_once MYEW_PLUGIN_PATH . '/includes/class-ajax-handler.php';
        require_once MYEW_PLUGIN_PATH . '/includes/class-edd.php';
        require_once MYEW_PLUGIN_PATH . '/includes/class-edd-checkout.php';
    }

    /**
    * Init Category Section
    * @since 1.0.0
    */
    public function init_category() {
        Elementor\Plugin::instance()->elements_manager->add_category(
            'myew-for-elementor',
            [
                'title' => 'My Elementor Widgets'
            ],
            1
        );
    }

    /**
    * Admin Notice
    * Warning when the site doesn't have Elementor installed or activated
    * @since 1.0.0
    */
    public function admin_notice_missing_main_plugin() {
        if( isset( $_GET[ 'activate' ] ) ) unset( $_GET[ 'activate' ] );
        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated', 'wisdm-elementor-widgets' ),
            '<strong>'.esc_html__( 'My Elementor Widget', 'wisdm-elementor-widgets' ).'</strong>',
            '<strong>'.esc_html__( 'Elementor', 'wisdm-elementor-widgets' ).'</strong>'
        );
        printf( '<div class="notice notice-warning is-dimissible"><p>%1$s</p></div>', $message );
    }

    /**
    * Admin Notice
    * Warning when the site doesn't have a minimum required Elementor version.
    * @since 1.0.0
    */
    public function admin_notice_minimum_elementor_version() {
        if( isset( $_GET[ 'activate' ] ) ) unset( $_GET[ 'activate' ] );
        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater', 'wisdm-elementor-widgets' ),
            '<strong>'.esc_html__( 'My Elementor Widget', 'wisdm-elementor-widgets' ).'</strong>',
            '<strong>'.esc_html__( 'Elementor', 'wisdm-elementor-widgets' ).'</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf( '<div class="notice notice-warning is-dimissible"><p>%1$s</p></div>', $message );
    }

    /**
    * Admin Notice
    * Warning when the site doesn't have a minimum required PHP version.
    * @since 1.0.0
    */
    public function admin_notice_minimum_php_version() {
        if( isset( $_GET[ 'activate' ] ) ) unset( $_GET[ 'activate' ] );
        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater', 'wisdm-elementor-widgets' ),
            '<strong>'.esc_html__( 'My Elementor Widget', 'wisdm-elementor-widgets' ).'</strong>',
            '<strong>'.esc_html__( 'PHP', 'wisdm-elementor-widgets' ).'</strong>',
            self::MINIMUM_PHP_VERSION
        );
        printf( '<div class="notice notice-warning is-dimissible"><p>%1$s</p></div>', $message );
    }

}

MY_Elementor_Widget::instance();
