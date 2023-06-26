<?php
/*
Plugin Name: WDM Common Functions
Plugin URI: https://www.wisdmlabs.com
Description: To add common functions which can be added to functions.php file of Wisdmlabs Theme
Version: 1.0.0
Author: Wisdmlabs
Author URI: http://www.wisdmlabs.com
*/
// To add a custom WordPress Rest API call aded on 22-04-2019
require_once 'WdmUnsubRest.php';
require_once(ABSPATH . 'wp-content/plugins/wdm-sendy-to-contact-form-7/libraries/MaxMind-DB-Reader-php-master/autoload.php');
use MaxMind\Db\Reader;

define('WDM_PRO_FEATURE', 1);
define('FCC_TO_IR', 1);
define('WDM_SEND_PER_PRODUCT_EMAILS', 1);
define('WDM_COMMON_FUNCTIONS_DIR', plugin_dir_path( __FILE__ ));

// To stop access to login page if the user is already logged in
add_action('wp', 'wdm_add_login_check');
function wdm_add_login_check()
{
    if (is_page('login') && is_user_logged_in()) {
        if (empty($_REQUEST['redirect_to'])) {
            wp_redirect(home_url());
            exit;
        } else {
            wp_redirect($_REQUEST['redirect_to']);
            exit;
        }
    }
}

// Added code to enable custom permalinks input field for Post type equals to Product
// only, in the earlier version of custom permalink they have not provided any hooks
// therefore we did change in the core file, but in the new version 1.3.0 they have
// given a hook 'custom_permalinks_exclude_post_type'
if (defined('CUSTOM_PERMALINKS_FILE')) {
    // If CUSTOM_PERMALINKS_FILE is defined it means custom permalink plugin is enabled

    function wdm_custom_permalinks_exclude_post_type($exclude_post_types)
    {
        if ($exclude_post_types != 'products') {
            return '__true';
        }
        return $exclude_post_types;
    }
    add_filter('custom_permalinks_exclude_post_type', 'wdm_custom_permalinks_exclude_post_type');
}

// NSL_PATH_FILE is defined it means nextend facebook connect social login plugin is active
if (defined('NSL_PATH_FILE')) {
    // To add style to social login form on checkout page
    function wisdmlabs_enqueue_custom_for_super_socializer()
    {
        if (/* is_page('checkout') ||  */is_page('my-account') || is_page('login')) {
            wp_enqueue_style('wdm-edd-checkout', get_stylesheet_directory_uri().'/css/wdm-footer-edd-checkout.css', false, CHILD_THEME_VERSION);
        }
    }
    add_action('wp_footer', 'wisdmlabs_enqueue_custom_for_super_socializer');

    // To change the order of register and login form, be default edd shows register form
    // first then login form
    function wdm_edd_show_purchase_form()
    {
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
                    <?php do_action('edd_purchase_form_login_fields'); ?>
                </div>
            <?php elseif (($show_register_form === 'login' || ($show_register_form === 'both' && isset($_GET['login']))) && ! is_user_logged_in()) : ?>
                <div id="edd_checkout_login_register">
                    <?php do_action('edd_purchase_form_register_fields'); ?>
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
    remove_action('edd_purchase_form', 'edd_show_purchase_form', 10);
    add_action('edd_purchase_form', 'wdm_edd_show_purchase_form');


    // To change the string "Need to create an account?" to the bottom of the form
    function wdm_edd_get_login_fields()
    {
        $color = edd_get_option('checkout_color', 'gray');
        $color = ($color == 'inherit') ? '' : $color;
        $edd_no_gst_chkot = edd_no_guest_checkout();
        // $style = edd_get_option('button_style', 'button');

        $show_register_form = edd_get_option('show_register_form', 'none');

        ob_start(); ?>
            <fieldset id="edd_login_fields">
                <?php do_action('edd_checkout_login_fields_before'); ?>
                <p id="edd-user-login-wrap">
                    <label class="edd-label" for="edd_user_login">
                        <?php _e('Username or Email', 'easy-digital-downloads'); ?>
        <?php
        if ($edd_no_gst_chkot) {
            ?>
                        <span class="edd-required-indicator">*</span>
            <?php
        }
        ?>
                    </label>
                    <input class="<?php
                    if ($edd_no_gst_chkot) {
                        echo 'required ';
                    } ?>edd-input" type="text" name="edd_user_login" id="edd_user_login" value="" placeholder="<?php _e('Your username or email address', 'easy-digital-downloads'); ?>"/>
                </p>
                <p id="edd-user-pass-wrap" class="edd_login_password">
                    <label class="edd-label" for="edd_user_pass">
                        <?php _e('Password', 'easy-digital-downloads'); ?>
                        <?php
                        if ($edd_no_gst_chkot) {
                            ?>
                        <span class="edd-required-indicator">*</span>
                            <?php
                        } ?>
                    </label>
                    <input class="<?php
                    if ($edd_no_gst_chkot) {
                        echo 'required ';
                    } ?>edd-input" type="password" name="edd_user_pass" id="edd_user_pass" placeholder="<?php _e('Your password', 'easy-digital-downloads'); ?>"/>
                    <?php if ($edd_no_gst_chkot) : ?>
                        <input type="hidden" name="edd-purchase-var" value="needs-to-login"/>
                    <?php endif; ?>
                </p>
                <p id="edd-user-login-submit">
                    <input type="submit" class="edd-submit button <?php echo $color; ?>" name="edd_login_submit" value="<?php _e('Login', 'easy-digital-downloads'); ?>"/>
                    <?php wp_nonce_field('edd-login-form', 'edd_login_nonce', false, true); ?>
                </p>
                <?php
                if ($show_register_form == 'both') {
                    ?>
                    <p id="edd-new-account-wrap">
                        <?php _e('Need to create an account?', 'easy-digital-downloads'); ?>
                        <a href="<?php echo esc_url(remove_query_arg('login')); ?>" class="edd_checkout_register_login" data-action="checkout_register" data-nonce="<?php echo wp_create_nonce('edd_checkout_register'); ?>">
                            <?php
                            _e('Register', 'easy-digital-downloads');
                            if (!$edd_no_gst_chkot) {
                                echo ' ' . __('or checkout as a guest.', 'easy-digital-downloads');
                            } ?>
                        </a>
                    </p>
                    <?php
                } ?>
                <?php do_action('edd_checkout_login_fields_after'); ?>
            </fieldset><!--end #edd_login_fields-->
        <?php
        echo ob_get_clean();
    }
    remove_action('edd_purchase_form_login_fields', 'edd_get_login_fields', 10);
    add_action('edd_purchase_form_login_fields', 'wdm_edd_get_login_fields');

    // To change the string "Already have an account?" to the bottom of the form
    function wdm_edd_get_register_fields()
    {
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
                    <p id="edd-user-login-wrap">
                        <label for="edd_user_login">
                            <?php _e('Username', 'easy-digital-downloads'); ?>
                        </label>
                        <span class="edd-description"><?php _e('The username you will use to log into your account.', 'easy-digital-downloads'); ?></span>
                        <input name="edd_user_login" id="edd_user_login" class="edd-input" type="text" placeholder="<?php _e('Username', 'easy-digital-downloads'); ?>"/>
                    </p>
                    <p id="edd-user-pass-wrap">
                        <label for="edd_user_pass">
                            <?php _e('Password', 'easy-digital-downloads'); ?>
                        </label>
                        <span class="edd-description"><?php _e('The password used to access your account.', 'easy-digital-downloads'); ?></span>
                        <input name="edd_user_pass" id="edd_user_pass" class="edd-input" placeholder="<?php _e('Password', 'easy-digital-downloads'); ?>" type="password"/>
                    </p>
                    <p id="edd-user-pass-confirm-wrap" class="edd_register_password">
                        <label for="edd_user_pass_confirm">
                            <?php _e('Password Again', 'easy-digital-downloads'); ?>
                        </label>
                        <span class="edd-description"><?php _e('Confirm your password.', 'easy-digital-downloads'); ?></span>
                        <input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="edd-input" placeholder="<?php _e('Confirm password', 'easy-digital-downloads'); ?>" type="password"/>
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

                <fieldset id="edd_register_account_fields">
                    <legend><?php
                    _e('Create an account', 'easy-digital-downloads');
                    ?></legend>
                    <?php do_action('edd_register_account_fields_before'); ?>
                    <p id="edd-user-login-wrap">
                        <label for="edd_user_login">
                            <?php _e('Username', 'easy-digital-downloads'); ?>
                            <span class="edd-required-indicator">*</span>
                        </label>
                        <span class="edd-description"><?php _e('The username you will use to log into your account.', 'easy-digital-downloads'); ?></span>
                        <input name="edd_user_login" id="edd_user_login" class="required edd-input" type="text" placeholder="<?php _e('Username', 'easy-digital-downloads'); ?>"/>
                    </p>
                    <p id="edd-user-pass-wrap">
                        <label for="edd_user_pass">
                            <?php _e('Password', 'easy-digital-downloads'); ?>
                            <span class="edd-required-indicator">*</span>
                        </label>
                        <span class="edd-description"><?php _e('The password used to access your account.', 'easy-digital-downloads'); ?></span>
                        <input name="edd_user_pass" id="edd_user_pass" class="required edd-input" placeholder="<?php _e('Password', 'easy-digital-downloads'); ?>" type="password"/>
                    </p>
                    <p id="edd-user-pass-confirm-wrap" class="edd_register_password">
                        <label for="edd_user_pass_confirm">
                            <?php _e('Password Again', 'easy-digital-downloads'); ?>
                            <span class="edd-required-indicator">*</span>
                        </label>
                        <span class="edd-description"><?php _e('Confirm your password.', 'easy-digital-downloads'); ?></span>
                        <input name="edd_user_pass_confirm" id="edd_user_pass_confirm" class="required edd-input" placeholder="<?php _e('Confirm password', 'easy-digital-downloads'); ?>" type="password"/>
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
        }
        echo ob_get_clean();
    }
    remove_action('edd_purchase_form_register_fields', 'edd_get_register_fields', 10);
    add_action('edd_purchase_form_register_fields', 'wdm_edd_get_register_fields');

    // To add social login on register and login form of EDD checkout
    function wdm_edd_checkout_login_fields_after()
    {
        echo '<fieldset>' . do_shortcode('[nextend_social_login redirect="https://wisdmlabs.com/checkout"]') . '<p class="login-or-para">OR</p></fieldset>';
    }
    add_action('edd_checkout_login_fields_before', 'wdm_edd_checkout_login_fields_after', 1);
    add_action('edd_register_fields_before', 'wdm_edd_checkout_login_fields_after', 1);

    // To add social login shortcode after login button on edd my account page
    function wdm_edd_tml_login_fields_next_after()
    {
        echo do_shortcode('[nextend_social_login redirect="https://wisdmlabs.com/my-account"]');
        echo '<p class="login-or-para">OR</p>';
    }

    add_action('edd_login_fields_before', 'wdm_edd_tml_login_fields_next_after');
    // add_action('login_form', 'wdm_edd_tml_login_fields_next_after');
}

