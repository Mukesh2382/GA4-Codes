<?php
namespace WDMCommonFunctions;

/**
 * Class to handle favourite product feature functionality
 */

class MoreEddCartPurchaseOptions
{
    // To store current class object
    private static $instance;
    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        // Show more options dropdown
        add_filter('wdm_edd_checkout_cart_item_show_more_option', array($this,'showMoreOptions'));
        add_filter('edd_checkout_cart_columns', array($this,'eddCheckoutCartColumns'));
        add_action('wp_enqueue_scripts', array($this,'wdmEddLoadScripts'));
        add_action('edd_post_remove_from_cart', array($this,'wdmEddPostRemoveFromCart'));
        // add_filter('edd_purchase_variable_prices', array($this,'wdmEddPurchaseVariablePrices'), 10);
    }

    public function showMoreOptions($item)
    {
        $random_string = rand(1,10);
        $edd_var = edd_get_variable_prices($item['id']);
        $dropdown_options = $radio_options = '';
        $nonce = wp_create_nonce('wdm-more-options-checkout-nonce');
        if (!empty($edd_var[$item['options']['price_id']])) {
            $current_option = $edd_var[$item['options']['price_id']];
            unset($edd_var[$item['options']['price_id']]);
            $cur_is_single = strpos(strtolower($current_option['name']), 'single') !== false;
            $cur_is_business = strpos(strtolower($current_option['name']), 'business') !== false;
            foreach ($edd_var as $price_id => $var_option) {
                $var_option_name = strtolower($var_option['name']);
                $limit = '';
                if ((!empty($var_option['recurring']) && $current_option['recurring']==$var_option['recurring']) || (!empty($var_option['is_lifetime']) && $current_option['is_lifetime']==$var_option['is_lifetime'])) {
                    if ($cur_is_business && strpos($var_option_name, 'business') !== false) {
                        $limit = ((int)$var_option['license_limit']/2) . ' Business License (Staging+Production)';
                    } elseif ($cur_is_single && strpos($var_option_name, 'single') !== false) {
                        $limit = $var_option['license_limit'] . ' Single Site License';
                    }
                    if ($limit) {
                        $radio_options .= '<input data-nonce="'.$nonce.'" data-parent-option-id="'.$item['options']['price_id'].'" data-option-id="'.$price_id.'" data-download-id="'.$item['id'].'" type="radio" id="'.$item['id'].'_'.$price_id.'" class="selected_more_options" name="selected_more_options_'.$item['id'].'_'.$random_string.'" value="'.$item['id'].'_'.$price_id.'"><label for="'.$item['id'].'_'.$price_id.'">'.$limit.'</label><br>';
                        // $dropdown_options .= '<option data-nonce="'.$nonce.'" data-parent-option-id="'.$item['options']['price_id'].'" data-option-id="'.$price_id.'" data-download-id="'.$item['id'].'" value="'.$item['id'].'_'.$price_id.'">'.$limit.'</option>';
                    }
                }
            }
        }
        if (!empty($current_option)) {
            $limit = $current_option['license_limit'];
            if (strpos(strtolower($current_option['name']), 'business') !== false) {
                $limit = ((int)$current_option['license_limit']/2) . ' Business License (Staging+Production)';
            } elseif (strpos(strtolower($current_option['name']), 'single') !== false) {
                $limit = $current_option['license_limit'] . ' Single Site License';
            } else {
                $limit = $current_option['name'];
            }
            // $item = '<select name="selected_more_options"><option disabled="disabled" value="" selected>'.$limit.'</option>'.$dropdown_options.'</select>';
            $item = '<input type="radio" id="selected_option_id" class="selected_more_options" name="selected_more_options_'.$item['id'].'_'.$random_string.'" value="" checked="checked"><label for="selected_option_id">'.$limit.'</label><br>'.$radio_options;
        } else {
            $item = '';
        }
        unset($edd_var);
        unset($current_option);
        return $item;
    }

    public function wdmEddPostRemoveFromCart()
    {
        if (!empty($_GET['wdm_nonce']) && !empty($_GET['wdm_add_cart_dnld'])  && !empty($_GET['wdm_add_cart_optn'])) {
            if (!wp_verify_nonce($_GET['wdm_nonce'], 'wdm-more-options-checkout-nonce')) {
                die('Security check failed');
            } else {
                $options['quantity'] = 1;
                $options['price_id'] = $_GET['wdm_add_cart_optn'];
                edd_add_to_cart($_GET['wdm_add_cart_dnld'], $options);
            }
        }
    }

    public function eddCheckoutCartColumns($cols)
    {
        unset($cols);
        return 4;
    }

    public function wdmEddLoadScripts()
    {
        $in_footer = edd_scripts_in_footer();
        if (edd_is_checkout()) {
            wp_register_script('wdm-edd-checkout-more-option', plugin_dir_url(__FILE__).'assets/js/wdm-more-options-checkout.js', array( 'jquery' ), EDD_VERSION, $in_footer);
            wp_enqueue_script('wdm-edd-checkout-more-option');
        }
    }
    
    public function wdmEddPurchaseVariablePrices($prices)
    {
        if (!is_array($prices)) {
            return $prices;
        }

        foreach ($prices as $key => $price) {
            if ($price['license_limit'] == 1) {
                unset($prices[$key]);
            }
        }
        return $prices;
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new MoreEddCartPurchaseOptions;
        }
        return self::$instance;
    }
}
