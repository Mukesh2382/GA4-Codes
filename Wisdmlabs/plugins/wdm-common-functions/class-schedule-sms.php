<?php
namespace WDMCommonFunctions;

/**
* Class to register custom post type and custom taxonomy for case studies
*/

class ScheduleSms
{
    // To store current class object
    private static $instance;
    /**
     * __construct
     *
     * @return void
     */
    private function __construct()
    {
        add_action('wpcf7_before_send_mail', [$this, 'before_send_email'], 14, 3);
        // wp_schedule_single_event(time() + 0, 'mautic_cf7_form_submit', array($formid, $data, $ipaddress));
        add_action('lead_form_smses', array( $this, 'process_lead_form_smses' ), 10, 2);
        add_filter('acf/update_value/name=support_member_sequence', array( $this, 'update_support_member_sequence' ), 10, 4);
        add_action('acf/save_post', array( $this, 'wdm_acf_save_current_support_member' ), 999);
        // add_filter('wpcf7_special_mail_tags', array( $this, 'wpcf7_tag_sup_member_tags' ), 10, 3);
        add_action('init', array( $this, 'update_curr_supp_mem_seq_key' ));
        add_action('update_curr_supp_mem_seq_key', array( $this, 'update_curr_supp_mem_seq_key_run_cron' ));
    }
    
    /**
     * Remove an object filter.
     *
     * @param  string $tag                Hook name.
     * @param  string $class              Class name. Use 'Closure' for anonymous functions.
     * @param  string|void $method        Method name. Leave empty for anonymous functions.
     * @param  string|int|void $priority  Priority
     * @return void
     */
    public function remove_object_filter($tag, $class, $method = null, $priority = null)
    {
        $filters = $GLOBALS['wp_filter'][ $tag ];
        if (empty($filters)) {
            return;
        }
        foreach ($filters as $p => $filter) {
            if (! is_null($priority) && ((int) $priority !== (int) $p)) {
                continue;
            }
            $remove = false;
            foreach ($filter as $identifier => $function) {
                $function = $function['function'];
                if (
            is_array($function)
            && (
                is_a($function[0], $class)
                || (is_array($function) && $function[0] === $class)
            )
            ) {
                    $remove = ($method && ($method === $function[1]));
                } elseif ($function instanceof Closure && $class === 'Closure') {
                    $remove = true;
                }
                if ($remove) {
                    unset($GLOBALS['wp_filter'][$tag][$p][$identifier]);
                }
            }
        }
    }

    public function before_send_email($form, &$abort, $submission)
    {
        // $this->remove_object_filter('wpcf7_before_send_mail', 'kmcf7_sms_extension\CF7SmsExtension', 'before_send_email', 15);

        $options_name = 'kmcf7se-tab-settings-' . $form->id();
        $options = get_option($options_name);

        $props = $form->get_properties();

        $visitor_number = trim(wpcf7_mail_replace_tags($options['visitor_phone']));
        $visitor_message = trim(wpcf7_mail_replace_tags($options['visitor_message']));
        $your_message = trim(wpcf7_mail_replace_tags($options['your_message']));
        $your_number = trim(wpcf7_mail_replace_tags($options['your_phone']));

        //todo: enable debug mode

        if (strlen($visitor_number) > 0) {
            if ($form->id()=='446234') {
                $smses = get_field('add_sms', 'option');
                if (!empty($smses)) {
                    foreach ($smses as $key => $sms) {
                        $visitor_message = trim(wpcf7_mail_replace_tags($sms['sms_body']));
                        wp_schedule_single_event(time() + $sms['when'] * DAY_IN_SECONDS, 'lead_form_smses', array($visitor_number, "$visitor_message"));
                    }
                }
                // wp_schedule_single_event(time() + (3*DAY_IN_SECONDS), 'lead_form_third_sms', array($formid, $data, $ipaddress));
            }else{
                if (!\kmcf7_sms_extension\CF7SmsExtension::send_sms($visitor_number, "$visitor_message")) {
                    // $abort = true;
                } 
            }
        }
        if (strlen($your_number) > 0) {
            if (!\kmcf7_sms_extension\CF7SmsExtension::send_sms($your_number, "$your_message")) {
                // $abort = true;
            }
        }


        if ($props['mail']['recipient'] == '') {
            // $abort = true;
        }
    }