if (is_admin()) {
    // To show svg featured image thumbnail in Dashboard
    function wdm_admin_head_for_svg()
    {
        $css = '';
        $css = '#set-post-thumbnail img[src$=".svg"],#listingimagediv img[src$=".svg"],#listingimagediv img[src$=".png"]{ width: 100% !important; height: auto !important; }';
        echo '<style type="text/css">'.$css.'</style>';

        // error_log(count(_get_cron_array()));
    }

    add_action('admin_head', 'wdm_admin_head_for_svg');
    // add meta box to get featured images for Premium Product Archive pages
    add_action('add_meta_boxes', 'wdm_listing_image_add_metabox');
    function wdm_listing_image_add_metabox()
    {
        add_meta_box('listingimagediv', __('Listing Image', 'text-domain'), 'wdm_listing_image_metabox', 'products', 'side', 'low');
    }
    /** * @SuppressWarnings(PHPMD) */
    function wdm_listing_image_metabox($post)
    {
        global $content_width, $_wp_additional_image_sizes;
        $image_id = get_post_meta($post->ID, '_listing_image_id', true);
        $old_content_width = $content_width;
        $content_width = 254;
        if ($image_id && get_post($image_id)) {
            if (! isset($_wp_additional_image_sizes['post-thumbnail'])) {
                $thumbnail_html = wp_get_attachment_image($image_id, array( $content_width, $content_width ));
            } else {
                $thumbnail_html = wp_get_attachment_image($image_id, 'post-thumbnail');
            }
            if (! empty($thumbnail_html)) {
                $content = $thumbnail_html;
                $content .= '<p class="hide-if-no-js"><a href="javascript:;" id="remove_listing_image_button" >' . esc_html__('Remove listing image', 'text-domain') . '</a></p>';
                $content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="' . esc_attr($image_id) . '" />';
            }
            $content_width = $old_content_width;
        } else {
            $content = '<img src="" style="width:' . esc_attr($content_width) . 'px;height:auto;border:0;display:none;" />';
            $content .= '<p class="hide-if-no-js"><a title="' . esc_attr__('Set listing image', 'text-domain') . '" href="javascript:;" id="upload_listing_image_button" id="set-listing-image" data-uploader_title="' . esc_attr__('Choose an image', 'text-domain') . '" data-uploader_button_text="' . esc_attr__('Set listing image', 'text-domain') . '">' . esc_html__('Set listing image', 'text-domain') . '</a></p>';
            $content .= '<input type="hidden" id="upload_listing_image" name="_listing_cover_image" value="" />';
        }
        echo $content;
    }
    // To save listing featured image
    add_action('save_post', 'wdm_listing_image_save', 10, 1);
    function wdm_listing_image_save($post_id)
    {
        if (isset($_POST['_listing_cover_image'])) {
            $image_id = (int) $_POST['_listing_cover_image'];
            update_post_meta($post_id, '_listing_image_id', $image_id);
        }
    }
    // To include listing image metabox's open media library script
    function wdm_add_admin_scripts_for_featured($hook)
    {
        global $post;
        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if ('products' === $post->post_type) {
                wp_enqueue_script('wdm-admin-script', get_stylesheet_directory_uri().'/js/wdm-admin.js');
            }
        }
    }
    add_action('admin_enqueue_scripts', 'wdm_add_admin_scripts_for_featured', 10, 1);
}


if (class_exists('EDD_Subscription')) {
    // To stop monthly renew notice for the downloads which has monthly subscription
    add_filter('edd_recurring_send_reminder', 'wdm_edd_recurring_send_reminder', 10, 2);

    function wdm_edd_recurring_send_reminder($send, $subscription_id)
    {
        $edd_subs = new EDD_Subscription($subscription_id);
        $download_id = $edd_subs->get_product_id();
        // 165532 --> WisdmApp for LearnDash
        if ($download_id == '165532') {
            return false;
        }
        return $send;
    }
}
if (defined('WPCF7_PLUGIN')) {
    require_once('contact-form-create-user.php');
    WDMCommonFunctions\Cf7CreateUser::getInstance();
    include_once 'contact-form-verify-email.php';
    Cf7VerifyEmail::getInstance();
}
if (defined('WPCF7_PLUGIN')) {
    // Added to remove auto p tag adding feature
    add_filter('wpcf7_autop_or_not', '__return_false');
    function wdm_contact_form_7_subs($content)
    {
        global $post;
        if (class_exists('WPCF7_ContactForm') && isset($post->post_type) && $post->post_type == 'post' && is_single() /*&& get_current_user_id() == 386*/) {
            $post_term = wp_get_post_terms($post->ID, 'menu_filter');
            $cf7_linked_id = get_term_meta($post_term[0]->term_id, 'wdm_cf7_linked', true);

            if (!isset($cf7_linked_id) || empty($cf7_linked_id)) {
                return $content;
            }
            $term = WPCF7_ContactForm::get_instance($cf7_linked_id);
            // base64encode(thankyou) = dGhhbmt5b3U=
            // base64encode(already) = YWxyZWFkeQ==
            // If user has not clicked thank you and is not already subscribed
            // if( empty($_COOKIE['dGhhbmt5b3U']) && empty($_COOKIE['YWxyZWFkeQ']) ){
            return $content .= do_shortcode('[subscription_box]'.$term->shortcode().'[/subscription_box]');
            // }
        }
        return $content;
    }
    // add_filter('the_content', 'wdm_contact_form_7_subs');

    add_filter('wpcf7_validate_email*', 'wdm_custom_email_confirmation_validation_filter', 9, 2);

    function wdm_custom_email_confirmation_validation_filter($result, $tag)
    {
        if ('your-email' == $tag->name) {
            if (isset($_POST['subs_term_and_conditions'])) {
                // $name    = filter_input( INPUT_POST, 'your-name' );
                $email   = filter_input(INPUT_POST, 'your-email');

                $sendy_url = esc_attr(get_option('sendy_url'));
                $sendy_api_key = esc_attr(get_option('sendy_api_key'));
                if (empty($email)|| $email == false || empty($sendy_url) || $sendy_url == false || empty($sendy_api_key) || $sendy_api_key == false) {
                    return $result;
                }
                $list_id = filter_input(INPUT_POST, 'sendy-id-value'); // ID of Sendy List

                /**
                 * Check if the user is subscribed and the current status.
                 */
                if ($list_id && $list_id !== '') {
                    $url     = $sendy_url.'/api/subscribers/subscription-status.php';
                    $data    = array(
                        'api_key'    => $sendy_api_key,
                        'email'      => $email,
                        'list_id'    => $list_id,
                    );

                    $options     = array(
                        'method'         => 'POST',
                        'headers'        => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
                        'body'           => $data,
                        'timeout'        => 5,
                        'httpversion'    => '1.0',
                    );
                    $curl_result      = wp_remote_post($url, $options);
                    $response    = wp_remote_retrieve_body($curl_result);

                    /**
                     * Adding user if not exist to sendy
                     */
                    if ($response === 'Subscribed') {
                        // add_filter('wpcf7_display_message','wdm_wpcf7_form_response_output',999);
                        // remove_filter('wpcf7_display_message','wdm_wpcf7_form_response_output',999);
                        $result->invalidate($tag, "You are already subscribed.");
                    }
                }
            }
        }
        return $result;
    }

    // For phone number validation on contact us page
    function wdm_custom_contact_us_validation_phone_filter($result,$tag){
        if( isset($_POST['_wpcf7']) && ( '446234'==$_POST['_wpcf7'] || '661149'==$_POST['_wpcf7'] || '886963'==$_POST['_wpcf7'] ) && !empty($_POST['your-phone']) && $_POST['your-phone'] && $tag->name="your-phone" ){
            // $preg_match_result = preg_match( '/^[+]?[0-9() -]*$/', $_POST['your-phone'] );
            $preg_match_result = preg_match( '/^\+(?:[0-9] ?){6,14}[0-9]$/', $_POST['your-phone'] );
            if(!$preg_match_result){
                $result->invalidate($tag, "Please enter a valid phone number.");
            }
        }
        return $result;
    }
    add_filter('wpcf7_validate_phonetext*', 'wdm_custom_contact_us_validation_phone_filter', 9, 2);


    add_filter('wpcf7_ajax_json_echo', 'wdm_wpcf7_form_response_output', 999, 2);
    function wdm_wpcf7_form_response_output($response, $status)
    {
        $form_title = !empty($status['contact_form_id'])?get_the_title($status['contact_form_id']):'';
        if (!empty($status['status'])
            && $status['status'] == 'validation_failed'
            && !empty($form_title)
            && ((stripos($form_title, 'Blog Subscription') > -1)
            || (stripos($form_title, 'Blog Subscriber') > -1))
        ) {
            $response['message'] = '';
            if (!empty($response['invalidFields'])) {
                foreach ($response['invalidFields'] as $key => $invalidFields) {
                    $response['message'] .= $invalidFields['message'] . ' ';
                    $response['invalidFields'][$key]['message'] = '';
                }
            }
        }
        // Is contact-us page's contact form
        if($status['contact_form_id']=='446234' && !empty($status['status']) && $status['status'] == 'validation_failed'){
            $response['message'] = 'Hey! Looks like we need to fix some minor errors. <br/>Please watch out for the messages in \'Red\'. They\'ll point you in the right direction. Thanks!';
        }
        return $response;
    }

    // Add twitter code in head
    add_action('wp_head', 'wdm_wp_head_twitter');

    function wdm_wp_head_twitter()
    {
        ?>
        <!-- Twitter universal website tag code -->
        <script>
        !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
        },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='//static.ads-twitter.com/uwt.js',
        a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
        // Insert Twitter Pixel ID and Standard Event data below
        twq('init','o0zkq');
        twq('track','PageView');
        </script>
        <!-- End Twitter universal website tag code -->
        <?php
    }

    // After updating contact form 7, additional settings events has been depreacated, therefore added
    // Js code in footers


    add_action('wp_footer', 'wdm_cf7_additional_settings_wp_footer');

    function wdm_cf7_additional_settings_wp_footer()
    {
        ?>
    <script type="text/javascript">
    document.addEventListener( 'wpcf7mailsent', function( event ) {
        if ( '112562' == event.detail.contactFormId ) {
            location.replace('https://wisdmlabs.com/woocommerce-user-specific-pricing-extension/');
        }
        if ( '136911' == event.detail.contactFormId ) {
            location.replace('http://wpsandbox.pro/create?src=amber-dugong&key=bU6eTbvlk49tytlM');
        }
        if ( '1059' == event.detail.contactFormId ) {
            ga('send', 'pageview', '/goal/contact-form');
        }
        if ( '109085' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com/front-end-course-creation-for-learndash/');"
        }
        if ( '111758' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com/group-registration-for-learndash/');"
        }
        if ( '111753' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com/instructor-role-extension-for-learndash/');"
        }
        if ( '175697' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com');"
        }
        if ( '33279' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com/thank-you/');"
        }
        if ( '78391' == event.detail.contactFormId ) {
            location.replace('https://wisdmlabs.com/thank-you/');
        }
        if ( '26291' == event.detail.contactFormId ) {
            location.replace('https://wisdmlabs.com/thank-you/');
        }
        if ( '1417' == event.detail.contactFormId ) {
            ga('send', 'Contact Form', 'Lead', 'Submit', '/mega-menu/', {'nonInteraction': 1});
        }

    }, false );
    </script>
        <?php
    }

    function wdm_cf7_additional_settings_wp_footer_alt()
    {
        ob_start(); ?>
        ?>
    <script type="text/javascript">
    document.addEventListener( 'wpcf7mailsent', function( event ) {
        if ( '112562' == event.detail.contactFormId ) {
            location.replace('https://wisdmlabs.com/woocommerce-user-specific-pricing-extension/');
        }
        if ( '136911' == event.detail.contactFormId ) {
            location.replace('http://wpsandbox.pro/create?src=amber-dugong&key=bU6eTbvlk49tytlM');
        }
        if ( '1059' == event.detail.contactFormId ) {
            ga('send', 'pageview', '/goal/contact-form');
        }
        if ( '109085' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com/front-end-course-creation-for-learndash/');"
        }
        if ( '111758' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com/group-registration-for-learndash/');"
        }
        if ( '111753' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com/instructor-role-extension-for-learndash/');"
        }
        if ( '175697' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com');"
        }
        if ( '33279' == event.detail.contactFormId ) {
            on_sent_ok: "location.replace('https://wisdmlabs.com/thank-you/');"
        }
        if ( '78391' == event.detail.contactFormId ) {
            location.replace('https://wisdmlabs.com/thank-you/');
        }
        if ( '26291' == event.detail.contactFormId ) {
            location.replace('https://wisdmlabs.com/thank-you/');
        }
        if ( '1417' == event.detail.contactFormId ) {
            ga('send', 'Contact Form', 'Lead', 'Submit', '/mega-menu/', {'nonInteraction': 1});
        }

    }, false );
    </script>
        <?php
        $script_data = ob_get_clean();
        wp_add_inline_script('wdm_moment_js_countdown', $script_data);
    }
}
// ------------------------------- To add trust making text in 2Checkout credit card fields ----------
if (class_exists('EDD_2Checkout_Gateway')) {
    remove_action('plugins_loaded', 'edd_2checkout_load');
    $gateway = new EDD_2Checkout_Gateway;
    remove_action('edd_2checkout_onsite_cc_form', array( $gateway, 'card_form' ));
    unset($gateway);
    add_action('edd_2checkout_onsite_cc_form', 'wdm_2checkout_card_form');
}

