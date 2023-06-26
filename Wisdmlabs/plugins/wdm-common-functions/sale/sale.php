<?php
namespace WDMCommonFunctions;

/**
* Class to handle sale related functionalities.
*/

class WdmSale
{
    // To store current class object
    private static $instance;

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new WdmSale;
        }
        return self::$instance;
    }

    /**
     * __construct
     *
     * @return void
     */
    private function __construct()
    {
        add_shortcode('wdm_upgrade_text_my_account', array($this, 'wdm_upgrade_text_my_account_exec_shortcode'));
        add_filter('wdm_upgrades_col_head_name_myac', array($this, 'alter_upgrades_col_head_name_myac'));
        add_filter('wdm_upgrade_cost_col_head_name', array($this, 'alter_upgrade_cost_col_head_name'));
        add_filter('wdm_should_auto_apply_discount_code_on_upgrades', array($this, 'should_auto_apply_discount_code_on_upgrades'));
        add_filter('wdm_upgrade_cost_on_upgrade_page',  array($this, 'alter_upgrade_cost_on_upgrade_page'), 10, 3);
        add_filter('wdm_sale_strip_banner_content', array($this, 'wdm_sale_strip_banner_content'));
        add_filter('wdm_discount_percentage_ribbons_archive_page', array($this, 'discount_percentage_ribbons_archive_page'));
        add_filter('wdm_show_free_trials_section', array($this, 'show_free_trials_section'));
        add_filter('wdm_show_free_trial_exit_intent_popup_products_lp', array($this, 'show_free_trial_exit_intent_popup_products_lp'));
        add_filter('wdm_product_data_fetched_from_json', array($this, 'use_price_sale_json_file'), 10, 3);
        add_filter('wdm_pricing_table_image_content', array($this, 'pricing_table_image_content'));
        add_action('wdm_ps_reports_page_before_title', array($this, 'display_bfcm_banner_for_reports'));
        add_filter('wdm_you_save_price_html', array($this, 'modify_you_save_price_html'), 10, 4);
        add_filter('edd_get_cart_total', array($this, 'round_up_cart_total_price_checkout'));
        add_filter('wdm_checkout_page_discount_applied_rate', array($this, 'show_discount_code_instead_of_rate'), 15, 2);
        add_action('plt_enqueues_scripts', array($this, 'enqueue_custom_sale_css'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_sale_css'));

        // Service pages graphics
        // add_action('wdm_sale_render_service_page_graphic', array($this, 'render_service_page_graphic'), 10, 1);
        // End of Service pages graphics

        // For BFCM - Spin The Wheel - Lucky Winners Discount Code (GRABFAST100).
        add_action( 'init', array( $this, 'wdm_remove_spin_winner_edd_discount_code' ) );
        add_action( 'edd_is_discount_valid', array( $this, 'wdm_is_spin_winner_edd_discount_code_valid' ), 15, 3 );
        add_filter('edd_ajax_discount_response', array($this, 'wdm_return_spin_winner_edd_discount_code_invalid_msg'));

        // For BFCM - Spin The Wheel - Copy Coupon Code Script.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_spin_the_wheel_copy_coupon_code_script' ), 15 );
    }

    /**
     * Callback to action 'wdm_sale_render_service_page_graphic'.
     */
    public function render_service_page_graphic($page_name) {
        if ( !WdmSale::is_sale_live() ) {
            return;
        }

        $graphic_url = '';
        switch ($page_name) {
            case 'learndash_service':
                $graphic_url = 'https://wisdmlabs.com/site/wp-content/uploads/2021/11/bfcm-ld-service-pg-graphic-3.png';
                break;
            case 'woocommerce_service':
                $graphic_url = 'https://wisdmlabs.com/site/wp-content/uploads/2021/11/bfcm-wc-service-page-graphic-3.png';
                break;
            case 'wordpress_service':
                $graphic_url = 'https://wisdmlabs.com/site/wp-content/uploads/2021/11/bfcm-wp-service-pg-graphic-3.png';
                break;
            default:
                $graphic_url = '';
        }

        if (empty($graphic_url)) {
            return;
        }

        ?>
        <div class="sale-graphic-wrapper sale-graphic-hidden-phone">
            <span>
                <img src="<?php echo esc_url($graphic_url); ?>" alt="BFCM Sale Offer Graphic" class="sale-graphic">
            </span>
        </div>
        <?php
    }

    /**
     * Show the upgrade text on the MyAccount page.
     *
     * Display the upgrade text on the MyAccount page added in the
     * ACF field setting 'Upgrade Text on MyAccount Page'.
     *
     * @return void
     */
    public function wdm_upgrade_text_my_account_exec_shortcode() {
        if (!is_user_logged_in() || !WdmSale::is_sale_live()) {
            return;
        }

        $upgrade_text = get_field('upgrade_text_on_myaccount_page','option');
        // echo wp_kses_post($upgrade_text);
        echo ($upgrade_text);
    }

    /**
     * Return the 'Upgrades' column head name on the MyAccount page.
     *
     * Callback to filter 'wdm_upgrades_col_head_name_myac'.
     *
     * @return  string  Modified name for the upgrade column head.
     */
    function alter_upgrades_col_head_name_myac($col_head_name) {
        if (WdmSale::is_sale_live() && function_exists('get_field') && !empty(get_field('upgrades_column_head_name_on_my_account_page','option'))) {
            $col_head_name = get_field('upgrades_column_head_name_on_my_account_page','option');
        }

        return $col_head_name;
    }

    /**
     * Return the modified name for 'Upgrade Cost' column on Upgrade page.
     *
     * Callback to filter 'wdm_upgrade_cost_col_head_name'.
     *
     * @return  string  Modified name for the Upgrade Cost column head.
     */
    function alter_upgrade_cost_col_head_name($col_head_name) {
        if (WdmSale::is_sale_live() && function_exists('get_field') && !empty(get_field('upgrade_cost_column_head_name_on_upgrade_page','option'))) {
            $col_head_name = get_field('upgrade_cost_column_head_name_on_upgrade_page','option');
        }

        return $col_head_name;
    }

    /**
     * Decide whether coupon code should be auto applied or not for upgrades.
     *
     * Callback to filter 'wdm_should_auto_apply_discount_code_on_upgrades'.
     *
     * @return  bool  Return true if coupon should be auto applied for upgrades, false
     *                otherwise.
     */
    function should_auto_apply_discount_code_on_upgrades($auto_apply = false) {
        if (WdmSale::is_sale_live()) {
            $auto_apply = true;
        }

        return $auto_apply;
    }

    /**
     * Modify the upgrade cost in the Uprage Cost column on the Upgrade page.
     * Strikethrough the upgrade cost and show the 30% discounted sale price beside it.
     *
     * Callback to filter 'wdm_upgrade_cost_on_upgrade_page'.
     *
     * @return  string  Return HTML content for the upgrade cost table data.
     */
    function alter_upgrade_cost_on_upgrade_page($upgrade_cost, $license_id, $upgrade_id) {
        if (WdmSale::is_sale_live() && $this->should_auto_apply_discount_code_on_upgrades()) {
            $og_upgrade_price = edd_sanitize_amount( edd_sl_get_license_upgrade_cost( $license_id, $upgrade_id ) );
            // 30% discount on upgrade cost
            // $sale_upgrade_price = $og_upgrade_price - ($og_upgrade_price * 0.3);
            $sale_upgrade_price = $og_upgrade_price - ($og_upgrade_price * 0.26);
            $sale_upgrade_price = ceil($sale_upgrade_price);
            $sale_upgrade_price = edd_sanitize_amount($sale_upgrade_price);

            $upgrade_cost_html = '<s>' . edd_currency_filter($og_upgrade_price) . '</s> <span><strong>' . edd_currency_filter($sale_upgrade_price) . '</strong></span>';
            return $upgrade_cost_html;
        }

        return $upgrade_cost;
    }

    /**
     * Callback function to filter 'wdm_sale_strip_banner_content'.
     *
     */
    public function wdm_sale_strip_banner_content($content) {
        // if ( empty(get_field('show_sale_strip','option')) || !WdmSale::is_sale_live() ) {
	if ( empty(get_field('show_sale_strip','option'))  ) {
            return $content;
        }


        $should_show_strip_on_blogs_only = empty(get_field('show_strip_only_on_blogs','option')) ? false : get_field('show_strip_only_on_blogs','option');
        $strip_text = get_field('text_on_strip','option'); // Fetch the strip text.
        $strip_css  = get_field('strip_css','option'); // Fetch the strip CSS.

        // If 'Show Strip Only On Blogs' is enabled and current page is not single post, then return.
        if ( $should_show_strip_on_blogs_only && ! is_single() ) {
            return $content;
        }

        // Fetch strip left-right images
        $strip_left_img_url    = empty(get_field('show_left_strip_image','option')) ? '' : get_field('show_left_strip_image','option');
        $strip_right_img_url   = empty(get_field('show_right_strip_image','option')) ? '' : get_field('show_right_strip_image','option');
        $strip_right_img_class = '';
        $strip_right_img_class = 'site-wide-banner-graphic-right';
        $strip_left_img_html   = empty( $strip_left_img_url ) ? '' : '<img src="' . esc_url($strip_left_img_url) . '" data-src="' . esc_url($strip_left_img_url) . '" class="site-wide-banner-graphic-left" alt="site-wide-banner-graphic" style="" data-was-processed="true">';
        $strip_right_img_html  = empty( $strip_right_img_url ) ? '' : '<img src="' . esc_url($strip_right_img_url) . '" data-src="' . esc_url($strip_right_img_url) . '" class="' . esc_attr($strip_right_img_class) . '" alt="site-wide-banner-graphic" style="" data-was-processed="true">';

        if (empty($strip_right_img_url)) {
            $strip_right_img_url   = $strip_right_img_url;
            $strip_right_img_class = 'site-wide-banner-graphic-right';
        }

        $content = '<div class="distitlediv" style=""> ' . $strip_left_img_html . wp_kses_post($strip_text) . $strip_right_img_html . ' </div> <style>' . esc_html($strip_css) . '</style>';

        // $content = '<div class="distitlediv" style=""> <img src="' . esc_url($strip_left_img_url) . '" data-src="' . esc_url($strip_left_img_url) . '" class="site-wide-banner-graphic-left" alt="site-wide-banner-graphic" style="" data-was-processed="true"> <noscript style=""><img src="' . esc_url($strip_left_img_url) . '" class="site-wide-banner-graphic-left" alt="site-wide-banner-graphic" style="float: left;height: 35px;"></noscript> ' . wp_kses_post($strip_text) . '<img src="' . esc_url($strip_right_img_url) . '" data-src="' . esc_url($strip_right_img_url) . '" class="' . esc_attr($strip_right_img_class) . '" alt="site-wide-banner-graphic" style="" data-was-processed="true"> <noscript style=""><img src="' . $strip_right_img_url . '" class="' . esc_attr($strip_right_img_class) . '" alt="site-wide-banner-graphic" style="float: right;height: 35px;"></noscript></div><style>' . esc_html($strip_css) . '</style>';

        $content = trim($content);

        return $content;
    }

    /**
     * Modify pricing table image content.
     */
    public function pricing_table_image_content($content) {
        if (WdmSale::is_sale_live() && function_exists('get_field') && !empty(get_field('show_sale_pricing_styling','option'))) {
            if (empty(get_field('use_one_center_image_on_pricing_table','option'))) {
                // Use two images
                // Fetch left-right images
                $left_img_url = empty(get_field('show_left_image_pricing_table','option')) ? '' : get_field('show_left_image_pricing_table','option');
                $right_img_url = empty(get_field('show_right_image_pricing_table','option')) ? '' : get_field('show_right_image_pricing_table','option');
                $right_img_class = '';

                if (empty($right_img_url)) {
                    $right_img_url   = $left_img_url;
                    $right_img_class = 'right-bg-img';
                }
                
                $text   = get_field('pricing_table_text_between_images','option');
                // $content = '<div class="bf_price_f_col"><span class="left-bg-img"><img src="https://wisdmlabs.com/site/wp-content/uploads/2021/05/may-spring.png"></span><span class="bf_price_f_text">WISDM</span> <span class="bf_price_s_text">Spring Sale</span><span class="right-bg-img"><img src="https://wisdmlabs.com/site/wp-content/uploads/2021/05/may-spring.png"></span></div>';
                $content = '<div class="bf_price_f_col"><span class="left-bg-img"><img src="' . esc_url($left_img_url) . '"></span>' . wp_kses_post($text) . '<span class="' . esc_attr($right_img_class) . '"><img src="' . esc_url($right_img_url) . '"></span></div>';
            } else {
                // Use one center image
                $price_table_image = get_field('pricing_table_center_image','option');
                $content           = '<div class="bf_price_f_col"><img loading="lazy" src="' . esc_url($price_table_image) . '" alt=""></div>';
            }
        } else {
            $content = '';
        }

        return $content;
    }

    /**
     * Display the BFCM banner for the Reports like pricing table.
     *
     * Callback to action 'wdm_pricing_table_image_content'.
     */
    public function display_bfcm_banner_for_reports() {
        if (WdmSale::is_sale_live() && function_exists('get_field') && !empty(get_field('show_sale_pricing_styling','option'))) {
            ?>
            <div class="wdm-ps-box-reports-pricing-bfcm-tag">
                <img src="https://wisdmlabs.com/site/wp-content/uploads/2022/11/bfcm-tag.png" />
            </div>
            <?php
        }
    }

    /**
     * Modify the 'You Save' text on the pricing table when sale is live.
     *
     * Callback to filter 'wdm_you_save_price_html'.
     */
    public function modify_you_save_price_html($you_save_price_html, $save_price_yr, $save_price_lyf, $data = array()) {
        if (WdmSale::is_sale_live()) {
            // Old BFCM Code
            // $you_save_price_html = '<span class="ups-toggle-txt" data-toggle-txt="' . round($save_price_lyf) . '">' . round($save_price_yr) . '</span>% OFF';

            // New BFCM Code
            $annual_you_save_amount   = empty($data['sale_price_yr']) || empty($data['regular_price_yr']) ? '' : $data['regular_price_yr'] - $data['sale_price_yr'];
            $lifetime_you_save_amount = empty($data['sale_price_lyf']) || empty($data['regular_price_lyf']) ? '' : $data['regular_price_lyf'] - $data['sale_price_lyf'];
            $annual_you_save_text     = empty($annual_you_save_amount) ? '' : 'You Save: $' . $annual_you_save_amount;
            $lifetime_you_save_text   = empty($lifetime_you_save_amount) ? '' : 'You Save: $' . $lifetime_you_save_amount;
            $you_save_price_html      = '<span class="ups-toggle-txt" data-toggle-txt="' . $lifetime_you_save_text . '">' . $annual_you_save_text . '</span>';
        }

        return $you_save_price_html;
    }

    /**
     * Round up the cart total price on the checkout page.
     *
     * Callback to filter 'edd_get_cart_total'.
     */
    public function round_up_cart_total_price_checkout($total) {
        // if (WdmSale::is_sale_live()) {
	    	return ceil($total);
        // }
        // return $total;
    }

    /**
     * Show the BFCM discount code instead of percentage amount.
     *
     * Callback to filter 'wdm_checkout_page_discount_applied_rate'.
     */
    public function show_discount_code_instead_of_rate($rate, $discount_code) {
        if ( WdmSale::is_sale_live() && 'wisdmbfcm22' == strtolower($discount_code) ) {
            return $discount_code;
        }
        return $rate;
    }


    /**
     * Enqueue sale CSS required when sale is live.
     * It will be loaded on all pages.
     */
    public function enqueue_custom_sale_css() {
        if (WdmSale::is_sale_live() && function_exists('get_field') && !empty(get_field('show_sale_pricing_styling','option'))) {
            $styleUri = get_stylesheet_directory_uri();
            $src = '/css/pricing-section-sales.css';
            wp_enqueue_style('plt-sale-page-css', $styleUri . $src);
        }
    }

    /**
     * Check whether the sale is live using the sale_ends_time.
     *
     * @return  bool  Returns true if sale is still live, false otherwise.
     */
    public static function is_sale_live() {
        if ( ! function_exists('get_field') ) {
            return false;            
        }

        // Fetch sales end time.
        $sales_end_time = get_field('sale_ends_time','option');
    
        // Fetch America/Los_Angeles current time
        $timezone       = new \DateTimeZone( 'America/Los_Angeles' );
        $current_time   = new \DateTimeImmutable( 'now', $timezone );
        $current_time   = $current_time->format( 'Y-m-d h:i:s a' );

        if (strtotime($current_time) > strtotime($sales_end_time)) {
            // current time is greater than sales end time.
            return false;
        } else {
            // current time is lesser than sales end time.
            return true;
        }
    }

    /**
     * Callback to filter 'wdm_discount_percentage_ribbons_archive_page'.
     */
    public function discount_percentage_ribbons_archive_page($best_seller) {
        $show_discount_percentage_ribbon = empty(get_field('show_archive_page_discount_percentage_ribbons', 'option')) ? false : true;

        if (WdmSale::is_sale_live() && $show_discount_percentage_ribbon) {
            global $post;
            $single_pro_list = array('300409','339999','310598','339261','427678','427772','383494','427762','341320', '715524', '671220');
            $bundle_pro_list = array('479042','462206');

            $discount_text = '';

            //BFCM SALE Ribbon Text
            $wdm_product_ribbon_text = [
                '479042'    => '70% Off', //LEAP
                '715524'    =>  'On Sale',
                '339999'    => 'On Sale', //rrf
                '671220'    =>  'Bestseller',
                '339261'    =>  'On Sale', //GR
                '427678'    =>  'On Sale', //pe
                '383494'    =>  'On Sale', //cpb
                '398794'    =>  'FREE', //pcm
                '343334'    =>  'FREE', //cc
                '462206'    =>  'On Sale'

            ];

            if ( isset($wdm_product_ribbon_text[$post->ID]) ) {
                $discount_text = $wdm_product_ribbon_text[$post->ID];
            }

            // if (in_array($post->ID, $single_pro_list)) {
            //     // Single Pro discount percentage ribbon HTML
            //     $discount_text = get_field('discount_percentage_ribbon_for_single', 'option');
            //     $best_seller = '<span class="popular-plan-col"> ' . esc_html($discount_text) . ' </span>';
            // } elseif(in_array($post->ID, $bundle_pro_list)) {
            //     // Bundle Pro discount percentage ribbon HTML
            //     $discount_text = get_field('discount_percentage_ribbon_for_bundle', 'option');
                
            //     // Temporary change for LEAP ribbon only
               
            // }
            if( strcmp('FREE', $discount_text) == 0 ) {
                $best_seller = '<span class="popular-plan-col">&nbsp;&nbsp;&nbsp;&nbsp;' . esc_html($discount_text) . '&nbsp;&nbsp;</span>';

            } else {
                $best_seller = '<span class="popular-plan-col">' . esc_html($discount_text) . '</span>';
            }


            return $best_seller;
        }

        return $best_seller;
    }

    /**
     * Callback to filter 'wdm_show_free_trials_section'.
     */
    public function show_free_trials_section($show_free_trials_section) {
        if (WdmSale::is_sale_live()) {
            $show_free_trials_section = empty(get_field('show_free_trials_section', 'option')) ? false : true;
            return $show_free_trials_section;
        }

        return $show_free_trials_section;
    }

    /**
     * Callback to filter 'wdm_show_free_trial_exit_intent_popup_products_lp'.
     *
     * Hide the free trial exit intent popop when sale is live and
     * 'Show Free Trails Section' setting is disabled.
     */
    function show_free_trial_exit_intent_popup_products_lp($show_popup) {
        if (WdmSale::is_sale_live()) {
            $show_popup = empty(get_field('show_free_trials_section', 'option')) ? false : true;
            return $show_popup;
        }

        return $show_popup;
    }

    /**
     * Callback to filter 'wdm_product_data_fetched_from_json'.
     */
    public function use_price_sale_json_file($file_contents, $file, $folder_path) {
        if (WdmSale::is_sale_live() && !empty(get_field('use_price_sale_json_file', 'option'))) {
            $price_sale_json_files_mapping = array (
                '/pricing.json'              => '/pricing_sale.json',
                '/pricing_section_data.json' => '/pricing_section_data_sale.json',
                '/pricing_table_plans.json'  => '/pricing_table_plans_sale.json',
            );

            if (!empty($file)) {
                if (isset($price_sale_json_files_mapping[$file])) {
                    $sale_file      = $price_sale_json_files_mapping[$file];
                    $sale_file_path = $folder_path . $sale_file;
                    $file_contents = file_exists($sale_file_path) ? file_get_contents($sale_file_path) : $file_contents;
                }
            }
        }
        return $file_contents;
    }

    /**
     * Remove the discount code GRABFAST100.
     */
    public function wdm_remove_spin_winner_edd_discount_code() {
        $discount_code    = 'GRABFAST100';
        $cart_items       = edd_get_cart_contents();
        $discount_applies = false;
        
        if ( is_array( $cart_items ) && count( $cart_items ) > 1 ) {
            edd_unset_cart_discount( $discount_code );
        }
    }

    /**
     * Return false if discount code is 'GRABFAST100' and
     * number of products in the cart are more than 1.
     */
    public function wdm_is_spin_winner_edd_discount_code_valid($return, $id, $discount_code) {
        $cart_items       = edd_get_cart_contents();
        
        if ( 'GRABFAST100' == $discount_code && is_array( $cart_items ) && count( $cart_items ) > 1 ) {
            return false;
        }

        return $return;
    }

    /**
     * Return the error message if discount code is
     * 'GRABFAST100' and number of products in the cart
     * is more than 1.
     */
    public function wdm_return_spin_winner_edd_discount_code_invalid_msg($return) {
        $discount_code = $return['code'];
        $cart_items    = edd_get_cart_contents();
        
        if ( 'GRABFAST100' == $discount_code && is_array( $cart_items ) && count( $cart_items ) > 1 ) {
            $return['msg'] = 'GRABFAST100 can be used only for one product.';
            return $return;
        }

        return $return;
    }

    /**
     * Enqueue spin the wheel copy coupon code script.
     */
    public function enqueue_spin_the_wheel_copy_coupon_code_script() {
        // Check if BFCM Giveaway page.
        if ( ! is_page( '472248' ) ) {
            return;
        }

        $js = '
        jQuery(document).ready(function(){
            jQuery(document.body).on("click", "#wisdm-spin-the-wheel-copy-coupon-code-btn", function(){
                navigator.clipboard.writeText(jQuery("#wisdm-spin-the-wheel-winner-coupon-code strong").text());
            });
        });
        ';
        wp_add_inline_script( 'wordpress-lucky-wheel-frontend-javascript', $js);
    }
}
