<?php
namespace WDMCommonFunctions;

/**
* Class to handle favourite product feature functionality
*/

class AllowFreeFccToIr
{
    // To store current class object
    private static $instance;
    public $current_customer_email;
    public $current_license;
    public $codes;
    public $IRDownloadID;
    public $FCCDownloadID;
    public $currentCart;
    
    // To add expensive codes and to prevent direct object instantiation
    private function __construct($disid, $code)
    {
        // $codes['FreeFccToIR2019'] = array(
        //                                 'id' => '294677',
        //                                 'code' => 'FreeFccToIR2019'
        //                             );
        $this->IRDownloadID = 20277;
        $this->FCCDownloadID = 33523;
        $this->codes[$code] = array(
                                'id' => $disid,
                                'code' => $code
                            );
        $this->current_customer_email = $this->current_license = '';
        add_action('wp', array($this,'wdmEddFcToIrAddCheckoutDiscount'));
        // add_action( 'wp_loaded', array($this,'sendFccToIrUpgradeNotice') );
        // add_action('edd_recurring_daily_scheduled_events', array( $this, 'sendFccToIrUpgradeNotice' ), 11);
        add_filter('edd_is_discount_valid', array($this,'wdmEddIsDiscountValid'), 10, 3);
        add_action('edd_before_checkout_cart', array($this,'wdmEddBeforeCheckoutCart'));
    }

    public function wdmEddIsDiscountValid($return, $disID, $code)
    {
        if ($disID==$this->codes[$code]['id']) {
            // $cart =  EDD()->session->get('edd_cart');
            $this->currentCart =  EDD()->session->get('edd_cart');
            if (count($this->currentCart) > 1) {
                foreach ($this->codes as $discount_code => $discount) {
                    edd_unset_cart_discount($discount_code);
                }
                unset($discount);
                return false;
            }
            foreach (( array ) $this->currentCart as $item) {
                if (!isset($item['options']['is_upgrade']) && $item['id']==$this->IRDownloadID) {
                    edd_set_error('edd-discount-error', _x('This discount is invalid.', 'error for when a discount is invalid based on its configuration', 'easy-digital-downloads'));
                    return false;
                }
            }
            unset($this->currentCart);
        }
        return $return;
    }

    public function wdmEddFcToIrAddCheckoutDiscount()
    {
        if (is_singular()) {
            $purchase_page = edd_get_option('purchase_page', false);
            if ($purchase_page  && is_page($purchase_page)) {
                $codes =  $this->wdmEddFccToIrGetActiveDiscounts();
                if (! $codes) {
                    $this->wdmEddFccToIrAutoApplyDiscount();
                } else {
                    $this->currentCart =  EDD()->session->get('edd_cart');
                    foreach ($codes as $code) {
                        if (!empty($this->codes[$code])) {
                            // $cart =  EDD()->session->get('edd_cart');
                            if (count($this->currentCart) > 1) {
                                foreach ($this->codes as $discount_code => $discount) {
                                    edd_unset_cart_discount($discount_code);
                                }
                                unset($discount);
                            }
                        }
                    }
                    unset($this->currentCart);
                }
            }
        }
    }

    public function wdmEddFccToIrGetActiveDiscounts()
    {
        $_codes = EDD()->session->get('cart_discounts');
        $codes = array();
        if (is_string($_codes)) {
            $codes = explode('|', $_codes);
        }
        if (is_array($codes)) {
            $codes = array_filter($codes);
        }

        if (! empty($codes)) {
            return $codes;
        } else {
            return false;
        }
    }

    public function wdmIsFccToIrEddAutoApplyDiscountCode()
    {
        if (EDD()->session->get('edd_is_renewal')) {
            return false;
        }

        // $cart =  EDD()->session->get('edd_cart');
        $this->currentCart =  EDD()->session->get('edd_cart');
        if (count($this->currentCart) > 1) {
            foreach ($this->codes as $discount_code => $discount) {
                edd_unset_cart_discount($discount_code);
            }
            unset($discount);
            return false;
        }
        foreach (( array ) $this->currentCart as $item) {
            if (isset($item['options']['is_upgrade']) && $item['options']['is_upgrade'] && $item['id']==$this->IRDownloadID) {
                // Get download if from license id $item['options']['license_id'] if it is 33523
                // then
                $download = edd_software_licensing()->get_license($item['options']['license_id'])->download->ID;
                if ($download==$this->FCCDownloadID) {
                    return true;
                }
                return false;
            }
        }
        unset($this->currentCart);
        return false;
    }

    public function wdmEddFccToIrAutoApplyDiscount()
    {
        $customer = EDD()->session->get('customer');
        $customer = wp_parse_args($customer, array( 'first_name' => '', 'last_name' => '', 'email' => '' ));

        if (is_user_logged_in()) {
            $user_data = get_userdata(get_current_user_id());
            foreach ($customer as $key => $field) {
                if ('email' == $key && empty($field)) {
                    $customer[ $key ] = $user_data->user_email;
                } elseif (empty($field)) {
                    $customer[ $key ] = $user_data->$key;
                }
            }
        }
        $customer = array_map('sanitize_text_field', $customer);
        $user = $customer['email'];
        $is_multiple_discounts_allowed =  edd_multiple_discounts_allowed();
        $set = false;

        if (! $this->wdmIsFccToIrEddAutoApplyDiscountCode()) {
            foreach ($this->codes as $discount_code => $discount) {
                edd_unset_cart_discount($discount_code);
            }
        } else {
            foreach ($this->codes as $discount_code => $discount) {
                if (edd_is_discount_valid($discount_code, $user)) {
                    edd_set_cart_discount($discount_code);
                    $set = true;
                    if (! $is_multiple_discounts_allowed) {
                        edd_unset_error('edd-discount-error');
                        return true;
                    }
                }
            }
        }

        edd_unset_error('edd-discount-error');
        return $set;
    }