function wdm_2checkout_card_form($display = 1)
{
    ob_start(); ?>

    <?php
    if (! wp_script_is('edd_2co_script')) : ?>
        <?php
        if (edd_is_gateway_active('2checkout_onsite') && edd_is_checkout()) {
            wp_enqueue_script('edd_2co', plugin_dir_url(__FILE__) . 'js/2co.js', array( 'jquery' ), '1.0', true);
            wp_register_script('edd_2co_script', plugin_dir_url(__FILE__) . 'js/script.js', array( 'edd_2co' ), '1.0', true);
            wp_localize_script(
                'edd_2co_script',
                'edd_2co_vars',
                array(
                    'sellerID'   => edd_get_option('tco_account_number', ''),
                    'public_key' => edd_get_option('tco_publishable_api_key', ''),
                    'mode'       => edd_is_test_mode() ? 'sandbox' : 'production'
                )
            );
            wp_enqueue_script('edd_2co_script');
        } ?>
    <?php endif; ?>

    <?php do_action('edd_before_cc_fields'); ?>

    <fieldset id="edd_cc_fields" class="edd-do-validate">
        <legend><?php _e('Credit Card Info', 'edd'); ?></legend>
        <?php if (is_ssl()) : ?>
            <div id="edd_secure_site_wrapper">
                    <span class="padlock"></span>
                    <div style="padding: 0 0 0 0;">
                        <p style="margin-bottom: 1px;">Safe &amp; Secure <i class="fa fa-lock" aria-hidden="true"></i></p>
                        <p style="font-weight: 400;color: #666;font-size: 80%;">Your credit card details will be save by our payment processor <b>2Checkout</b> over a secure SSL connection.</p>
                    </div>
            </div>
        <?php endif; ?>
        <p id="edd-card-number-wrap">
            <label for="card_number" class="edd-label">
                <?php _e('Card Number', 'edd'); ?>
                <span class="edd-required-indicator">*</span>
                <span class="card-type"></span>
            </label>
            <span class="edd-description"><?php _e('The (typically) 16 digits on the front of your credit card.', 'edd'); ?></span>
            <input type="text" autocomplete="off" id="card_number" class="card-number edd-input required" placeholder="<?php _e('Card number', 'edd'); ?>" />
        </p>
        <p id="edd-card-cvc-wrap">
            <label for="card_cvc" class="edd-label">
                <?php _e('CVC', 'edd'); ?>
                <span class="edd-required-indicator">*</span>
            </label>
            <span class="edd-description"><?php _e('The 3 digit (back) or 4 digit (front) value on your card.', 'edd'); ?></span>
            <input type="text" size="4" autocomplete="off" id="card_cvc" class="card-cvc edd-input required" placeholder="<?php _e('Security code', 'edd'); ?>" />
        </p>
        <p id="edd-card-name-wrap">
            <label for="card_name" class="edd-label">
                <?php _e('Name on the Card', 'edd'); ?>
                <span class="edd-required-indicator">*</span>
            </label>
            <span class="edd-description"><?php _e('The name printed on the front of your credit card.', 'edd'); ?></span>
            <input type="text" autocomplete="off" name="card_name" id="card_name" class="card-name edd-input required" placeholder="<?php _e('Card name', 'edd'); ?>" />
        </p>
        <?php do_action('edd_before_cc_expiration'); ?>
        <p class="card-expiration">
            <label for="card_exp_month" class="edd-label">
                <?php _e('Expiration (MM/YY)', 'edd'); ?>
                <span class="edd-required-indicator">*</span>
            </label>
            <span class="edd-description"><?php _e('The date your credit card expires, typically on the front of the card.', 'edd'); ?></span>
            <select id="card_exp_month" class="card-expiry-month edd-select edd-select-small required">
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    echo '<option value="' . $i . '">' . sprintf('%02d', $i) . '</option>';
                } ?>
            </select>
            <span class="exp-divider"> / </span>
            <select id="card_exp_year" class="card-expiry-year edd-select edd-select-small required">
                <?php
                for ($i = date('Y'); $i <= date('Y') + 10; $i++) {
                    echo '<option value="' . $i . '">' . substr($i, 2) . '</option>';
                } ?>
            </select>
        </p>
        <?php do_action('edd_after_cc_expiration'); ?>

    </fieldset>
    <?php
    do_action('edd_after_cc_fields');

    $form = ob_get_clean();

    if (false !== $display) {
        echo $form;
    }

    return $form;
}
// -------------------------- Theme my login mautic integration -----------------------------

// if (defined('THEME_MY_LOGIN_PATH')) {
//     require_once('login-mautic.php');
//     $Wdm_Login_Form_To_Mautic = new Wdm_Login_Form_To_Mautic();
// }
// ------------------------------ To inform admins about amazon ses off status --------------
if (is_admin()) {
    // if (is_plugin_active('wp-ses/wp-ses.php')) {
    //     include_once 'inform-wp-ses-status.php';
    //     WdmSiteAlert\InformWpSesStatus::getInstance();
    // }
    // if (is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')) {
    //     include_once 'inform-robots-block.php';
    //     WdmSiteAlert\InformRobotsBlock::getInstance();
    // }
}
include_once 'class-edd-settings-custom-renewal-emails.php';
$eddRenewalEmailObj = WDMCommonFunctions\EddSettingsCustomRenewalEmails::getInstance();

// To show more purchase options for more licenses
include_once 'class-more-edd-purchase-options.php';
$eddMorePurchaseOptionsObj = WDMCommonFunctions\MoreEddCartPurchaseOptions::getInstance();

// To allow FCC clients to upgrade to IR with a discount
include_once 'class-allow-free-fcc-to-ir.php';
$allowFreeFccToIrObj = WDMCommonFunctions\AllowFreeFccToIr::getInstance('348813', 'FreeFccToIR2019');

// ---------------------------- Custom Schema on Single Article -------------------------
function wdm_add_custom_schema_post($metadata, $object_id, $meta_key, $single)
{
    $custom_schema_key ="wp_schema_pro_optimized_structured_data";
    if (isset($meta_key) && $meta_key==$custom_schema_key) {
        remove_filter('get_post_metadata', 'wdm_add_custom_schema_post', 999);
        $json_ld_markups = get_post_meta($object_id, 'wp_schema_pro_optimized_structured_data', true);
        if ($json_ld_markups) {
            $metadata .= $json_ld_markups;
        }
        // error_log('Tariq Schema');
        $custom_schema = get_post_meta($object_id, 'wdm_custom_schema', true);
        if (!empty($custom_schema)) {
            $metadata .= '<!-- Schema optimized by Schema Pro --><script type="application/ld+json">'.json_encode(json_decode($custom_schema)).'</script><!-- / Schema optimized by Schema Pro -->';
        }
    }
    return $metadata;
}
add_filter('get_post_metadata', 'wdm_add_custom_schema_post', 999, 4);

