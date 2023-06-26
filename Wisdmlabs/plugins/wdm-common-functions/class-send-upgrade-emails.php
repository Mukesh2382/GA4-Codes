<?php
namespace WDMCommonFunctions;

/**
 * SendUpgradeEmails to generate and handle an interface to send upgrade emails to clients
 */
class SendUpgradeEmails
{
    // To store current class object
    private static $instance;
    public $cron_action_hook;
    public $user_meta_key;
    public $post_type;
    
    /**
     * __construct
     *
     * @return void
     */
    private function __construct()
    {
        $this->cron_action_hook = 'wdm_upgrade_email_send';
        $this->post_type = 'upgrade-renew-emails';
        $this->user_meta_key = 'wdm_email_upgrade_sent';

        // Use hook to add menu in Dashboard -> Downloads menu
        // Process Cron Hook
        // Register post type for email, subject
        add_action('init', array( $this, 'register_upgrade_renew_emails_post_type'));
        add_action('wp_ajax_loadmore_upgrades', array($this,'generate_upgrade_options_dropdown'));
        add_action('admin_menu', array($this,'upgrade_emails_settings_page'));
        add_action('admin_enqueue_scripts', array($this,'send_upgrade_selectively_enqueue_admin_script'));
        add_action('wp_ajax_wdm_upgrade_send_test_email', array($this,'send_test_email'));
        add_action($this->cron_action_hook, array($this,'process_cron'), 10, 7);
    }
    
    /**
     * products_active_customers
     *
     * @param  int $download
     * @param  int $variation
     * @return array
     */
    public function products_active_customers($download, $variation = '', $period = '', $filter_users_for_email = '')
    {
        // Get code hint from send renewal emails file
        global $wpdb;
        $table_name1_where = $table_name3_where = $args = '';
        $table_name1 = $wpdb->prefix . 'edd_licenses';
        $table_name2 = $wpdb->prefix . 'edd_customers';
        $table_name3 = $wpdb->prefix . 'usermeta';
        if ($variation) {
            $table_name1_where = ' AND ' . $table_name1.'.price_id='.$variation;
        }
        if ($filter_users_for_email) {
            $table_name3_where = ' AND ' . $table_name1.'.user_id NOT IN ( SELECT user_id FROM '.$table_name3.' WHERE meta_key="'.$this->user_meta_key.'" AND meta_value='.$filter_users_for_email.')';
        }
        if (!empty($period)) {
            $args = array(
                    'expiration' => array(
                        'start' => strtotime($period . ' midnight', current_time('timestamp')),
                        'end'   => strtotime($period . ' midnight', current_time('timestamp')) + ( DAY_IN_SECONDS - 1 ),
                    )
                );
        }
        if (is_array($args['expiration'])) {
            if (! empty($args['expiration']['start'])) {
                if (is_numeric($args['expiration']['start'])) {
                    $start = $args['expiration']['start'];
                } else {
                    $start = strtotime($args['expiration']['start']);
                }
            }
            if (! empty($args['expiration']['end'])) {
                if (is_numeric($args['expiration']['end'])) {
                    $end = $args['expiration']['end'];
                } else {
                    $end = strtotime($args['expiration']['end']);
                }
            }
            if (isset($start) && isset($end)) {
                if ($start > $end) {
                    $table_name1_where .= " AND ". $table_name1 .".expiration BETWEEN {$end} AND {$start}";
                } else {
                    $table_name1_where .= " AND ". $table_name1 .".expiration BETWEEN {$start} AND {$end}";
                }
            } elseif (isset($start) && ! isset($end)) {
                $table_name1_where .= " AND ". $table_name1 .".expiration >= {$start}";
            } elseif (isset($end) && ! isset($start)) {
                $table_name1_where .= " AND ". $table_name1 .".expiration <= {$start}";
            }
        }
        
        $results = $wpdb->get_results($wpdb->prepare('SELECT '.$table_name1.'.customer_id,'.$table_name1.'.user_id,'.$table_name2.'.email,download_id,payment_id,license_key FROM '.$table_name1.' JOIN '.$table_name2. ' ON '.$table_name1.'.customer_id='.$table_name2.'.id WHERE '.$table_name1.'.status IN ("active","inactive") AND download_id='.$download.$table_name1_where.$table_name3_where));
        // echo 'SELECT '.$table_name1.'.customer_id,'.$table_name1.'.user_id,'.$table_name2.'.email,download_id,payment_id,license_key FROM '.$table_name1.' JOIN '.$table_name2. ' ON '.$table_name1.'.customer_id='.$table_name2.'.id WHERE '.$table_name1.'.status IN ("active","inactive") AND download_id='.$download.$table_name1_where.$table_name3_where;
        return array('rows'=>$results,'total'=>count($results));
    }
    