    public function sendFccToIrUpgradeNotice()
    {
        $license_args = array(
            'number'  => -1,
            'offset'  => 0,
            'search'  => '',
            'orderby' => 'id',
            'order'   => 'ASC',
            // 'parent'         => 0,
            'status'        => 'active',
            'download_id'   => $this->FCCDownloadID
        );
        $licenses = edd_software_licensing()->licenses_db->get_licenses($license_args);
        $edd_emails   = EDD()->emails;
        // $subject = 'Time to Upgarde to Better and Bigger WISDM Instructor Role Plugin';
        foreach ($licenses as $license) {
            $customer = new \EDD_Customer($license->customer_id);
            $this->current_customer_email = $customer->email;
            $this->current_license = $license;
            // edd_debug_log(print_r($this->getFccToIrUpgradeInviteEmailBody(), true));
            // Send email then set user meta
            if (!get_user_meta($customer->user_id, 'wdm_fcc_to_ir_invited', true) && !$this->isUserUnsubscribed($customer->email) /*&& $edd_emails->send($customer->email, $subject, $this->getFccToIrUpgradeInviteEmailBody())*/) {
                edd_debug_log('FCC To IR invitation sent to: '.$customer->email);
                edd_debug_log($this->getFccToIrUpgradeInviteEmailBody());
                // update_user_meta($customer->user_id, 'wdm_fcc_to_ir_invited', 1);
            }
            /*else{
                update_user_meta($customer->user_id,'wdm_fcc_to_ir_invited',0);
            }*/
        }
        unset($customer);
        unset($this->current_customer_email);
        unset($this->current_license);
        unset($edd_emails);
        unset($licenses);
        unset($license_args);
    }

    public function getFccToIrUpgradeInviteEmailBody()
    {
        if (!empty($this->current_customer_email) && !empty($this->current_license)) {
            $upgrade_id = 1;
            $replace_with = edd_sl_get_license_upgrade_url($this->current_license->ID, $upgrade_id);
        } else {
            $replace_with = 'https://wisdmlabs.com/my-account';
        }
        return str_replace('{{upgrade_link}}', $replace_with, 'We want you to know that we\'ve retired Front-end Course Creation. As our valued customer, who believed in the product, we\'re not leaving you stranded.

        We\'re offering you a FREE upgrade to the Instructor Role plugin. 
        Instructor Role offers the same functionality as Front-end Course Creation and provides the same level of security. In fact, the plugin also offers a cool-new dashboard that makes the life of an instructor a whole lot easier. 

        If you have an active Front-end Course Creation license or a lifetime license, you can continue using the same license for Instructor Role. We\'ve got detailed <a href="https://wisdmlabs.com/front-end-course-creation-for-learndash/">instructions to help you</a> migrate. 

        It\'s time to embrace the change. 

        <a href="{{upgrade_link}}">Click To Upgrade</a>');
    }

    public function wdmEddBeforeCheckoutCart()
    {
        $this->currentCart =  EDD()->session->get('edd_cart');
        foreach (( array ) $this->currentCart as $item) {
            if (isset($item['options']['is_upgrade']) && $item['options']['is_upgrade'] && $item['id']==$this->IRDownloadID) {
                // Get download if from license id $item['options']['license_id'] if it is 33523
                // then
                $download = edd_software_licensing()->get_license($item['options']['license_id'])->download->ID;
                if ($download==$this->FCCDownloadID) {
                    echo '<div class="alert alert-warning" role="alert">FREEFCCTOIR2019 Discount Code is applicable on only upgrade from Front-End Course Creation to Instructor Role for LearnDash Plugin</div>';
                }
            }
        }
        if (count($this->currentCart) > 1) {
            foreach ($this->codes as $discount_code => $discount) {
                edd_unset_cart_discount($discount_code);
            }
            unset($discount);
            return false;
        }
        unset($this->currentCart);
        return false;
    }

    public function isUserUnsubscribed($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $basicauth = 'Basic ' . base64_encode('wisdmadmin:GbCFSi1VXaVLe5ol');

            $headers = array(
                'Authorization' => $basicauth,
                'Content-type' => 'application/x-www-form-urlencoded'
            );
            $wdm_post_data = array(
                'method' => 'POST',
                'headers'  => $headers,
                'body' => array('email'=>$email,'wdm_unsub_rest_auth'=>'hsxh6ygNCI1CbC90'),
                'timeout' => 5
            );
            $response = wp_remote_post('https://subscribe.wisdmlabs.com/nimdamdsiw/wdmapi.php', $wdm_post_data);
            if (is_wp_error($response)) {
                return 1;
            } else {
                if ($response['body']==0) {
                    return 0;
                }
            }
        }
        return 1;
    }
    
    // To get object of the current class
    public static function getInstance($disid, $code)
    {
        if (!isset(self::$instance)) {
            self::$instance = new AllowFreeFccToIr($disid, $code);
        }
        return self::$instance;
    }
}

// AllowFreeFccToIr::getInstance();