// ------------------------------- To make jilt js request async -------------------
add_filter('script_loader_tag', 'wdm_add_async_for_jilt_script', 10, 3);
function wdm_add_async_for_jilt_script($tag, $handle, $src)
{
    // The handles of the enqueued scripts we want to defer
    $defer_scripts = array(
        'edd-jilt'
    );
    if (in_array($handle, $defer_scripts)) {
        return '<script src="' . $src . '" type="text/javascript" defer async></script>' . "\n";
    }
    return $tag;
}
// --------------------------------- To remove genesis schema from product landing pages -----------------
add_action('wp_head', 'wdm_remove_genesis_schema');
function wdm_remove_genesis_schema()
{
    global $template;
    if (strpos($template, 'product-landing-template-v13.php')) {
        $elements = array(
            'head',
            'body',
            'site-header',
            'site-title',
            'site-description',
            'breadcrumb',
            'breadcrumb-link-wrap',
            'breadcrumb-link-wrap-meta',
            'breadcrumb-link',
            'breadcrumb-link-text-wrap',
            'search-form',
            'search-form-meta',
            'search-form-input',
            'nav-primary',
            'nav-secondary',
            'nav-header',
            'nav-link-wrap',
            'nav-link',
            'entry',
            'entry-image',
            'entry-image-widget',
            'entry-image-grid-loop',
            'entry-author',
            'entry-author-link',
            'entry-author-name',
            'entry-time',
            'entry-modified-time',
            'entry-title',
            'entry-content',
            'comment',
            'comment-author',
            'comment-author-link',
            'comment-time',
            'comment-time-link',
            'comment-content',
            'author-box',
            'sidebar-primary',
            'sidebar-secondary',
            'site-footer',
        );

        $elements = apply_filters('be_remove_schema_elements', $elements);

        foreach ($elements as $element) {
            add_filter('genesis_attr_' . $element, 'wdm_be_remove_schema_attributes', 20);
        }
    }
}
/**
 * Remove schema attributes
 *
 */
function wdm_be_remove_schema_attributes($attr)
{
    unset($attr['itemprop'], $attr['itemtype'], $attr['itemscope']);
    return $attr;
}
// ------------------------------------- To
if (defined('WDM_PRO_FEATURE')) {
    require_once('class-prod-fav-feature-model.php');
    require_once('class-prod-features-fav.php');
    $prodFavFeatureObj = WDMCommonFunctions\ProdFavFeature::getInstance();
}
if (defined('FCC_TO_IR')) {
    require_once('fcc_with_ir.php');
    $fcc_with_ir = WDMCommonFunctions\FccWithIr::getInstance();
}
require_once('class-custom-blog-cta.php');
$custom_cta_blogs_object = WDMCommonFunctions\CustomBlogCta::getInstance();
add_shortcode('wdm_custom_cta', array($custom_cta_blogs_object,'shortcodeCallback'));
add_shortcode('wdm_up_sell_leap_cta', array($custom_cta_blogs_object,'upSellLeapCtsShortcodeCallback'));
add_action('edd_pre_add_to_cart', array($custom_cta_blogs_object,'remove_existing_before_upsell'),99,2);
add_action('init', array($custom_cta_blogs_object,'remove_query_args'),99);

require_once('class-upgrade-popup.php');
function wdm_upgrade_instantiation(){
    $upgrade_popup = WDMCommonFunctions\UpgradePopup::getInstance();
    add_shortcode('wdm_upgrade_popup', array($upgrade_popup,'shortcodeCallback'));
}
add_action('plugins_loaded','wdm_upgrade_instantiation');

require_once('class-elumine-lpp-leap.php');
$elumine_lpp_leap_object = WDMCommonFunctions\ElumineLppLeap::getInstance();

require_once('class-send-upgrade-emails.php');
$send_upgrade_emails_object = WDMCommonFunctions\SendUpgradeEmails::getInstance();

require_once('class-upgrade-shortcode-button.php');
$upgrade_shortcode_button = WDMCommonFunctions\UpgradeShortcodeButton::getInstance();

require_once('class-downgrade-ir.php');
$downgrade_ir = WDMCommonFunctions\DowngradeIR::getInstance();

// To track free trial renewals
require_once('class-free-trial-renewal.php');
// Custom CLI Command to add meta to existing free trial renewals
//require_once('update_existing_free_trial_renewals.php');

// Edd GA4 integration
require_once('class-edd-ecommerce-tracking-ga4.php');
$edd_ga4 = WDMCommonFunctions\EDD_Ecommerce_Tracking_GA4::getInstance();

function include_ir_leap(){
    global $post;
    if( !empty($post->ID) && 523051 == $post->ID){
        require_once('class-ir-leap-lp.php');
        $ir_leap_lp = WDMCommonFunctions\IrLeapLp::getInstance();
    }
}
add_action('template_redirect', 'include_ir_leap');

require_once('class-receipt-shortcodes.php');
$receipt_shortcodes = WDMCommonFunctions\ReceiptShortcodes::getInstance();

require_once('class-case-study-cpt.php');
$case_study_cpt = WDMCommonFunctions\CaseStudyCpt::getInstance();

require_once('class-cost-based-prorate-lifetime.php');
$cost_based_prorate_lifetime = WDMCommonFunctions\CostBasedProrateLifetime::getInstance();

function include_subscription_cancellation_feedback(){
    if(function_exists('get_field')){
        if(!empty(get_field('subscription_cancellation_feedback','option'))){
            require_once('class-edd-sub-cancel-feedback.php');
            $edd_unsub_feedback = WDMCommonFunctions\EddSubCancelFeedback::getInstance();
        }
    }
}

add_action('template_redirect', 'include_subscription_cancellation_feedback');

add_action('template_redirect', 'wdmEddCheckoutExitIntentPlugin');

function wdmEddCheckoutExitIntentPlugin()
{
    if (edd_is_checkout()) {
        require_once('class-MyPopupHandler.php');
    }
}


// To override TML Login template
add_filter('tml_shortcode', 'wdm_tml_shortcode', 10, 2);
function wdm_tml_shortcode($content, $action_name)
{
    // global $wp;
    // $checkout_page_url = function_exists('get_field') ? get_field('wisdm_minimal_elementor_checkout_page','option') : '';
    // $current_page_url  = home_url( add_query_arg( array(), $wp->request ) );

    // if NSL_PATH_FILE is defined it means nextend facebook connect social login plugin is active
    if (defined('NSL_PATH_FILE') && $action_name=='login') {
        $checkout_page_url = function_exists('get_field') ? get_field('wisdm_minimal_elementor_checkout_page','option') : '';
        $checkout_page_id  = url_to_postid($checkout_page_url);

        // For checkout page only
        // if ( untrailingslashit($checkout_page_url) == $current_page_url ) {
        if (is_page($checkout_page_id)) {
            ob_start();
            include get_stylesheet_directory() . '/wdm-minimal-login-form.php';
            $content = ob_get_clean();
        } else {
            $content = '<div class="tml tml-login testing-tariq" id="theme-my-login">'.do_shortcode('[nextend_social_login'. $redirect_to .']').'<p class="login-or-para">OR</p>'.$content.'</div>';
        }
    }

    return $content;
}

/**
 * Modify the 'Lost your password?' text to 'Lost Password' text on the login page for TML version > 7.1.3.
 */
function wdm_tml_modify_lost_password_text($links) {
    if ( !empty($links['lostpassword']) ) {
        $links['lostpassword']['text'] = 'Lost Password';
    }
    return $links;
}
add_filter('tml_get_form_links', 'wdm_tml_modify_lost_password_text');


// Note added for indian buyers to contact us in case of paypal payments
// add_action('edd_before_purchase_form', 'wdmEddBeforePurchaseForm', -2);

function wdmEddBeforePurchaseForm()
{
    echo '<p><b>Note for Indian Buyers:</b> If you are not able to complete the payment through PayPal. <a href="https://wisdmlabs.com/neft-purchase/">Then drop an email here.</a></p>';
}
// --------------------------------- Change paypal payment gateway label ---------------------
function edd_gateway_checkout_label_paypal_express($label){
    if('paypal'==strtolower($label)){
        return 'Other';
    }
    return $label;
}
add_filter( 'edd_gateway_checkout_label_paypalexpress', 'edd_gateway_checkout_label_paypal_express', 10 );
// -------------------------------- To notify on post comments --------------------
function wdm_filter_comment_notification_email_to($email_to)
{
    return array_unique(array_merge($email_to, array('helpdesk@wisdmlabs.com')));
}
add_filter('comment_notification_recipients', 'wdm_filter_comment_notification_email_to');

// ----------------------------- Disable autocomplete on search form ----------------------

add_filter('genesis_search_form', 'wdm_search_form_filter', 5);
function wdm_search_form_filter($form)
{

    $document = new DOMDocument();
    $document->loadHTML($form);
    $xpath = new DOMXPath($document);
    $input = $xpath->query('//input[@name="s"]');
    if ($input->length > 0) {
        $input->item(0)->setAttribute('autocomplete', 'off');
    }

    # remove <!DOCTYPE
    $document->removeChild($document->doctype);
    # remove <html><body></body></html>
    $document->replaceChild($document->firstChild->firstChild->firstChild, $document->firstChild);
    $form_html = $document->saveHTML();

    return $form_html;
}

// ----------------------------------- Custom Error Message on Checkout For Stripe ----------------
add_filter('edd_errors', 'wdmEddCustomErros', 999);

function wdmEddCustomErros($errors)
{
    if (isset($errors['edd_stripe_error_limit'])) {
        $errors['edd_stripe_error_limit'] .= ' Payment Problem? <a href="/contact-us">Contact Us</a>';
    }
    if (isset($errors['api_error'])) {
        $errors['api_error'] .= ' Payment Problem? <a href="/contact-us">Contact Us</a>';
    }
    return $errors;
}

add_filter('wdmStripeApiError', 'wdmStripeApiError');

function wdmStripeApiError($msg)
{
    return $msg . ' Payment Problem? <a href="/contact-us">Contact Us</a>';
}
/******************************** Consultation Call in Purchase Receipt *****************************/

require_once 'class-wdm-alter-email-body.php';

/******************************** Consultation Call in Purchase Receipt Ends *************************/
// To enable admin email notifications showing product name only feature
require_once 'class-downloadnamelisttag.php';
// -------------------------------------- Schedule SMSes -------------------------------------
require_once 'class-schedule-sms.php';
$schedule_sms = WDMCommonFunctions\ScheduleSms::getInstance();
//******************************** Fix: redirect to orange for login from RRF like button *******
add_filter('login_url', 'wdm_login_url', 10);

function wdm_login_url($login_url)
{
    if (!is_user_logged_in() && strpos($login_url, 'wdm_review_id_') >= 0) {
        $query = parse_url($login_url, PHP_URL_QUERY);
        return home_url('/login?').$query;
    }
    return $login_url;
}

// ****************************** RRF issue Fix code ends **************************************

// ****************************** Include lead number in form submission fields *******************************

add_filter('wpcf7_posted_data', 'wdmWpcf7PostedData');

function wdmWpcf7PostedData($posted_data)
{
    if (isset($posted_data['wdm-lead-number']) && $posted_data['wdm-lead-number']==0) {
        $posted_data['wdm-lead-number'] = get_option('wdm_cf_count');
    }
    return $posted_data;
}

// ************************ Code including lead number in form submission fields  ends *******************

// ************************** To remove pricing section from upsell crossell section *********************

