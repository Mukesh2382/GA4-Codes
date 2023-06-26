<?php
/**
 * Plugin Name: WDM Elementor
 * Description: Custom Elementor Widgets by WisdmLabs.
 * Plugin URI:  https://wisdmlabs.com/
 * Version:     1.0.0
 * Author:      Tariq Kotwal
 * Author URI:  https://wisdmlabs.com/
 * Text Domain: wdm-elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Defining plugin constants.
 *
 * @since 3.0.0
 */
define('WDMELE_PLUGIN_FILE', __FILE__);
define('WDMELE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WDMELE_PLUGIN_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('WDMELE_PLUGIN_URL', trailingslashit(plugins_url('/', __FILE__)));
define('WDMELE_PLUGIN_VERSION', '1.0.0');

final class Wdm_Elementor_Widget_Extension {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Wdm_Elementor_Widget_Extension The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Wdm_Elementor_Widget_Extension An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'i18n' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 9 );

		// add_action( 'plugins_loaded', 'edd_add_to_cart_redirect' );
		add_action( 'plugins_loaded', array( $this, 'edd_post_add_to_cart_hook' ), 10 );
		// Make elementor checkout page a valid edd checkout page
		add_filter( 'edd_is_checkout', array( $this, 'edd_is_checkout' ), 999 );
	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'wdm-elementor-addon-extension' );

	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_missing_main_plugin' ) );
			return;
		}

		// Add Plugin actions
		// add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );
		add_action('elementor/elements/categories_registered', array($this, 'register_widget_categories'));
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
		
		
		// Enqueue
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('elementor/frontend/before_enqueue_scripts', array($this, 'generate_frontend_scripts'));
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'editor_enqueue_scripts'));
		add_action( 'elementor/element/print_template', array( $this, 'wdm_template_javascript'), 10, 2);
		
		// add_action( 'media_view_settings', array( $this, 'add_media_tab'));
		// add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'wdm_media_lib_uploader_enqueue'));

		// add_action( 'elementor/preview/enqueue_styles', array( $this, 'preview_editor_enqueue_scripts') );		
		$this->loadLibrary();
		
	}

	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'wdm-elementor-addon-extension' ),
			'<strong>' . esc_html__( 'Elementor Wisdm Addon Extension', 'wdm-elementor-addon-extension' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'wdm-elementor-addon-extension' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	public function register_widget_categories($elements_manager)
    {
        $elements_manager->add_category(
            'wdm-elementor-addon-extension',
            [
                'title' => __('Wisdm Addons', 'wdm-elementor-addon-extension'),
                'icon' => 'font',
            ], 1);
    }


	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_widgets() {
		// Cart Widget
		require_once( __DIR__ . '/widgets/class-wdm-cart.php' );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \WdmCart() );

		// Cart Discount
		require_once( __DIR__ . '/widgets/class-wdm-cart-discount.php' );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \WdmCartDiscount() );

		// Cart Related Products
		require_once( __DIR__ . '/widgets/class-wdm-cart-related-products.php' );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \WdmCartRelatedProducts() );

		// Checkout Login Field
		// require_once( __DIR__ . '/widgets/class-wdm-checkout-login.php' );
		// \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \WdmCheckoutLogin() );

		// Minimal Checkout
		require_once( __DIR__ . '/widgets/class-wdm-minimal-checkout.php' );
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \WdmMinimalCheckout() );
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function enqueue_scripts() {
		
	}
	public function admin_enqueue_scripts() {
		
	}
	public function editor_enqueue_scripts() {
		wp_enqueue_style('wdm-style-handle-cart');
		wp_enqueue_script('wdm-script-handle-cart');

		wp_enqueue_style('wdm-style-handle-discount');
		wp_enqueue_script('wdm-script-handle-discount');

		wp_enqueue_style('wdm-style-handle-related-prods');
		wp_enqueue_script('wdm-script-handle-related-prods');

		wp_enqueue_style('wdm-style-handle-login');
		wp_enqueue_script('wdm-script-handle-login');
		
		// wp_enqueue_style( 'ftlaicon-lms-addon', FTLMSA_PLUGIN_URL.'assets/css/ftlaicon.css', false );
		// wp_enqueue_script( 'flip-card-lms-addon-editor', FTLMSA_PLUGIN_URL . 'assets/js/flip-card-lms-addon-editor.js', false );
	}
	public function generate_frontend_scripts() {
		// wp_enqueue_style( 'flip-card-lms-addon', FTLMSA_PLUGIN_URL.'assets/css/flip-card-lms-addon.css', false );
		// wp_enqueue_script( 'flip-card-lms-addoeditor_enqueue_scriptsn', FTLMSA_PLUGIN_URL . 'assets/js/flip-card-lms-addon.js', false );
	}

	public function edd_is_checkout($is_checkout) {
		global $wp;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		$set_checkout = '';
		$set_minimal_checkout = '';
	
		global $wp_query;
	
		$is_object_set    = isset( $wp_query->queried_object );
		$is_object_id_set = isset( $wp_query->queried_object_id );
		$is_checkout      = is_page( edd_get_option( 'purchase_page' ) );
	
		if( ! $is_object_set ) {
			unset( $wp_query->queried_object );
		} else if ( is_singular() ) {
			$content = $wp_query->queried_object->post_content;
		}
	
		if( ! $is_object_id_set ) {
			unset( $wp_query->queried_object_id );
		}

		if(function_exists('get_field')){
            if(!empty(get_field('wisdm_elementor_checkout_page','option'))){
				$set_checkout = get_field('wisdm_elementor_checkout_page','option');
			}
		}

		global $wp_rewrite;

		if(null != $wp_rewrite && function_exists('get_field')){
            if(!empty(get_field('wisdm_minimal_elementor_checkout_page','option'))){
				$set_minimal_checkout = get_field('wisdm_minimal_elementor_checkout_page','option');
			}
		}

		// If we know this isn't the primary checkout page, check other methods.
		if ( 
				! $is_checkout && isset( $content ) && 
				(
					has_shortcode( $content, 'download_checkout' ) ||
					has_shortcode( $content, 'wdm_download_checkout' ) ||
					has_shortcode( $content, 'wdm_minimal_download_checkout' ) ||
					trailingslashit($set_checkout)==trailingslashit($current_url) ||
					trailingslashit($set_minimal_checkout)==trailingslashit($current_url)
				)
			) {
			$is_checkout = true;
		}

		return $is_checkout;
	}

	public function edd_post_add_to_cart_hook(){
		add_action( 'edd_post_add_to_cart', array( $this, 'edd_post_add_to_cart' ), 10, 2 );
	}

	public function edd_post_add_to_cart($download_id, $options){
		// only run when ajax is not enabled
		if ( edd_is_ajax_enabled() )
		return;

		$redirect = edd_get_checkout_uri();
		if(function_exists('get_field')){
            if(!empty(get_field('wisdm_elementor_cart_page','option'))){
				$redirect = get_field('wisdm_elementor_cart_page','option');
			}
		}

		if ( $redirect ) {
			wp_redirect( $redirect ); exit;
		}
	}

	public function loadLibrary() {
		// Including stuff for Library
		if(!class_exists('Base')){
			require_once ( WDMELE_PLUGIN_PATH . 'library/inc/class-base.php' );
		}
		if(!class_exists('Elementor')){
			require_once ( WDMELE_PLUGIN_PATH . 'library/inc/class-elementor.php' );
		}

		// Utility Class
		// require_once( WDMELE_PLUGIN_PATH . 'library/inc/class-utility.php');
		// $utility = WdmElementorUtility::getInstance();

		// Minimal checkout Utility Class
		require_once( WDMELE_PLUGIN_PATH . 'library/inc/class-minimal-utility.php');
		$minimal_utility = WdmElementorMinimalUtility::getInstance();
		
		// Ajax handler
		require_once( WDMELE_PLUGIN_PATH . 'library/inc/class-ajax-handler.php');
		$ajax_handler = WdmElementorAjaxHandler::getInstance();
		
	}
}
Wdm_Elementor_Widget_Extension::instance();