    public function update_support_member_sequence($value, $post_id, $field, $original)
    {
        if (!empty($value) && $value > 0) {
            $arr_keys = array_keys($original);
            for ($i=0;$i<$value;$i++) {
                $compare_array[] = 'row-'.$i;
            }
            if ($compare_array===$arr_keys) {
                // Order is same and not changed
                delete_transient('support_member_seq_changed');
            } else {
                // Order is changed
                set_transient('support_member_seq_changed', 1, 300);
            }
        }
        return $value;
    }

    public function wdm_acf_save_current_support_member($post_id)
    {
        if (get_transient('support_member_seq_changed')) {
            $support_member_sequence = get_field('support_member_sequence', 'option');
            if (!empty($support_member_sequence[0])) {
                update_option('curr_supp_mem_seq_key', 0);
            }
            delete_transient('support_member_seq_changed');
        }
    }

    public function wpcf7_tag_sup_member_tags($output, $name, $html)
    {
        $name = preg_replace('/^wpcf7\./', '_', $name); // for back-compat
        
        if(class_exists('WPCF7_Submission')){
            $submission = \WPCF7_Submission::get_instance();
        }
    
        if (! $submission) {
            return $output;
        }
    
        if ('calender_link' == $name) {
            $support_member_sequence = get_field('support_member_sequence', 'option');
            $curr_supp_mem_seq_key = get_option('curr_supp_mem_seq_key');
            if ($curr_supp_mem_seq_key!==false) {
                if (!empty($support_member_sequence[$curr_supp_mem_seq_key]['calender_link'])) {
                    return $support_member_sequence[$curr_supp_mem_seq_key]['calender_link'];
                } else {
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
        }
    
        return $output;
    }

    public function process_lead_form_smses($number, $visitor_message)
    {
        // $smses = get_field('add_sms', 'option');
        if (!empty($visitor_message) && !empty($number)) {
        //     foreach ($smses as $key => $sms) {
        //         if ($sms['when']==$when) {
        //             $visitor_message = trim(wpcf7_mail_replace_tags($sms['sms_body']));
                    if (!\kmcf7_sms_extension\CF7SmsExtension::send_sms($number, "$visitor_message")) {
                        // $abort = true;
                    }
        //         }
        //     }
        }
    }

    public function update_curr_supp_mem_seq_key()
    {
        if (! wp_next_scheduled('update_curr_supp_mem_seq_key')) {
            $currentDate = new \DateTime("now");
            $today_is = $currentDate->format('N');
            if( $today_is <= 5 && $today_is >= 2 ){
                wp_schedule_event((strtotime('00:00:00')-(5.5*60*60)), 'daily', 'update_curr_supp_mem_seq_key');
            }
        }
    }

    public function update_curr_supp_mem_seq_key_run_cron()
    {
        $support_member_sequence = get_field('support_member_sequence', 'option');
        $curr_supp_mem_seq_key = get_option('curr_supp_mem_seq_key');
        if ($curr_supp_mem_seq_key!==false) {
            if (!empty($support_member_sequence[$curr_supp_mem_seq_key]['member_name'])) {
                $new_key = (intval($curr_supp_mem_seq_key)+1)%(count($support_member_sequence));
                if (!empty($support_member_sequence[$new_key]['calender_link'])) {
                    update_option('curr_supp_mem_seq_key', $new_key);
                }
            }
        } else {
            if (!empty($support_member_sequence)) {
                update_option('curr_supp_mem_seq_key', 0);
            }
        }
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ScheduleSms;
        }
        return self::$instance;
    }
}