add_filter('edd_csau_upsell_show_button', '__return_false()', 999);
// ******************** To add custom CF7 tag to get current page url **************
add_action( 'wpcf7_init', 'wdm_wpcf7_add_form_tag_current_url' );
function wdm_wpcf7_add_form_tag_current_url() {
    // Add shortcode for the form [current_url]
    wpcf7_add_form_tag( 'current_url',
        'wdm_wpcf7_current_url_form_tag_handler',
        array(
            'name-attr' => true
        )
    );
}

// Parse the shortcode in the frontend
function wdm_wpcf7_current_url_form_tag_handler( $tag ) {
    global $wp;
    $url = home_url( $wp->request );
    return '<input type="hidden" name="'.$tag['name'].'" value="'.$url.'" />';
}
//  ---------------- Show content after checkout table for free trial products ------------
add_action('edd_after_checkout_cart','wdm_edd_after_checkout_cart');

function wdm_edd_after_checkout_cart(){
    $cart_items = edd_get_cart_contents();
    $title = array();
    foreach($cart_items as $key => $item){
        if(!empty($item['options']['recurring']['trial_period']['quantity'])){
            if($item['id']==10055){
                $title[] = 'WISDM Custom Product Boxes';
            }elseif($item['id']==109665){
                $title[] = 'WISDM Ratings, Reviews, & Feedback';
            }elseif($item['id']==366221){
                $title[] = 'WISDM eLumine + LearnDash Essential Addon Pack (LEAP)';
            }elseif($item['id']==3212){
                $title[] = 'WISDM Product Enquiry Pro';
            }elseif($item['id']==14995){
                $title[] = 'WISDM Quiz Reporting Extension';
            }
        }
    }
    if($title){
        ?>
        <div>
            <p>You are signing up for the <strong>15-Day Free Trial</strong> for <?php echo implode(', ',$title)?>.</p>
            <p>This includes -
                <ul>
                    <li>15 days of <strong>complete access</strong> to all premium plugin features</li>
                    <li><strong>Premium support</strong> for the duration of the Trial</li>
                    <li>Freedom to <strong>cancel anytime</strong> during the Trial period</li>
                </ul>
            </p>
            <p>You will need to enter your credit card details while registering for the Free Trial. After 15 days, i.e. after the duration of the free trial, the <strong>payment for your chosen plan will be deducted automatically and is non-refundable.</strong></p>
        </div>
        <?php
    }
}
// ------------------ To change yearly text to annually ------------------
add_filter( 'gettext', 'wdm_change_edd_yearly', 10, 3 );
function wdm_change_edd_yearly($translation, $text, $domain){
    if(function_exists('edd_is_checkout') && 'edd-recurring'==$domain && edd_is_checkout()){
        if($text=='Yearly'){
            $translation = 'Annually';
        }
    }
    return $translation;
}
// ---------------------- Minimal checkout common functions -------------
function minimal_get_cart_table_details($cart_item){
	$return = array(false,'');
	$download_id = $cart_item['id'];
	$added_download			= new EDD_SL_Download( $download_id );
	$licensing_enabled		= $added_download->licensing_enabled();
	$has_variable_prices	= $added_download->has_variable_prices();
	$is_bundle				= $added_download->is_bundled_download();

	$return[0] = wdm_minimal_get_period($cart_item);
	if ( ! $licensing_enabled ) {
		$return[1] = '';
	}else{
		$return[1] = wdm_minimal_get_license_options($added_download,$cart_item,$has_variable_prices);
	}
	return $return;
}

function wdm_minimal_get_period($cart_item){
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

function wdm_minimal_renewal_notice( $item ){
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

function wdm_minimal_get_license_options($added_download,$cart_item,$has_variable_prices){
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
				$output[$i]['label'] = ($added_download_price_license_type=='business'?($activation_limit/2). ' Business ':$activation_limit) . ' ' . _n( 'License', 'Licenses', $activation_limit);
			}
			$i++;
		}
	}else{
		$output[$i]['value'] = $added_download->ID;
		$activation_limit = $added_download->get_activation_limit();
		if(!empty($activation_limit)){
			$output[$i]['label'] = ($added_download_price_license_type=='business'?($activation_limit/2). ' Business ':$activation_limit) . ' ' . _n( 'License', 'Licenses', $activation_limit);
		}
		$output[$i]['selected'] = 1;
	}
	return $output;
}

function wdm_minimal_get_license_options_html($options=array(),$item_key=0,$mobile=0){
	global $wdm_cart_nonce;
	$html = '';
	if($options){
		$m_id = $mobile?'m_':'';
		foreach ($options as $key => $value) {
			$id = 'license_option_rad_'.$m_id.$item_key.'_'.$key;
			// $radio_image = !empty($value['selected'])?WDMELE_PLUGIN_URL.'assets/images/selected_radio.svg':WDMELE_PLUGIN_URL.'assets/images/radio.svg';
			if($value['label']){
				$html .= '<label class="license_option_label'.(!empty($value['selected'])?' license_option_label_checked':'').'" for="'.$id.'"><span class="license_options'.(!empty($value['selected'])?' license_options_checked':'').'"></span><input data-nonce="'.$wdm_cart_nonce.'" id="'. $id .'" type="radio" class="edd-select-license" name="license_options_'.$item_key.'" value="'.$value['value'].'" '. (!empty($value['selected'])?'checked="true"':'') .'></span> <span class="license_options_quantity">'.$value['label'].'</label>';
			}
		}
	}
	return $html;
}
function wdm_minimal_checkout_get_product_title($download_id=0){
	$title = '';
	if($download_id){
		$title = get_the_title( $download_id );
	}
	return $title;
}
// ------------------ Accordion for contact form page's form ------------------
function wdm_cf7_accordion() {
    // 349684 live site page id
    // 430039 local site page id
    // Live site thank-you-form page id 661204
    if ( !is_admin() && ( is_page('25713') || is_page('13517') || is_page('80859') || is_page('349684') || is_page('1526') || is_page('119457') || is_page('1427') || is_page('93150') || is_page('93174') || is_page('14506') || is_page('186434') || is_page('661204') || is_page('661175') || is_page('597588') ) ) {
        wp_register_style('wpb-jquery-ui-style', get_stylesheet_directory_uri().'/css/jquery-ui.css', false, CHILD_THEME_VERSION);
        wp_enqueue_style('wpb-jquery-ui-style');
        wp_enqueue_style('cf-accordion', get_stylesheet_directory_uri().'/css/cf-accordion.css', false, CHILD_THEME_VERSION);
        wp_enqueue_style('cf-accordion');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script(
            'cf-accordion',
            get_stylesheet_directory_uri() . '/js/cf-accordion.js',
            array('jquery')
        );
    }elseif( !is_admin() && ( is_page_template( 'template-learndash-lms-setup.php' ) || is_page_template( 'service-landing-template.php' ) || is_page_template('service-landing-template-temp.php')) ){
        wp_register_style('wpb-jquery-ui-style', get_stylesheet_directory_uri().'/css/jquery-ui.css', false, CHILD_THEME_VERSION);
        wp_enqueue_style('wpb-jquery-ui-style');
        wp_enqueue_style('cf-accordion', get_stylesheet_directory_uri().'/css/cf-accordion-services.css', false, CHILD_THEME_VERSION);
        wp_enqueue_style('cf-accordion');
        wp_enqueue_script('jquery-ui-accordion');
    }
}
add_action('wp_enqueue_scripts', 'wdm_cf7_accordion');
// ------------------ Custom validation for contact us page contact form ------------------
function wdm_custom_contact_us_radio_validation_filter($result,$tag){
    if ( !isset($_POST['_wpcf7']) || (446234 != $_POST['_wpcf7'] && 661149 != $_POST['_wpcf7'] && 661174 != $_POST['_wpcf7']) ) // Only form id 446234 will be validated.
        return $result;

    if (('about-reason' == $tag->name && !empty($_POST['about-reason']) && $_POST['about-reason']=='Not answered')) {
        $result->invalidate($tag, "Please select an option.");
    }
    if (('about-assistance' == $tag->name && !empty($_POST['about-assistance']) && $_POST['about-assistance']=='Not answered')) {
        $result->invalidate($tag, "Please select an option.");
    }
    if (('about-company' == $tag->name && !empty($_POST['about-company']) && $_POST['about-company']=='Not answered')) {
        $result->invalidate($tag, "Please select an option.");
    }

    if (('about-assist-woocommerce' == $tag->name && !empty($_POST['about-assist-woocommerce']) && 'Not answered' == $_POST['about-assist-woocommerce'])) {
        $result->invalidate($tag, "Please select an option.");
    }
    if (('about-assist-learndash' == $tag->name && !empty($_POST['about-assist-learndash']) && 'Not answered' == $_POST['about-assist-learndash'])) {
        $result->invalidate($tag, "Please select an option.");
    }
    if (('about-assist-wordpress' == $tag->name && !empty($_POST['about-assist-wordpress']) && 'Not answered' == $_POST['about-assist-wordpress'])) {
        $result->invalidate($tag, "Please select an option.");
    }

    return $result;
}
add_filter('wpcf7_validate_radio', 'wdm_custom_contact_us_radio_validation_filter', 9, 2);

// -------------- Modify Radio Button Value to Push to HubSpot Dropdown ----------------
function wdm_custom_modify_radio_button_value($value, $value_orig, $tag) {
    if ( !isset($_POST['_wpcf7']) || ( 661149 != $_POST['_wpcf7'] && 661174 != $_POST['_wpcf7'] ) ) // Only form id 661174 will be validated.
        return $value;

    $radio_button_fields = array('about-assistance', 'about-assist-woocommerce', 'about-assist-learndash', 'about-assist-wordpress');
    if (in_array($tag->name, $radio_button_fields)) {
        $befores = $tag->pipes->collect_befores();
        $afters  = $tag->pipes->collect_afters();

        foreach ( $befores as $index => $before ) {
            if ( false !== strpos($before, $value) ) {
                return $afters[$index];
            }
        }
    }

    return $value;
}

add_filter('wpcf7_posted_data_radio', 'wdm_custom_modify_radio_button_value', 10, 3);
// ----------- End of Modify Radio Button Value to Push to HubSpot Dropdown -------------