    /**
     * process_cron
     *
     * @param  int $download_id
     * @param  int $variation_id
     * @param  string $period
     * @param  int $upgrade_id
     * @param  int $email_body
     * @param  string $download_name
     * @param  string $email_subject
     * @return void
     */
    public function process_cron($download_id, $variation_id, $period, $upgrade_id, $email_body, $download_name, $email_subject)
    {
        $data = $this->products_active_customers($download_id, $variation_id, $period, $email_body);
        if ($data['rows']) {
            $sub = get_post_meta($email_body, 'subject', true);
            $msg = get_post_meta($email_body, 'body', true);
            // $cntr = 0;
            foreach ($data['rows'] as $row) {
                // if ($cntr==1) {
                //     error_log('Tariq Kotwal Break Foreach at ' . $cntr);
                //     break;
                // }
                $mail_to = $row->email;
                if ($row->user_id && !($this->is_user_unsubscribed($mail_to))) {
                    $license_id = $this->get_active_license($download_id, $variation_id, $row->customer_id);
                    if ($sub && $msg && $license_id) {
                        $msg_to_send = $msg;
                        $msg_to_send = $this->filter_email_body($msg_to_send, $license_id, $upgrade_id);
                        // error_log('Mail to '."\n".$mail_to.' Subject '."\n".$sub.' Message '."\n".$msg_to_send);
                        // $mail_to = 'tariq.kotwal@wisdmlabs.com';
                        // $mail_to = 'samiksha.ghuge@wisdmlabs.com';
                        // $mail_to=array("tariq.kotwal@wisdmlabs.com","samiksha.ghuge@wisdmlabs.com");
                        // shuffle($mail_to);
                        // $this->send_email($mail_to, $sub, $msg_to_send);
                        if ($this->send_email($mail_to, $sub, $msg_to_send)) {
                            update_user_meta($row->user_id, $this->user_meta_key, $email_body);
                        }
                    }
                }
                // $cntr++;
            }
        }
    }
        
