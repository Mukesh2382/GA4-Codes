<?php
/*
Plugin Name: WDM GA4 Edwiser
Plugin URL: https://wisdmlabs.com/
Description: A custom plugin which implements Google Analytics 4 Tracking on Edwwiser Site
Version: 1.0.0
Author: Wisdmlabs
Author URI: https://wisdmlabs.com
Contributors: wisdmlabs
*/

add_action('wp' ,'add_config_code');
function add_config_code(){
    wp_enqueue_script(
        'wdm_ga4_config_code',
        plugin_dir_url(__FILE__)  . 'assets/ga4_config_code.js',
        array(  ),
    );
}

add_action('wp_body_open' , 'add_ga4_code_to_head');
function add_ga4_code_to_head(){
    echo '<!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5VJSDL5"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->';
}


require_once 'class-ga4-ecommerce.php';
require_once 'class-ga4-buy-now-click.php';
require_once 'class-ga4-all-products-page.php';
require_once 'class-ga4-select-item.php';
require_once 'class-ga4-ecommerce-checkout-page-event.php';
require_once 'class-ga4-ecommerce-thank-you-page.php';
require_once 'class-ga4-ecommerce-remove-from-cart.php';