/*
// ------------------ Send additional email after 15 minutes ------------------
function wdm_prevent_additional_email($additional_mail, $contact_form)
{
    // contact-us page new form id 661149
    if (!isset($_POST['_wpcf7']) || '661149' != $_POST['_wpcf7']) {
        return $additional_mail;
    }

    foreach ( $additional_mail as $name => $template ) {
        $components = array(
			'subject' => wdm_custom_cf7_mail_get( 'subject', true, $template ),
			'sender' => wdm_custom_cf7_mail_get( 'sender', true, $template ),
			'body' => wdm_custom_cf7_mail_get( 'body', true, $template ),
			'recipient' => wdm_custom_cf7_mail_get( 'recipient', true, $template ),
			'additional_headers' => wdm_custom_cf7_mail_get( 'additional_headers', true, $template ),
			'attachments' => '',
		);
    }

    $components['subject'] = wpcf7_strip_newline( $components['subject'] );
    $components['sender'] = wpcf7_strip_newline( $components['sender'] );
    $components['recipient'] = wpcf7_strip_newline( $components['recipient'] );
    $additional_headers = trim( $components['additional_headers'] );

    // Push the data into database
    global $wpdb;
    $time             = time();
    $custom_mail_data = $wpdb->prefix . 'wdm_custom_cf7_mail_data';

    $wpdb->insert(
        $custom_mail_data,
        array(
            'submit_time' => $time,
            'recipient_email' => $components['recipient'],
            'sender' => $components['sender'],
            'mail_subject' => $components['subject'],
            'additional_headers' => $components['additional_headers'],
            'mail_body' => $components['body'],
            'cf7_id' => '661149', // contact-us page new form id 661149
            'mail_sent' => 'no',
        ),
        array(
            '%f',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s',
        )
    );

    return array();
}
add_filter('wpcf7_additional_mail', 'wdm_prevent_additional_email', 10, 2);

function wdm_custom_cf7_mail_get( $component, $replace_tags = false, $template ) {
    $use_html = ( 'body' == $component );
    $exclude_blank = false;

    $component = isset( $template[$component] ) ? $template[$component] : '';

    if ( $replace_tags ) {
        $component = wdm_custom_cf7_mail_replace_tags( $component, array(
            'html' => $use_html,
            'exclude_blank' => $exclude_blank,
        ) );

        if ( $use_html
        and ! preg_match( '%<html[>\s].*</html>%is', $component ) ) {
            $component = wdm_custom_cf7_mail_htmlize( $component, $template );
        }
    }

    return $component;
}

function wdm_custom_cf7_mail_replace_tags( $content, $args = '' ) {
    if ( true === $args ) {
        $args = array( 'html' => true );
    }

    $args = wp_parse_args( $args, array(
        'html' => false,
        'exclude_blank' => false,
    ) );

    return wpcf7_mail_replace_tags( $content, $args );
}

function wdm_custom_cf7_mail_htmlize( $body, $template ) {
    $header = '<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>' . esc_html( wdm_custom_cf7_mail_get( 'subject', true, $template ) ) . '</title>
</head>
<body>';

    $html = $header . wpautop( $body );
    return $html;
}

// Add interval in cron schedule
function wdm_add_cron_schedule_every_fifteen_mins( $schedules ) {
    // Adds once weekly to the existing schedules.
    $schedules['wdm_fifteen_mins'] = array(
        'interval' => 900,
        'display' => __( 'Every 15 Minutes' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'wdm_add_cron_schedule_every_fifteen_mins' );

// Schedule an event to send a mail to the customers who have filled the first services form only.
function wdm_schedule_contact_us_form_mail() {
    if (! wp_next_scheduled('wdm_schedule_contact_us_form_mail_cron_hook')) {
        $cron_time = current_time('timestamp', true) + 900;
        wp_schedule_event($cron_time, 'wdm_fifteen_mins', 'wdm_schedule_contact_us_form_mail_cron_hook');
    }
}
add_action('init', 'wdm_schedule_contact_us_form_mail');

// Send the mails to customers who have filled the first services form only.
function wdm_execute_contact_us_form_mail() {
    global $wpdb;
    $custom_mail_data = $wpdb->prefix . 'wdm_custom_cf7_mail_data';
    $max_time         = time() - 900;

    $mails_to_sent = $wpdb->get_results( "SELECT id, sender, recipient_email, mail_subject, mail_body, additional_headers  FROM $custom_mail_data WHERE mail_sent='no' AND cf7_id = 661149 AND submit_time < $max_time", ARRAY_A );

    if (!empty($mails_to_sent)) {
        foreach ($mails_to_sent as $mail_data) {
            // Prepare headers
            $headers  = "From: {$mail_data['sender']}\n";
            $headers .= "Content-Type: text/html\n";
            $headers .= "X-WPCF7-Content-Type: text/html\n";

            if ( $mail_data['additional_headers'] ) {
                $headers .= $additional_headers . "\n";
            }

            // Send mails
            wp_mail( $mail_data['recipient_email'], $mail_data['mail_subject'], $mail_data['mail_body'], $headers );

            $data = $wpdb->delete(
                $custom_mail_data,
                array(
                    'id' => $mail_data['id'],
                ),
                array(
                    '%d',
                )
            );
        }
    }
}
add_action('wdm_schedule_contact_us_form_mail_cron_hook', 'wdm_execute_contact_us_form_mail');

// Remove the contact-us page new form (661149) data when customer fills the thank-you-form page form (661174).
function wdm_remove_contact_us_form_mail_data($contact_form) {
    // thank-you-form page form id 661174
    if ( isset($_POST['_wpcf7']) && '661174' == $_POST['_wpcf7'] ) {
        $recipient_email = isset($_POST['your-email']) ? sanitize_email($_POST['your-email']) : '';

        if (empty($recipient_email)) {
            return;
        }

        global $wpdb;
        $custom_mail_data = $wpdb->prefix . 'wdm_custom_cf7_mail_data';

        $data = $wpdb->delete(
            $custom_mail_data,
            array(
                'cf7_id' => 661149, // contact-us page new form id 661149
                'recipient_email' => $recipient_email,
            ),
            array(
                '%d',
                '%s'
            )
        );
    }
}
add_action('wpcf7_mail_sent', 'wdm_remove_contact_us_form_mail_data');

// ------------------ End of Send additional email after 15 minutes ------------------
*/

// ---------------- To move discount code above the Renewing license key field ---------
remove_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );
add_action( 'edd_before_purchase_form', 'edd_discount_field', 1 );
function wdm_edd_software_licensing(){
    remove_action( 'edd_before_purchase_form', 'edd_sl_renewal_form', -1 );
    add_action( 'edd_before_purchase_form', 'edd_sl_renewal_form', 2 );
}
add_action( 'plugins_loaded', 'wdm_edd_software_licensing', 999 );

// ------------ Remove default cf7 checkbox and radio button generator and add custom------------
// CF7 code is in file plugins/contact-form-7/modules/checkbox.php
remove_action( 'wpcf7_init', 'wpcf7_add_form_tag_checkbox', 10, 0 );
add_action( 'wpcf7_init', 'wdm_wpcf7_add_form_tag_checkbox', 10, 0 );

function wdm_wpcf7_add_form_tag_checkbox() {
	wpcf7_add_form_tag( array( 'checkbox', 'checkbox*', 'radio' ),
		'wdm_wpcf7_checkbox_form_tag_handler',
		array(
			'name-attr' => true,
			'selectable-values' => true,
			'multiple-controls-container' => true,
		)
	);
}
function wdm_wpcf7_checkbox_form_tag_handler($tag){
    if ( empty( $tag->name ) ) {
        return '';
    }
    $wpcf7 = WPCF7_ContactForm::get_current();

    $form_id = $wpcf7->id;

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$label_first = $tag->has_option( 'label_first' );
	$use_label_element = $tag->has_option( 'use_label_element' );
	$exclusive = $tag->has_option( 'exclusive' );
	$free_text = $tag->has_option( 'free_text' );
	$multiple = false;

	if ( 'checkbox' == $tag->basetype ) {
		$multiple = ! $exclusive;
	} else { // radio
		$exclusive = false;
	}

	if ( $exclusive ) {
		$class .= ' wpcf7-exclusive-checkbox';
	}

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();

	$tabindex = $tag->get_option( 'tabindex', 'signed_int', true );

	if ( false !== $tabindex ) {
		$tabindex = (int) $tabindex;
	}

	$html = '';
	$count = 0;

	if ( $data = (array) $tag->get_data_option() ) {
		if ( $free_text ) {
			$tag->values = array_merge(
				array_slice( $tag->values, 0, -1 ),
				array_values( $data ),
				array_slice( $tag->values, -1 ) );
			$tag->labels = array_merge(
				array_slice( $tag->labels, 0, -1 ),
				array_values( $data ),
				array_slice( $tag->labels, -1 ) );
		} else {
			$tag->values = array_merge( $tag->values, array_values( $data ) );
			$tag->labels = array_merge( $tag->labels, array_values( $data ) );
		}
	}

	$values = $tag->values;
	$labels = $tag->labels;

	$default_choice = $tag->get_default_option( null, array(
		'multiple' => $multiple,
	) );

	$hangover = wpcf7_get_hangover( $tag->name, $multiple ? array() : '' );

	foreach ( $values as $key => $value ) {
		if ( $hangover ) {
			$checked = in_array( $value, (array) $hangover, true );
		} else {
			$checked = in_array( $value, (array) $default_choice, true );
		}

		if ( isset( $labels[$key] ) ) {
			$label = $labels[$key];
		} else {
			$label = $value;
		}

		$item_atts = array(
			'type' => $tag->basetype,
			'name' => $tag->name . ( $multiple ? '[]' : '' ),
			'value' => $value,
			'checked' => $checked ? 'checked' : '',
			'tabindex' => false !== $tabindex ? $tabindex : '',
        );

        // To remove html from values
        // Live site form id 556900
        // Local site form id 466919
        // Live new contact-us form id 661149
        // Live site thank-you-form page contact form id 661174
        $custom_forms = array('446234','556900', '661149', '661174');
        if(in_array($form_id,$custom_forms)){
            $item_atts['value'] = strip_tags($item_atts['value']);
        }

		$item_atts = wpcf7_format_atts( $item_atts );

        if ( $label_first ) { // put label first, input last

            $item = sprintf(
                '<span class="wpcf7-list-item-label">%1$s</span><input %2$s />',
                esc_html( $label ), $item_atts );
            if(in_array($form_id,$custom_forms)){
                $item = sprintf(
                    '<span class="wpcf7-list-item-label">%1$s</span><input %2$s />',
                    $label, $item_atts );
            }
		} else {
			$item = sprintf(
				'<input %2$s /><span class="wpcf7-list-item-label">%1$s</span>',
                esc_html( $label ), $item_atts );
            if(in_array($form_id,$custom_forms)){
                $item = sprintf(
                    '<input %2$s /><span class="wpcf7-list-item-label">%1$s</span>',
                    $label, $item_atts );
            }
		}

		if ( $use_label_element ) {
			$item = '<label>' . $item . '</label>';
		}

		if ( false !== $tabindex
		and 0 < $tabindex ) {
			$tabindex += 1;
		}

		$class = 'wpcf7-list-item';
		$count += 1;

		if ( 1 == $count ) {
			$class .= ' first';
		}

		if ( count( $values ) == $count ) { // last round
			$class .= ' last';

			if ( $free_text ) {
				$free_text_name = sprintf(
					'_wpcf7_%1$s_free_text_%2$s', $tag->basetype, $tag->name );

				$free_text_atts = array(
					'name' => $free_text_name,
					'class' => 'wpcf7-free-text',
					'tabindex' => false !== $tabindex ? $tabindex : '',
				);

				if ( wpcf7_is_posted()
				and isset( $_POST[$free_text_name] ) ) {
					$free_text_atts['value'] = wp_unslash(
						$_POST[$free_text_name] );
				}

				$free_text_atts = wpcf7_format_atts( $free_text_atts );

				$item .= sprintf( ' <input type="text" %s />', $free_text_atts );

				$class .= ' has-free-text';
			}
        }

        $item = '<span class="' . esc_attr( $class ) . '">' . $item . '</span>';

		$html .= $item;
	}

	$atts = wpcf7_format_atts( $atts );

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><span %2$s>%3$s</span>%4$s</span>',
		sanitize_html_class( $tag->name ), $atts, $html, $validation_error );

	return $html;
}
// ------------ Remove default cf7 hidden and add custom ------------
// remove_action( 'wpcf7_init', 'wpcf7_add_form_tag_hidden', 10, 0 );
// add_action( 'wpcf7_init', 'wdm_wpcf7_add_form_tag_hidden', 10, 0 );