    /**
     * get_active_license
     *
     * @param  int $download
     * @param  int $variation_id
     * @param  int $customer_id
     * @return string
     */
    public function get_active_license($download, $variation_id, $customer_id)
    {
        $args = array(
                    'number'      => -1,
                    'customer_id' => $customer_id,
                    'orderby'     => 'id',
                    'order'       => 'ASC',
                );
        if ($variation_id) {
            $args['price_id'] = $variation_id;
        }
        $licenses = edd_software_licensing()->licenses_db->get_licenses($args);
        if (! empty($licenses)) {
            foreach ($licenses as $license) {
                if ($license->download_id==$download && ($license->status=='active' || $license->status=='inactive' )) {
                    return $license->ID;
                }
            }
        }
        return '';
    }
    /**
     * send_email
     *
     * @param  array $mail_to
     * @param  string $subject
     * @param  string $message
     * @return bool
     */
    public function send_email($mail_to, $subject, $message)
    {
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
            error_log('Upgrade offer email could not send to ' . $mail_to);
        }
        return $sent;
    }
    
    /**
     * upgrade_emails_settings_page
     *
     * @return void
     */
    public function upgrade_emails_settings_page()
    {
        add_submenu_page(
            'edit.php?post_type=upgrade-renew-emails',
            'Set Emails Processes',
            'Upgrade Emails Settings',
            'manage_options',
            'upgrade-renew-emails-settings',
            array($this,'generate_interface')
        );
    }

    /**
     * generate_interface
     *
     * @return void
     */
    public function generate_interface()
    {
        $customers_table = $this->process_submitted_actions();
        ?>
        <div class="wrap">
            <h1>Upgrade Emails Sending</h1>
            <form method="post" action="">
                <div id="message-container"></div>
                <table class="form-table">
                    <?php
                        echo $this->generate_select_email_dropdown();
                        echo $this->generate_products_dropdown();
                        echo $this->generate_customer_details($customers_table['total'], $customers_table['filtered']);
                        echo $this->generate_test_email_section();
                    ?>
                </table>
                <?php submit_button('Send Emails');?>
            </form>
        </div>
        <?php
        echo $customers_table['html'];
    }
     
    /**
     * generate_select_email_dropdown
     *
     * @return string
     */
    public function generate_select_email_dropdown()
    {
        $selected = '';
        $args = array(
            'numberposts' => -1,
            'post_type'   => $this->post_type
        );
        $posts = get_posts($args);
        $text ='<tr valign="top"><th scope="row">Select Email</th>';
        $text .= '<td><select id="select_email" name="select_email">';
        if (!empty($_POST['select_email'])) {
            $selected = $_POST['select_email'];
        }
        $text .= '<option value="">Select email...</option>';
        foreach ($posts as $key => $value) {
            $selected_str = ($selected==$value->ID)?'selected="selected"':'';
            $active = get_post_meta($value->ID, 'active', true);
            $text .= '<option '.$selected_str.' value="'.$value->ID.'">' . $value->post_title . ($active?' - '.$active:'') .'</option>';
        }
        $text .= '</select></td></tr>';
        return $text;
    }
    
    /**
     * generate_products_dropdown
     *
     * @return string
     */
    public function generate_products_dropdown()
    {
        $ajax_path = site_url() . '/wp-admin/admin-ajax.php';
        $args = array(
            'post_type'     => 'download',
            'numberposts' => -1
        );
        $posts = get_posts($args);
        
        // Downloads dropdown.
        $text = '<tr valign="top"><th scope="row">Select Download</th><td><select id="select_download" name="select_download">';
        $text .= '<option value="" '.(empty($_POST['select_download'])?'selected="true"':'').' disabled>Select a download...</option>';
        foreach ($posts as $key => $value) {
            $text .= '<option value="'.$value->ID.'" '.((!empty($_POST['select_download']) && $_POST['select_download']==$value->ID)?'selected="selected"':'').'>'.apply_filters( 'the_title', $value->post_title, $value->ID ).'</option>';
        }
        $text .= '</select></td>';

        // Periods dropdown.
        $notices = new \EDD_Recurring_Reminders();
        $text .= '<tr valign="top"><th scope="row">Select Period</th><td><select id="select_period" name="select_period">';
        $text .= '<option value="" '.(empty($_POST['select_period'])?'selected="true"':'').'>All Periods</option>';
        foreach ($notices->get_notice_periods('renewal') as $period => $label) {
            if ($period=='today' || strpos($label, 'after')!==false) {
                continue;
            }
            $text .= "<option value=".esc_attr($period)." ".(isset($_POST['select_period'])?selected($period, $_POST['select_period'], false):'').">".esc_html($label)."</option>";
        }
        $text .= '</select></td>';
        
        // Product variations options.
        $text .= '<tr valign="top"><th scope="row">Select Variable Option </th><td><select id="select_variation" name="select_variation">';
        if (isset($_POST['select_variation']) && !empty($_POST['select_download'])) {
            $text .= $this->generate_upgrade_options_dropdown($_POST['select_download'], $_POST['select_variation'], '', 'select_variation');
        }
        $text .= '</select></td>';

        // Upgrade options.
        $text .= '<tr valign="top"><th scope="row">Select Upgrade Option </th><td><select id="select_upgrade_option" name="select_upgrade_option">';
        if (!empty($_POST['select_download'])) {
            $text .= $this->generate_upgrade_options_dropdown($_POST['select_download'], '', (!empty($_POST['select_upgrade_option'])?$_POST['select_upgrade_option']:''), 'select_upgrade_option');
        }
        $text .= '</select></td>';
        $text .= '</tr>';

        return $text;
    }
    
    /**
     * generate_upgrade_options_dropdown
     *
     * @param  int $download
     * @param  int $variation_option
     * @param  int $upgrade_option
     * @param  string $drop_down_name
     * @return string
     */
    function generate_upgrade_options_dropdown($download = '', $variation_option = '', $upgrade_option = '', $drop_down_name = '')
    {
        $text['upgrades'] = '';
        $text['options'] = '';
        if (!empty($_POST['download']) || !empty($download)) {
            if (!empty($_POST['download'])) {
                $download = $_POST['download'];
            }
            if (isset($_POST['select_variation'])) {
                $variation_option = $_POST['select_variation'];
            }
            if (!empty($_POST['select_upgrade_option'])) {
                $upgrade_option = $_POST['select_upgrade_option'];
            }
            $upgrades = edd_sl_get_upgrade_paths($download);
            $prices = edd_get_variable_prices($download);
            
            $text['upgrades'] = $text['options'] = '<option value="">Select an option</option>';
            foreach ($upgrades as $upgrade_id => $upgrade) {
                $upgrade_prices = edd_get_variable_prices($upgrade['download_id']);
                $upgrade_variation_name = '';
                if ($upgrade_prices[$upgrade['price_id']]) {
                    $upgrade_variation_name = ' - ' . $upgrade_prices[$upgrade['price_id']]['name'];
                }
                $selected_str = (!empty($upgrade_option)&&($upgrade_id==$upgrade_option))?'selected="selected"':'';
                $text['upgrades'] .= '<option '.$selected_str.' value="'. $upgrade_id .'">'. get_the_title($upgrade['download_id']) . $upgrade_variation_name . '</option>';
            }
            if (!empty($prices)) {
                foreach ($prices as $price_id => $variation) {
                    $selected_str = (!empty($variation_option)&&($price_id==$variation_option))?'selected="selected"':'';
                    $text['options'] .= '<option '.$selected_str.' value="'. $price_id .'">'. $variation['name'].'</option>';
                }
            }
            if (wp_doing_ajax()) {
                echo json_encode($text);
                die;
            }
        }
        if ($drop_down_name=='select_upgrade_option') {
            return $text['upgrades'];
        } elseif ($drop_down_name=='select_variation') {
            return $text['options'];
        }
        return $text;
        // here we exit the script
    }

    /**
     * generate_customer_details
     *
     * @param  int $total
     * @param  int $filtered
     * @return string
     */
    public function generate_customer_details($total, $filtered)
    {
        $text ='<tr valign="top"><th scope="row">Fetch Active Customers</th>';
        $text .= '<td> <button class="button button-primary" name="fetch_customers" id="fetch_customers">Fetch</button> ';
        // <button class="button button-primary" name="filter_unsub_customers" id="filter_unsub_customers">Filter Unsubscribed</button>
        $text .= '</td></tr>';
        $text .='<tr valign="top"><th scope="row">Customers</th>';
        $text .= '<td> <span class="" id="customers">Total '.$total.'</span>, <span class="" id="unsub_customers">Filtered '.$filtered.'</span>';
        $text .= '</td></tr>';
        return $text;
    }

    /**
     * generate_test_email_section
     *
     * @return string
     */
    public function generate_test_email_section()
    {
        $text = '<tr valign="top"><th scope="row">Test Email </th>';
        $text .= '<td><input name="test_email" type="text" id="test_email"> <button class="button button-primary" id="send_test_email">Send Test Email</button></td></tr>';
        return $text;
    }
    
    /**
     * send_test_email
     *
     * @return void
     */
    public function send_test_email()
    {
        if (!empty($_POST['email']) && !empty($_POST['select_email'])) {
            $sub = get_post_meta($_POST['select_email'], 'subject', true);
            $msg = get_post_meta($_POST['select_email'], 'body', true);
            if ($this->send_email($_POST['email'], $sub, $msg)) {
                echo 1;
                die;
            }
        }
        echo 0;
        die;
    }
    
    /**
     * filter_email_body
     *
     * @param  string $text
     * @param  int $license_id
     * @param  int $upgrade_id
     * @return string
     */
    public function filter_email_body($text, $license_id, $upgrade_id)
    {
        // {name} Customer Name, {license_key} License Key, {product_name} Product Name, {expiration} License Expiration, {expiration_time} License Expiration Time, {upgrade_link} Upgrade Link, {upgrade_link_{upgrade id}} A specific upgrade id example: {upgrade_link_1}
        $license = edd_software_licensing()->get_license($license_id);
        // Retrieve the customer name
        if ($license->user_id) {
            $user_data     = get_userdata($license->user_id);
            $customer_name = $user_data->display_name;
        } else {
            $user_info  = edd_get_payment_meta_user_info($license->payment_id);
            if (isset($user_info[ 'first_name' ])) {
                $customer_name = $user_info[ 'first_name' ];
            } else {
                $customer_name = $user_info[ 'email' ];
            }
        }

        $expiration      = date_i18n(get_option('date_format'), $license->expiration);
        $discount        = edd_sl_get_renewal_discount_percentage($license_id);

        // $renewal_link is actually just a URL. Not renamed for historical reasons.
        $current_time    = current_time('timestamp');
        $time_diff       = human_time_diff($license->expiration, $current_time);

        if ($license->expiration < $current_time) {
            $time_diff = sprintf(__('expired %s ago', 'edd_sl'), $time_diff);
        } else {
            $time_diff = sprintf(__('expires in %s', 'edd_sl'), $time_diff);
        }

        $text = str_replace('{name}', $customer_name, $text);
        $text = str_replace('{license_key}', $license->key, $text);
        $text = str_replace('{product_name}', $license->get_download()->get_name(), $text);
        $text = str_replace('{expiration}', $expiration, $text);
        $text = str_replace('{expiration_time}', $time_diff, $text);
        $text = str_replace('{upgrade_link}', edd_sl_get_license_upgrade_url($license_id, $upgrade_id), $text);
        if (strpos($text, '{upgrade_link_utm}')!==false && strpos($text, '{/upgrade_link_utm}')!==false) {
            $utm_string = $this->find_between($text, '{upgrade_link_utm}', '{/upgrade_link_utm}');
            $text = str_replace($utm_string, html_entity_decode($utm_string), $text);
            $text = str_replace('{upgrade_link_utm}', '', $text);
            $text = str_replace('{/upgrade_link_utm}', '', $text);
        }
        return $text;
    }
    
    /**
     * find_between
     *
     * @param  string $string
     * @param  string $start
     * @param  string $end
     * @param  bool $greedy
     * @return string
     */
    public function find_between(string $string, string $start, string $end, bool $greedy = false)
    {
        $start = preg_quote($start, '/');
        $end   = preg_quote($end, '/');
     
        $format = '/(%s)(.*';
        if (!$greedy) {
            $format .= '?';
        }
        $format .= ')(%s)/';
     
        $pattern = sprintf($format, $start, $end);
        preg_match($pattern, $string, $matches);
     
        return $matches[2];
    }
    
    /**
     * cron_setting
     *
     * @param  int $download_id
     * @param  int $variation_id
     * @param  string $period
     * @param  int $upgrade_id
     * @param  int $email_body
     * @param  string $download_name
     * @param  string $email_subject
     * @return void
     */
    public function cron_setting($download_id, $variation_id, $period, $upgrade_id, $email_body, $download_name = '', $email_subject = '')
    {
        if (!empty($download_id) && !empty($upgrade_id) && !empty($email_body)) {
            wp_schedule_single_event(time() + (60*20), $this->cron_action_hook, array($download_id, $variation_id, $period, $upgrade_id,$email_body,$download_name,$email_subject));
        }
    }
    
    /**
     * delete_cron
     *
     * @param  string $license_id
     * @param  int $upgrade_id
     * @return null
     */
    public function delete_cron($license_id, $upgrade_id)
    {
        $crons  = _get_cron_array();
        if (empty($crons)) {
            return;
        }
        //
        foreach ($crons as $time => $cron) {
            foreach ($cron as $hook => $dings) {
                foreach ($dings as $sig => $data) {
                    if ($hook == $this->cron_action_hook) {
                        if (!empty($data['args'][0]) && $data['args'][0]==$license_id && !empty($data['args'][0]) && $data['args'][1]==$upgrade_id) {
                            wp_clear_scheduled_hook($hook, $data['args']);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * process_submitted_actions
     *
     * @return array
     */
    public function process_submitted_actions()
    {
        $table = '';
        $data['total'] = 0;
        if ((isset($_POST['fetch_customers']) || isset($_POST['filter_unsub_customers']) || isset($_POST['submit'])) && !empty($_POST['select_download'])) {
            $data = $this->products_active_customers($_POST['select_download'], (!empty($_POST['select_variation'])?$_POST['select_variation']:''), (!empty($_POST['select_period'])?$_POST['select_period']:''), (!empty($_POST['select_email'])?$_POST['select_email']:''));
        }
        $data['filtered'] = 0;
        if (isset($_POST['fetch_customers'])) {
            if ($data['rows']) {
                $table = '';
                $table .= '<div><table id="download_cutomers">';
                $table .= '<thead><tr><th>User ID</th><th>Customer ID</th><th>Email</th><th>Payment ID</th><th>License Key</th></tr></thead><tbody>';
                foreach ($data['rows'] as $row) {
                    $table .= '<tr><td>'.$row->user_id.'</td>';
                    $table .= '<td>'.$row->customer_id.'</td>';
                    $table .= '<td>'.$row->email.'</td>';
                    $table .= '<td>'.$row->payment_id.'</td>';
                    $table .= '<td>'.$row->license_key.'</td></tr>';
                }
                $table .= '</tbody></table></div>';
            }
        }
        if (isset($_POST['filter_unsub_customers'])) {
            if ($data['rows']) {
                $table = '';
                $table .= '<div><table id="download_cutomers">';
                $table .= '<thead><tr><th>Customer ID</th><th>Email</th><th>Payment ID</th><th>License Key</th></tr></thead><tbody>';
                $filtered = 0;
                foreach ($data['rows'] as $row) {
                    if ($this->is_user_unsubscribed($row->email)) {
                        continue;
                    }
                    $filtered++;
                    $table .= '<tr><td>'.$row->customer_id.'</td>';
                    $table .= '<td>'.$row->email.'</td>';
                    $table .= '<td>'.$row->payment_id.'</td>';
                    $table .= '<td>'.$row->license_key.'</td></tr>';
                }
                $table .= '</tbody></table></div>';
                $data['filtered'] = $filtered;
            }
        }
        if (isset($_POST['submit']) && !empty($_POST['select_email']) && !empty($_POST['select_download']) && !empty($_POST['select_upgrade_option'])) {
            if ($data['rows']) {
                $this->cron_setting($_POST['select_download'], (!empty($_POST['select_variation'])?$_POST['select_variation']:''), (!empty($_POST['select_period'])?$_POST['select_period']:''), $_POST['select_upgrade_option'], $_POST['select_email'], get_the_title($_POST['select_download']), get_post_meta($_POST['select_email'], 'subject', true));
                $table = '<div class="updated notice"><p>Cron set successfully! To delete the cron <a href="'.admin_url('tools.php?page=crontrol_admin_manage_page').'">open</a> the page and find email subject OR download name OR "wdm_upgrade_email_send"</p></div>';
            } else {
                $table = '<div class="updated error"><p>No customers found!</p></div>';
            }
        }
        return array('html'=>$table,'total'=>$data['total'],'filtered'=>$data['filtered']);
    }
    
    /**
     * register_upgrade_renew_emails_post_type
     *
     * @return void
     */
    public function register_upgrade_renew_emails_post_type()
    {
        $labels = array(
            'name'                  => __('Upgrade & Renew Email'),
            'singular_name'         => __('Upgrade & Renew Email'),
            'menu_name'             => __('Upgrade & Renew Email'),
            'name_admin_bar'        => __('Upgrade & Renew Email'),
            'add_new'               => __('Add New Upgrade & Renew Email'),
            'add_new_item'          => __('Add New Upgrade & Renew Email'),
            'new_item'              => __('New Upgrade & Renew Email'),
            'edit_item'             => __('Edit Upgrade & Renew Email'),
            'view_item'             => __('View Upgrade & Renew Email'),
            'all_items'             => __('All Upgrade & Renew Emails'),
            'search_items'          => __('Search Upgrade & Renew Emails'),
            'not_found'             => __('No Upgrade & Renew Emails found.'),
            'not_found_in_trash'    => __('No Upgrade & Renew Emails found in Trash.'),
            'insert_into_item'      => __('Insert into Upgrade & Renew Email'),
            'uploaded_to_this_item' => __('Uploaded to this Upgrade & Renew Email'),
            'filter_items_list'     => __('Filter Upgrade & Renew Emails list'),
            'items_list_navigation' => __('Upgrade & Renew Emails list navigation'),
            'items_list'            => __('Upgrade & Renew Emails list'),
        );
     
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => $this->post_type ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => array( 'title', 'author' ),
        );
        register_post_type($this->post_type, $args);
    }
    
    /**
     * send_upgrade_selectively_enqueue_admin_script
     *
     * @param  string $hook
     * @return void
     */
    public function send_upgrade_selectively_enqueue_admin_script($hook)
    {
        if ('upgrade-renew-emails_page_upgrade-renew-emails-settings' == $hook) {
            wp_enqueue_script('wdm-send-upgrade', plugin_dir_url(__FILE__) . 'assets/js/wdm-send-upgrade.js', array('jquery'));
            wp_enqueue_style('wdm-datatable', plugin_dir_url(__FILE__) . 'assets/css/jquery.dataTables.min.css');
            wp_enqueue_script('wdm-datatable', plugin_dir_url(__FILE__) . 'assets/js/jquery.dataTables.min.js', array('jquery'));
            wp_localize_script(
                'wdm-send-upgrade',
                'frontend_ajax_object',
                array(
                    'ajaxurl' => admin_url('admin-ajax.php')
                )
            );
        }
    }
    
    /**
     * is_user_unsubscribed
     *
     * @param  string $email
     * @return int
     */
    public function is_user_unsubscribed($email)
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

    /**
     * getInstance
     *
     * @return object
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new SendUpgradeEmails;
        }
        return self::$instance;
    }
}
