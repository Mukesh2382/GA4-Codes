<?php
namespace WDMCommonFunctions;

/**
* Class to handle Elumine Renewals Notification to admin, Lpp to Leap
*/

class ElumineLppLeap
{
    // To store current class object
    private static $instance;
    public $view_payment_link;
    
    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        $this->view_payment_link = 'https://wisdmlabs.com/site/wp-admin/edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=';
        $this->emails = array('ryan.warren@wisdmlabs.com','tariq.kotwal@wisdmlabs.com');
        add_action('edd_subscription_post_renew', array($this,'elumine_goldmine_renewal_notify'), 10, 4);
        // Cron to notify admin about Elumine Goldmine renewal
        add_action('elumine_goldmine_form_submit', array($this,'notify_admin'), 10);
        add_action('edd_payment_receipt_after', array( $this, 'remove_edd_sl_show_keys_on_receipt'), 9);
        add_action('edd_payment_receipt_after', array( $this, 'edd_sl_show_keys_on_receipt'), 11, 2);
        add_filter('edd_sl_manage_template_payment_licenses', array($this,'edd_sl_manage_template_payment_licenses'), 10, 2);
        add_filter('edd_user_can_view_receipt_item', array($this,'edd_user_can_view_receipt_item'), 999, 2);
    }

    public function elumine_goldmine_renewal_notify($sub_id, $expiration, $subscription, $payment_id)
    {
        $download = edd_get_download($subscription->product_id);
        // Lpp Plus 341418.
        // Lpp 289448.
        // Treasure Chest 162694.
        // Goldmine 162696.
        if (162696==$download->ID) {
            wp_schedule_single_event(time() + 0, 'elumine_goldmine_form_submit', array($payment_id));
        }
    }

    public function notify_admin($payment_id)
    {
        $message = $this->get_email_body($payment_id);
        $subject = $this->get_email_subject();
        $sent = 0;
        if (class_exists('EDD_Emails')) {
            $sent = EDD()->emails->send($this->emails, $subject, $message);
        } else {
            $from_name  = get_bloginfo('name');
            $from_email = get_bloginfo('admin_email');
            $headers    = "From: " . stripslashes_deep(html_entity_decode($from_name, ENT_COMPAT, 'UTF-8')) . " <$from_email>\r\n";
            $headers   .= "Reply-To: ". $from_email . "\r\n";
            $sent = wp_mail($email_to, $subject, $message, $headers);
        }
        if (!$sent) {
            error_log('Goldmine - Renewal Notice could not send to Admin for payment id ' . $payment_id);
        }
    }

    public function get_email_body($payment_id)
    {
        return '<p>Check renewal payment details <a href="'. $this->view_payment_link . $payment_id .'">here</a>.</p>';
    }

    public function get_email_subject()
    {
        return 'eLumine - Goldmine Renewal Occurred';
    }

    public function remove_edd_sl_show_keys_on_receipt()
    {
        remove_action('edd_payment_receipt_after', 'edd_sl_show_keys_on_receipt', 10);
    }

    public function edd_sl_show_keys_on_receipt($payment, $edd_receipt_args)
    {
        if (empty($payment) || empty($payment->ID)) {
            return;
        }
    
        $licensing = edd_software_licensing();
        $licenses  = apply_filters('edd_sl_licenses_of_purchase', $licensing->get_licenses_of_purchase($payment->ID), $payment, $edd_receipt_args);
        $wdm_can_show_downloads = 1;
        list($wdm_can_show_downloads,$res_prods) = $this->wdm_can_show_downloads($payment->ID);
        if (! empty($licenses)) {
            echo '<tr class="edd_license_keys">';
                echo '<td colspan="2"><strong>' . __('License Keys:', 'edd_sl') . '</strong></td>';
            echo '</tr>';
            foreach ($licenses as $license) {
                if (!$wdm_can_show_downloads && in_array($license->get_download()->ID, $res_prods)) {
                    continue;
                }
                $license_notes = apply_filters('edd_license_notes' , "" , $license);
                echo '<tr class="edd_license_key">';
                    echo '<td>';
                        echo '<span class="edd_sl_license_title">' . $license->get_download()->get_name() . '</span>&nbsp;';
                if ($license->get_download()->has_variable_prices()) {
                    echo '<span class="edd_sl_license_price_option">&ndash;&nbsp;' . edd_get_price_option_name($license->get_download()->ID, $license->price_id) . '</span>';
                }
                if ('expired' == $license->status) {
                    echo '<span class="edd_sl_license_key_expired">&nbsp;(' . __('expired', 'edd_sl') . ')</span>';
                } elseif ('disabled' === $license->status) {
                    echo '<span class="edd_sl_license_key_revoked">&nbsp;(' . __('disabled', 'edd_sl') . ')</span>';
                }
                    echo $license_notes;
                    echo '</td>';
                if ($license) {
                    echo '<td>';
                        echo '<span class="edd_sl_license_key">' . $license->key . '</span>';
                    echo '</td>';
                } else {
                    echo '<td><span class="edd_sl_license_key edd_sl_none">' . __('none', 'edd_sl') . '</span></td>';
                }
                echo '</tr>';
            }
        }
    }

    public function purchased_after_2020($payment_id)
    {
        $subs_db = new \EDD_Subscriptions_DB;
        $subs    = $subs_db->get_subscriptions(array( 'parent_payment_id' => $payment_id, 'order' => 'ASC' ));
        unset($subs_db);
        foreach ($subs as $sub) {
            $payments = $sub->get_child_payments();
            if(!$payments){
                $meta = edd_get_payment_meta( $payment_id );
                if (strtotime($meta['date']) >= strtotime("1 January 2020")) {
                    return 1;
                }
            }
            foreach ($payments as $payment) {
                if (strtotime($payment->date) >= strtotime("1 January 2020")) {
                    return 1;
                }
            }
        }
        return 0;
    }

    public function wdm_can_show_downloads($payment_id)
    {
        $treasure_chest = $lpp = $purchased_after_jan1_2020 = 0;
        $wdm_can_show_downloads = 1;
        $payment_obj   = edd_get_payment($payment_id);
        
        // To check if lifetime or any subscription
        $db            = new \EDD_Subscriptions_DB;
        $args          = array(
            'parent_payment_id' => $payment_id,
            'order'             => 'ASC'
        );
        $subscriptions = $db->get_subscriptions($args);
        unset($db);
        // wisdm code to check Lpp, Lpp Plus and Elumine Treasure Chest
        // Lpp Plus 341418.
        // Lpp 289448.
        // Treasure Chest 162694.
        $purchased_after_jan1_2020 = $this->purchased_after_2020($payment_id);
        // LDGR 44670
        // QRE 14995
        // LDCC 34202
        $res_prods = array(44670,14995,34202);
        
        if (!empty($payment_obj->downloads) && $subscriptions) {
            foreach ($payment_obj->downloads as $download) {
                if ($download['id']==162694) {
                    $treasure_chest = 1;
                } elseif ($download['id']==289448 || $download['id']==341418) {
                    $lpp = 1;
                }
            }
        }
        if (!$purchased_after_jan1_2020 && ($lpp || $treasure_chest)) {
            // If purchased bedore jan 1 2020 and is not lifetime
            if ($subscriptions!==false) {
                $wdm_can_show_downloads = 0;
            }
        }
        unset($payment_obj);
        unset($subscriptions);
        unset($args);
        return array($wdm_can_show_downloads,$res_prods);
    }

    public function edd_sl_manage_template_payment_licenses($keys, $payment_id)
    {
        $edd_sl = edd_software_licensing();
        list($wdm_can_show_downloads,$res_prods) = $this->wdm_can_show_downloads($payment_id);
        foreach ($keys as $key => $license) {
            $download_id = $edd_sl->get_download_id($license->ID);
            if (!$wdm_can_show_downloads && in_array($download_id, $res_prods)) {
                unset($keys[$key]);
            }
        }
        unset($edd_sl);
        unset($res_prods);
        return $keys;
    }
    
    /**
     * edd_user_can_view_receipt_item
     *
     * @param  bool $can_view
     * @param  array $item
     * @return void
     */
    public function edd_user_can_view_receipt_item($can_view, $item)
    {
        global $edd_receipt_args;
        $payment   = get_post($edd_receipt_args['id']);
        if (empty($payment->ID)) {
            return $can_view;
        }
        list($wdm_can_show_downloads,$res_prods) = $this->wdm_can_show_downloads($payment->ID);
        $item_id = explode('_', $item['id']);
        if (!empty($item_id[0]) && !$wdm_can_show_downloads && in_array($item_id[0], $res_prods)) {
            $can_view = false;
        }
        return $can_view;
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ElumineLppLeap;
        }
        return self::$instance;
    }
}