function wdm_wpcf7_add_form_tag_hidden() {
	wpcf7_add_form_tag( 'hidden',
		'wdm_wpcf7_hidden_form_tag_handler',
		array(
			'name-attr' => true,
			'display-hidden' => true,
		)
	);
}

function wdm_wpcf7_hidden_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
    }

	$atts = array();

	$class = wpcf7_form_controls_class( $tag->type );
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();

	$value = (string) reset( $tag->values );
	$value = $tag->get_default_option( $value );
    if( $tag->name == 'wdm-hidden-country-name' ){
        $value = getCurrentUsersCountryName();
    }
	$atts['value'] = $value;

	$atts['type'] = 'hidden';
	$atts['name'] = $tag->name;
	$atts = wpcf7_format_atts( $atts );

	$html = sprintf( '<input %s />', $atts );
	return $html;
}
// ------------ Remove default cf7 hidden and add custom ends ------------

// ------ Remove default cf7msm enqueque script, style and localization -------
function wdm_custom_cf7msm_scripts()
{
    wp_enqueue_style(
        'cf7msm_styles',
        plugins_url( '/resources/cf7msm.css', CF7MSM_PLUGIN ),
        array( 'contact-form-7' ),
        CF7MSM_VERSION
    );
    wp_enqueue_script(
        'cf7msm',
        plugins_url( '/resources/cf7msm.min.js', CF7MSM_PLUGIN ),
        array( 'jquery', 'contact-form-7' ),
        CF7MSM_VERSION,
        true
    );
    $cf7msm_posted_data = cf7msm_get( 'cf7msm_posted_data' );
    if ( empty($cf7msm_posted_data) || is_page('349684') || is_page('661175') || is_page('3442') || is_page('621090') || is_page('397175') ) {
        $cf7msm_posted_data = array();
    }

    wp_localize_script( 'cf7msm', 'cf7msm_posted_data', $cf7msm_posted_data );
}
function wdm_remove_cf7msm_scripts() {
    remove_action( 'wp_enqueue_scripts', 'cf7msm_scripts' );
    add_action( 'wp_enqueue_scripts', 'wdm_custom_cf7msm_scripts' );
}
add_action( 'init', 'wdm_remove_cf7msm_scripts' );
// ------ End of remove default cf7msm enqueque script, style and localization -------

// ---- Use session instead of cookie in Contact Form 7 Multi-Step Forms plugin -----
add_filter('cf7msm_force_session', '__return_true');
add_filter('cf7msm_allow_session', '__return_true');
// ---- End of Use session instead of cookie in Contact Form 7 Multi-Step Forms plugin -----

// ------------------------- Disable renewal tracking in Edd Enhanced Ecommerce Tracking --------
function wdm_disable_edd_enhanced_for_renewal(){
    if(function_exists('EDD_Enhanced_Ecommerce_Tracking')){
		remove_action( 'edd_update_payment_status', array( EDD_Enhanced_Ecommerce_Tracking()->compatibility, 'trigger_transaction_for_recurring' ), 10, 3 );
    }
}
add_action( 'plugins_loaded', 'wdm_disable_edd_enhanced_for_renewal', 999 );

// -------------------- Add country field value in form submission for contact us page form -------
add_filter( 'wpcf7_posted_data', 'update_lead_score_on_country', 10, 1 );
function update_lead_score_on_country($posted_data){
    if (($posted_data['_wpcf7']=='446234' || $posted_data['_wpcf7']=='661149' || $posted_data['_wpcf7']=='886963') && isset($posted_data['country-name']) && $posted_data['country-name']=='' && isset($posted_data['cf_lead_score'])) {
        if (isset($_SERVER['REMOTE_ADDR']) && WP_Http::is_ip_address($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }

        $country = '';
        if (isset($ipaddress) && !empty($ipaddress)) {
            $databaseFile = '/home/accounts/gwcfreed/wisdmlabs/public_html/site/wp-content/plugins/wdm-sendy-to-contact-form-7/libraries/MaxMind-DB-Reader-php-master/src/MaxMind/Db/geo-country/GeoLite2-Country.mmdb';
            // $databaseFile = '/var/www/html/wisdmlabs.com/site/wp-content/plugins/wdm-sendy-to-contact-form-7/libraries/MaxMind-DB-Reader-php-master/src/MaxMind/Db/geo-country/GeoLite2-Country.mmdb';
            // US/ Canada/ EU/ Australia/ New Zealand/ Singapore: 10
            // Middle East/ Malaysia/ Thailand/ Hong Kong/ Mexico: 5

            if(file_exists($databaseFile)){
                $reader = new Reader($databaseFile);
                $country = $reader->get($ipaddress)['country']['names']['en'];
                // $country = $reader->get($ipaddress)['country']['iso_code'];
                $reader->close();
                if (empty($country)) {
                    $country = '';
                }else{
                    $countr_f = array(
                        'United States',
                        'New Zealand',
                        'Australia',
                        'Singapore',
                        'United Kingdom',
                        'Germany',
                        'France',
                        'Spain',
                        'Italy',
                        'Netherlands',
                        'Switzerland',
                        'Ireland',
                        'Sweden',
                        'Greece',
                        'Hungary',
                        'Russia',
                        'Belgium',
                        'Norway',
                        'Austria',
                        'Finland',
                        'Poland',
                        'Portugal',
                        'Romania',
                        'Ukraine'
                    );
                    $countr_s = array(
                        'Malaysia',
                        'Thailand',
                        'Hong Kong',
                        'Mexico',
                        'United Arab Emirates',
                        'Qatar',
                        'Saudi Arabia',
                        'Turkey',
                        'Phillipines',
                        'Israel'
                    );
                    if(empty($posted_data['cf_lead_score'])){
                        $posted_data['cf_lead_score'] = 0;
                    }
                    if(wdm_strposa($country,$countr_f)!==false){
                        $posted_data['cf_lead_score'] = (int)$posted_data['cf_lead_score'] + 10;
                    }elseif(wdm_strposa($country,$countr_s)!==false){
                        $posted_data['cf_lead_score'] = (int)$posted_data['cf_lead_score'] + 5;
                    }else{
                        $posted_data['cf_lead_score'] = (int)$posted_data['cf_lead_score'] + 2;
                    }
                }
            }
        }
        $posted_data['country-name'] = $country;
    }
    return $posted_data;
}
function wdm_strposa($haystack, $needle, $offset=0) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $query) {
        if(stripos($haystack, $query, $offset) !== false) return true; // stop on first true result
    }
    return false;
}
// ----------------- Add custom hidden field for country ------------------
add_filter( 'wpcf7_posted_data', 'update_hidden_country_field_data', 11, 1 );
function update_hidden_country_field_data($posted_data){
    if(($posted_data['_wpcf7']=='446234' || $posted_data['_wpcf7']=='661149' || $posted_data['_wpcf7']=='886963') && isset($posted_data['country-name']) && !empty($posted_data['country-name']) && isset($posted_data['wdm-hidden-country-name']) && $posted_data['wdm-hidden-country-name']==''){
        $posted_data['wdm-hidden-country-name'] = $posted_data['country-name'];
    }elseif(($posted_data['_wpcf7']=='446234' || $posted_data['_wpcf7']=='661149' || $posted_data['_wpcf7']=='840922') && isset($posted_data['wdm-hidden-country-name']) && $posted_data['wdm-hidden-country-name']=='') {
        if (isset($_SERVER['REMOTE_ADDR']) && WP_Http::is_ip_address($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }

        $country = '';
        if (isset($ipaddress) && !empty($ipaddress)) {
            $databaseFile = '/home/accounts/gwcfreed/wisdmlabs/public_html/site/wp-content/plugins/wdm-sendy-to-contact-form-7/libraries/MaxMind-DB-Reader-php-master/src/MaxMind/Db/geo-country/GeoLite2-Country.mmdb';
            // $databaseFile = '/var/www/html/wisdmlabs.com/site/wp-content/plugins/wdm-sendy-to-contact-form-7/libraries/MaxMind-DB-Reader-php-master/src/MaxMind/Db/geo-country/GeoLite2-Country.mmdb';
            if(file_exists($databaseFile)){
                $reader = new Reader($databaseFile);
                $country = $reader->get($ipaddress)['country']['names']['en'];
                // $country = $reader->get($ipaddress)['country']['iso_code'];
                $reader->close();
                if (empty($country)) {
                    $country = '';
                }
            }
        }
        $posted_data['wdm-hidden-country-name'] = $country;
    }
    return $posted_data;
}
// ------------ Add country field value in form submission for contact us page form ends -------
// ---------------------------- Inform admins about upgrade --------------------------
function wdm_edd_sl_license_upgraded($license_id, $args){
    // $license = edd_software_licensing()->get_license( $license_id );
    $license_key = edd_software_licensing()->get_license_key($license_id);
    // If upgraded to lifetime then inform product managers
    if( $license_key && edd_software_licensing()->get_price_is_lifetime( $args['download_id'], $args['upgrade_price_id'] ) ) {
        $mail_to = array('dhawal.bargir@wisdmlabs.com','ryan.warren@wisdmlabs.com','mrunmayee.kulkarni@wisdmlabs.com','sharon.koshy@wisdmlabs.com');
        $subject = 'License upgraded by a client';

        $message = 'A customer has upgraded his/her license with license key <a href="'.admin_url( 'edit.php?post_type=download&page=edd-licenses&view=overview&license_id=' . $license_id ).'">'.$license_key.'</a><br/>';
        $message .= 'Upgraded from product '.get_the_title($args['old_download_id']).' with price id '.$args['old_price_id'].' to product '.get_the_title($args['download_id']).' with price id '.$args['upgrade_price_id'].'<br/>';
        $message .= 'Old Payment is <a href="' . add_query_arg( 'id', $args['old_payment_id'], admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ) . '">' . __( 'View Old Payment', 'easy-digital-downloads' ) . '</a><br/>';
        $message .= 'New Payment is <a href="' . add_query_arg( 'id', $args['payment_id'], admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ) . '">' . __( 'View New Payment', 'easy-digital-downloads' ) . '</a><br/>';
        if (class_exists('EDD_Emails')) {
            $sent = EDD()->emails->send($mail_to, $subject, $message);
        } else {
            $from_name  = get_bloginfo('name');
            $from_email = get_bloginfo('admin_email');
            $headers    = "From: " . stripslashes_deep(html_entity_decode($from_name, ENT_COMPAT, 'UTF-8')) . " <$from_email>\r\n";
            $headers   .= "Reply-To: ". $from_email . "\r\n";
            $sent = wp_mail($mail_to, $subject, $message, $headers);
        }
        if (!$sent) {
            error_log('License upgrade notification emails could not send to product managers.');
        }
	}
    unset($license_key);
}
add_action( 'edd_sl_license_upgraded', 'wdm_edd_sl_license_upgraded', 10, 2 );
// ---------------------------- Inform admins about upgrade ends --------------------------
// ------------------------------ Extend add to cart cookie expiry time ------------------
function wdm_edd_change_expiration_cookie() {
    $cart = edd_get_cart_contents();
    if ( isset( $_COOKIE['edd_items_in_cart'] ) ) {
        $items = $_COOKIE['edd_items_in_cart'];
        @setcookie( 'edd_items_in_cart', $items, time() + 2880 * 60, COOKIEPATH, COOKIE_DOMAIN, false );
    } elseif ( $cart != false ) {
        $items = count( $cart );
        @setcookie( 'edd_items_in_cart', $items, time() + 2880 * 60, COOKIEPATH, COOKIE_DOMAIN, false );
    }
}
add_action( 'init', 'wdm_edd_change_expiration_cookie' );
// ------------------- Extend add to cart cookie expiry time code ends -----------------
// ---------------------------- Add calender link in CF7 sms -----------------------------
add_filter('wpcf7_special_mail_tags', 'wpcf7_tag_sup_member_tags', 10, 3);
function wpcf7_tag_sup_member_tags($output, $name, $html)
{
    $name = preg_replace('/^wpcf7\./', '_', $name); // for back-compat

    // if(class_exists('WPCF7_Submission')){
    //     $submission = WPCF7_Submission::get_instance();
    // }

    // if (! $submission) {
    //     return $output;
    // }

    if ('calender_link' == $name) {
        $support_member_sequence = get_field('support_member_sequence', 'option');
        $curr_supp_mem_seq_key = get_option('curr_supp_mem_seq_key');
        if ($curr_supp_mem_seq_key!==false) {
            if (!empty($support_member_sequence[$curr_supp_mem_seq_key]['calender_link'])) {
                return $support_member_sequence[$curr_supp_mem_seq_key]['calender_link'];
            } else {
                if (!empty($support_member_sequence[0])) {
                    update_option('curr_supp_mem_seq_key', 0);
                    $curr_supp_mem_seq_key = get_option('curr_supp_mem_seq_key');
                    if (!empty($support_member_sequence[$curr_supp_mem_seq_key]['calender_link'])) {
                        return $support_member_sequence[$curr_supp_mem_seq_key]['calender_link'];
                    }
                }
                return '';
            }
        } else {
            if (!empty($support_member_sequence)) {
                update_option('curr_supp_mem_seq_key', 0);
            }
        }
        // $calender_links = array("https://app.hubspot.com/meetings/pooja-sahasrabuddhe", "https://app.hubspot.com/meetings/shishir-nayak");
        // $rand_key = array_rand($calender_links);
        // return $calender_links[$rand_key];
    } elseif ('wdm_sup_mem_name' == $name) {
        $support_member_sequence = get_field('support_member_sequence', 'option');
        $curr_supp_mem_seq_key = get_option('curr_supp_mem_seq_key');
        if ($curr_supp_mem_seq_key!==false) {
            if (!empty($support_member_sequence[$curr_supp_mem_seq_key]['member_name'])) {
                return $support_member_sequence[$curr_supp_mem_seq_key]['member_name'];
            } else {
                return '';
            }
        } else {
            if (!empty($support_member_sequence)) {
                update_option('curr_supp_mem_seq_key', 0);
            }
        }
    } elseif ('wdm_new_line'== $name ){
        return "\n";
    }

    return $output;
}
// ---------------------------- Add calender link in CF7 sms ends ------------------------
//---------------------------- Get country from ip -------------------
function getCurrentUsersCountryName(){
    $ipaddress = '';
    if (isset($_SERVER['REMOTE_ADDR']) && \WP_Http::is_ip_address($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }

    $country = '';
    if (isset($ipaddress) && !empty($ipaddress)) {
        $databaseFile = '/home/accounts/gwcfreed/wisdmlabs/public_html/site/wp-content/plugins/wdm-sendy-to-contact-form-7/libraries/MaxMind-DB-Reader-php-master/src/MaxMind/Db/geo-country/GeoLite2-Country.mmdb';
        if($_SERVER["SERVER_ADDR"] == '127.0.0.1'){
            $databaseFile = '/var/www/html/wisdmlabs.com/site/wp-content/plugins/wdm-sendy-to-contact-form-7/libraries/MaxMind-DB-Reader-php-master/src/MaxMind/Db/geo-country/GeoLite2-Country.mmdb';
        }

        $reader = new Reader($databaseFile);
        $country = $reader->get($ipaddress)['country']['names']['en'];
        // $country = $reader->get($ipaddress)['country']['iso_code'];
        $reader->close();

        if (empty($country)) {
            $country = '';
        }
    }
    return $country;
}
//---------------------------- Get country from ip ends -------------------
// ------------------------ Mautic common functions -----------------------------
function makeCurl($url, $data, $ipaddress)
{
    if (empty($url) || empty($data) || empty($ipaddress)) {
        // curl_close($ch);
        return;
    }
    // try {
    $curlh = curl_init();
    curl_setopt($curlh, CURLOPT_URL, $url);
    curl_setopt($curlh, CURLOPT_POST, 1);
    curl_setopt($curlh, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curlh, CURLOPT_HTTPHEADER, array("X-Forwarded-For: $ipaddress"));
    curl_setopt($curlh, CURLOPT_TIMEOUT, 5); //timeout in seconds
    curl_setopt($curlh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlh, CURLOPT_FOLLOWLOCATION, 0);
    curl_exec($curlh);
    // update_option('contact_mautic',$response);
    curl_close($curlh);
}

function getRemoteIpAddress()
{
    $ipHolders = array(
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED',
    'REMOTE_ADDR'
    );
    foreach ($ipHolders as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                // Multiple IPs are present so use the last IP which should be the most reliable IP that last connected to the proxy
                $ips = explode(',', $ip);
                array_walk($ips, create_function('&$val', '$val = trim($val);'));
                $ip = end($ips);
            }
            $ip = trim($ip);
            break;
        }
    }
    return $ip;
}

add_action('wp_footer', 'wdm_load_custom_copied_gtm_js_77885');
/**
 * The function loads the custom GTM JS.
 * The code present in the file /plugins/wdm-common-functions/assets/js/wdm-custom-gtm.js
 * has been taken from the original
 * https://www.googletagmanager.com/gtm.js?id=GTM-5HBRP3 file.
 * This is because Brave browser and some extensions block the
 * Google Tag Manager script. Due to this, we are not able to
 * track the 'wdm_utm_source_medium' value whenever a user fills
 * the form.
 * We need to update the code in the file /plugins/wdm-common-functions/assets/js/wdm-custom-gtm.js
 * whenever we make changes in the Google Tag Manager or whenever
 * Google makes changes in the its code for Google Tag Manager.
 */
function wdm_load_custom_copied_gtm_js_77885() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function(){
        setTimeout(
            function(){
                if ("undefined" === typeof google_tag_manager) {
                    let s   = document.createElement( "script" );
                    let src = "/site/wp-content/plugins/wdm-common-functions/assets/js/wdm-custom-gtm.js";
                    s.setAttribute( "src", src );
                    document.body.appendChild( s );
                }
            },
            5000
        );
    });
    </script>
    <?php
}




function wdm_custom_js_qre_elementor_page_895404() {
    if ( is_page( '895404' ) ) {
        ?>
        <style>
        .pricing-tables-2{
            display: none;
        }
        </style>
        <script id="qre-custom-js-elementor-page-895404">
            jQuery(document).ready( function() {
                jQuery('.price-toggle').on('click', function() {
                    jQuery(this).toggleClass('monthly');
                    jQuery('.pricing-tables').toggle();
                });
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'wdm_custom_js_qre_elementor_page_895404');

include_once 'class-edd-reports-api.php';
include_once 'class-edd-reports.php';
include_once 'class-case-studies.php';

require_once('sale/sale.php');
WDMCommonFunctions\WdmSale::getInstance();

require_once 'class-whats-new-features-tab-content.php';


add_action('plugins_loaded', 'wdm_remove_questions', 15);

function wdm_remove_questions() {
    remove_action('edd_purchase_form', 'edd_show_purchase_form', 10);
    remove_action('edd_purchase_form_login_fields', 'edd_get_login_fields', 10);
    remove_action('edd_purchase_form_register_fields', 'edd_get_register_fields', 10);
    remove_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );
    remove_action( 'wpcf7_init', 'wpcf7_add_form_tag_checkbox', 10, 0 );
    if (class_exists('EDD_2Checkout_Gateway')) {
        remove_action('plugins_loaded', 'edd_2checkout_load');
        remove_action('wp_ajax_edd_load_gateway', 'edds_prb_load_gateway', 5);
        remove_action('wp_ajax_nopriv_edd_load_gateway', 'edds_prb_load_gateway', 5);
        $gateway = new EDD_2Checkout_Gateway;
        remove_action('edd_2checkout_onsite_cc_form', array( $gateway, 'card_form' ));
        unset($gateway);
        add_action('edd_2checkout_onsite_cc_form', 'wdm_2checkout_card_form');
    }

}


// Enable font size & font family selects in the editor.
add_filter( 'mce_buttons_2', function( $buttons ) {
	array_unshift( $buttons, 'fontselect' );
	array_unshift( $buttons, 'fontsizeselect' );
	return $buttons;
} );

require_once 'includes/ga4/index.php';
