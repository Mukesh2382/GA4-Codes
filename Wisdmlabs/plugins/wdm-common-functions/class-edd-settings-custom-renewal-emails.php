<?php
namespace WDMCommonFunctions;

/**
* Class to handle EDD renewal emails settings
*/

class EddSettingsCustomRenewalEmails
{
    // To store current class object
    private static $instance;
    public $subscription;
    
    /** * @SuppressWarnings(PHPMD) */
    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', array($this,'wdmEddAddMenuPages'), 10);
            if (isset($_GET['page']) && $_GET['page']=='renewal-emails-settings') {
                add_action('admin_enqueue_scripts', array($this,'wdmEnqueueCustomAdminJS'));
            }
            add_action("wp_ajax_wdm_admin_renewal_email_data", array($this,"wdmGetDownloadRenewalEmailDataAjax"));
            add_action("wp_ajax_wdm_admin_renewal_email_test_send", array($this,"wdmGetDownloadRenewalEmailTestSend"));
            add_action("wp_ajax_wdm_admin_renewal_email_copy_data", array($this,"wdmCopyEmailData"));
        }
        if (defined('WDM_SEND_PER_PRODUCT_EMAILS')) {
            add_action('edd_recurring_daily_scheduled_events', array( $this, 'wdmScheduledReminders' ), 11);
            // To disable other default notices
            add_filter('edd_recurring_send_reminder', array( $this, 'wdmEddRecurringSendReminder'), 100, 3);
            // remove_action('edd_subscription_post_renew', array( EDD_Recurring()->$emails, 'send_payment_received' ), 10);
            // add_action('edd_subscription_post_renew', array( $this, 'wdmSendPaymentReceived' ), 10, 4);
            // error_log('Tariq');
            // remove_action('edd_daily_scheduled_events', 'edd_sl_scheduled_reminders', 10);
            add_action('edd_daily_scheduled_events', array($this,'wdmEddSlScheduledReminders'), 9);
        }

        add_action('init', array($this,'ontimescript'), 991);
    }

    function ontimescript(){
        if(isset($_GET['copy_email_temp']) && $_GET['copy_email_temp'] == 1){
            echo "<pre>";
            $scriptname = "upgrades_renewal_onetime_script_120821";
            $option = get_option($scriptname);
            if($option === false){
                add_option( $scriptname, 'started', '', 'yes' ); 
                echo('script started');
                global $wpdb;
                $results = $wpdb->get_results("SELECT option_name, option_value FROM `wp_options` WHERE `option_name` LIKE '%wdm_edd_manual_renewal_email_settings_%' OR `option_name` LIKE '%wdm_edd_renewal_email_settings_%'");
                $notices = new \EDD_Recurring_Reminders();
                $notices = $notices->get_notice_periods('renewal');
                $notices['after'] = 'Immediately after renewal';
                foreach($results as $option){
                    $option_name = $option->option_name;
                    $option_value = unserialize($option->option_value);
                    if($option_value && isset($option_value['subject'])){
                        $subject  = $option_value['subject']; 
                        $body  = $option_value['body']; 
                        $enabled  = $option_value['enabled']; 

                        $exploded_option_name = explode('_', $option_name);
                        $exploded_option_name_count = count($exploded_option_name);
                        if (!empty($exploded_option_name[$exploded_option_name_count-2])) {
                            $manual = (strpos($option_name, '_manual_')!==false);

                            if ($manual) {
                                $post_name = $exploded_option_name[$exploded_option_name_count-2].'_'.$exploded_option_name[$exploded_option_name_count-1].'_manual';
                                $post_title = get_the_title($exploded_option_name[$exploded_option_name_count-2]).' ** Manual ** '. $notices[$exploded_option_name[$exploded_option_name_count-1]];
                            } else {
                                $post_name = $exploded_option_name[$exploded_option_name_count-2].'_'.$exploded_option_name[$exploded_option_name_count-1].'_auto';
                                $post_title = get_the_title($exploded_option_name[$exploded_option_name_count-2]).' ** Auto ** '. $notices[$exploded_option_name[$exploded_option_name_count-1]] ;
                            }
                        }
                        
                       
                        $postdata = array(
                            'post_title'    => $post_title,
                            'post_status'   => 'publish',
                            'post_author'   => 1,
                            // 'post_name'   => $post_name,
                            'post_type'   => 'upgrade-renew-emails',
                            'meta_input'    => [
                                'active'    => 1,
                                'body'      => $body,
                                'subject'   => $subject
                            ]
                        );
                        // Insert the post into the database
                        $renewal_email_template_id = wp_insert_post( $postdata );
                        if($renewal_email_template_id){
                            update_option( $option_name, [
                                'enabled'    => $enabled,
                                'renewal_email_template_id'   => $renewal_email_template_id,
                            ]); 
                        }
                        echo("option_name : " . $option_name);
                        echo("renewal_email_template_id : " . $renewal_email_template_id);
                        echo("Updated successfully" );

                    }
                }
                update_option( $scriptname, 'completed'); 
                print_r('script executed');
            }
            else{
                print_r('script already executed');
            }
            exit;
        }
    }

    public function get_update_renew_email($id){
        $post = get_post($id);
        if($post){
            if($post->post_type == 'upgrade-renew-emails'){
                $post_meta = get_post_meta($id);
                if($post_meta){
                    $result = new \stdClass;
                    $result->title = $post->post_title;
                    $result->status = $post->post_status;
                    $result->subject = $this->filter_postmeta($post_meta,'subject');
                    $result->body = $this->filter_postmeta($post_meta,'body');
                    $result->active = $this->filter_postmeta($post_meta,'active');
                    return $result;
                }
            }
        }
        return false;
    }

    public function filter_postmeta($postmeta,$key){
        if(isset($postmeta[$key])){
            if(is_array($postmeta[$key])){
                return implode("",$postmeta[$key]);
            }
            return $postmeta[$key]; 
        }
        return false;
    }

    public function wdmEddAddMenuPages()
    {
        add_submenu_page('edit.php?post_type=download', __('Easy Digital Downloads Settings', 'easy-digital-downloads'), __('WISDM Renewal Emails', 'easy-digital-downloads'), 'manage_shop_settings', 'renewal-emails-settings', array($this,'eddWdmRenewalOptionsPage'));
    }

    public function eddWdmRenewalOptionsPage()
    {
        $notices = new \EDD_Recurring_Reminders();
        ?>
        <div class="wrap">
            <style>.js .tmce-active .wp-editor-area{color: #000 !important;}</style>
            <?php echo $this->saveReminder();?>
            <h1><?php _e('Add Renewal Emails', 'edd-recurring'); ?></h1>
            <form id="edd-add-reminder-notice" action="" method="post">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row" valign="top">
                                <label for="edd-notice-downloads"><?php _e('Active Renewal Emails', 'edd-recurring'); ?></label>
                            </th>
                            <td>
                                <?php echo $this->getExistingDownloadsEmails('active-wdm-downloads', 1);?>

                                <p class="description"><?php _e('The active renewal email campaigns', 'edd-recurring'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row" colspan="2">
                                <hr>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" valign="top">
                                <label for="edd-notice-downloads"><?php _e('Downloads', 'edd-recurring'); ?></label>
                            </th>
                            <td>
                                <?php echo $this->getDownloads();?>

                                <p class="description"><?php _e('The download for which renewal email is being set', 'edd-recurring'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" valign="top">
                                <label for="edd-notice-status"><?php _e('Status', 'edd-recurring'); ?></label>
                            </th>
                            <td>
                                <input name="status" id="edd-notice-status" class="edd-notice-status" type="checkbox" <?php echo (!isset($_POST['delete_reminder']) && !empty($_POST['status']))?'checked':''?>/>

                                <p class="description"><?php _e('The current status of the notice email', 'edd-recurring'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" valign="top">
                                <label for="auto-manual"><?php _e('Auto / Manual Renewal', 'edd-recurring'); ?></label>
                            </th>
                            <td>
                                <?php $this->wdmShowAutoManualOptions();?>
                                <p class="description"><?php _e('Whether the renewal is auto or manual'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" valign="top">
                                <label for="wdm-renewal-email-template"><?php _e('Select Email Template', 'edd-recurring'); ?></label>
                            </th>
                            <td>
                                <?php echo $this->getUpgradeRenewEmails('wdm-renewal-email-template');?>
                                <p class="description"><?php _e('Select email template', 'edd-recurring'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" valign="top">
                                <label for="edd-notice-period"><?php _e('Email Period', 'edd-recurring'); ?></label>
                            </th>
                            <td>
                                <select name="period" id="edd-notice-period">
                                    <?php foreach ($notices->get_notice_periods('renewal') as $period => $label) : ?>
                                        <option value="<?php echo esc_attr($period); ?>" <?php !isset($_POST['delete_reminder'])?selected($period, $_POST['period']):''?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                        <option value="after" <?php !isset($_POST['delete_reminder'])?selected('after', $_POST['period']):'' ?>><?php echo 'Immediately after renewal'; ?></option>
                                </select>

                                <p class="description"><?php _e('When should this email be sent?', 'edd-recurring'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row" valign="top">
                                <label for="edd-notice-message"><?php _e('Test Email', 'edd-recurring'); ?></label>
                            </th>
                            <td>
                                <input type="email" name="wdm-test-email"/>
                                <button id="wdm-test-purchase-email"> Send </button>
                            </td>    
                        </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input type="hidden" name="edd-recurring-renewal-reminder-notice-nonce" value="<?php echo wp_create_nonce('wdm_edd_recurring_renewal_reminder'); ?>" />
                    <input name="save_reminder" type="submit" value="<?php _e('Add / Update Notice', 'edd-recurring'); ?>" class="button-primary" />
                    <input name="delete_reminder" type="submit" value="<?php _e('Delete Notice', 'edd-recurring'); ?>" class="button-primary" />
                </p>
            </form>
            <!-- <form method="post"> -->
                <?php
                // echo wp_nonce_field( 'wdm-edd-renewal-email-action', '_wpnonce_wdm_edd_renewal' );
                // echo '<p><label> Downloads: ' . $this->getDownloads() . '</label></p>';
                // echo '<p><label> Periods: ' . $this->getPeriods() . '</label></p>';
                // echo '<p><label> Subject: ' . $this->getSubject() . '</label></p>';
                // echo '<p><label> Email: ' . $this->getBody() . '</label></p>';
                ?>
            <!-- </form> -->
        </div>
        <?php
    }

    public function wdmShowAutoManualOptions($name = 'auto-manual', $only_option = 0)
    {
        ?>
        <select id="auto-manual" name="<?php echo $name;?>">
            <?php
            if ($only_option) {
                ?>
                <option value='auto'>Auto</option>
                <option value='manual'>Manual</option>
                <?php
            } else {
                ?>
                <option value='auto' <?php echo (isset($_POST['auto-manual'])&&$_POST['auto-manual']=='auto')?'selected':'';?>>Auto</option>
                <option value='manual' <?php echo (isset($_POST['auto-manual'])&&$_POST['auto-manual']=='manual')?'selected':'';?>>Manual</option>
                <?php
            }
            ?>
        </select>
        <?php
    }

    public function showInstructions()
    {
        ?>
            <p class="description"><?php _e('The email message to be sent with the notice. The following template tags can be used in the message:', 'edd-recurring'); ?></p>
            <ul>
                <li>{name} <?php _e('The customer\'s name', 'edd-recurring'); ?></li>
                <li>{subscription_name} <?php _e('The name of the product the subscription belongs to', 'edd-recurring'); ?></li>
                <li>{expiration} <?php _e('The expiration or renewal date for the subscription', 'edd-recurring'); ?></li>
                <li>{amount} <?php _e('The recurring amount of the subscription', 'edd-recurring'); ?></li>
            </ul>
            <p class="description"><?php _e('The email message to be sent with the notice. The following template tags can be used in the message for users with manual renewal:', 'edd-recurring'); ?></p>
            <ul>
                <li>{name} <?php _e('The customer\'s name', 'edd-recurring'); ?></li>
                <li>{product_name} <?php _e('The name of the product the license belongs to', 'edd-recurring'); ?></li>
                <li>{expiration} <?php _e('The expiration or renewal date of the license', 'edd-recurring'); ?></li>
                <li>{license_key} <?php _e('License Key', 'edd-recurring'); ?></li>
                <li>{renewal_link} <?php _e('Manual Renewal Link', 'edd-recurring'); ?></li>
                <li>{renewal_url} <?php _e('Manual Renewal Url', 'edd-recurring'); ?></li>
            </ul>
        <?php
    }

    public function wdmGetDownloadRenewalEmailData($download = '')
    {
        if (wp_doing_ajax()) {
            if (isset($_POST['download'])) {
                $wdm_edd_renewal_email_settings = get_option('wdm_edd_renewal_email_settings_'.$_POST['download'], 0);
                if(isset($wdm_edd_renewal_email_settings['renewal_email_template_id'])){
                    $template_id = $wdm_edd_renewal_email_settings['renewal_email_template_id'];
                    $email_template = $this->get_update_renew_email($template_id);
                    $subject = $email_template->subject;
                    $body = $email_template->body;
                }
                else{
                    $subject = $wdm_edd_renewal_email_settings['subject'];
                    $body = $wdm_edd_renewal_email_settings['body'];
                }
                $data = array(
                            'subject' => stripslashes($subject),
                            'body' => stripslashes($body),
                            'enabled' => !empty($wdm_edd_renewal_email_settings['enabled'])?$wdm_edd_renewal_email_settings['enabled']:0
                            );
                echo empty($wdm_edd_renewal_email_settings)?0:json_encode($data);
                die();
            }
            echo '0';
            die();
        } else {
            if ($download) {
                $wdm_edd_renewal_email_settings = get_option('wdm_edd_renewal_email_settings_'.$download, 0);
                if(isset($wdm_edd_renewal_email_settings['renewal_email_template_id'])){
                    $template_id = $wdm_edd_renewal_email_settings['renewal_email_template_id'];
                    $email_template = $this->get_update_renew_email($template_id);
                    $wdm_edd_renewal_email_settings['subject'] = ($email_template->subject);
                    $wdm_edd_renewal_email_settings['body'] = ($email_template->body);
                }
                return $wdm_edd_renewal_email_settings;
            }
        }
    }

    public function wdmGetDownloadRenewalEmailDataAjax()
    {
        if (wp_doing_ajax()) {
            if (isset($_POST['download']) && isset($_POST['automanual'])) {
                if ($_POST['automanual']=='auto') {
                    $wdm_edd_renewal_email_settings = get_option('wdm_edd_renewal_email_settings_'.$_POST['download'], 0);
                } elseif ($_POST['automanual']=='manual') {
                    $wdm_edd_renewal_email_settings = get_option('wdm_edd_manual_renewal_email_settings_'.$_POST['download'], 0);
                }
                if(isset($wdm_edd_renewal_email_settings['renewal_email_template_id'])){
                    $template_id = $wdm_edd_renewal_email_settings['renewal_email_template_id'];
                    $email_template = $this->get_update_renew_email($template_id);
                    $subject = ($email_template->subject);
                    $body = ($email_template->body);
                }
                else{
                    $subject = $wdm_edd_renewal_email_settings['subject'];
                    $body = $wdm_edd_renewal_email_settings['body'];
                }
                $data = array(
                            'subject' => stripslashes($subject),
                            'body' => stripslashes($body),
                            'enabled' => !empty($wdm_edd_renewal_email_settings['enabled'])?$wdm_edd_renewal_email_settings['enabled']:0,
                            'auto' => ($_POST['automanual']=='auto')?1:0
                            );
                echo empty($wdm_edd_renewal_email_settings)?0:json_encode($data);
                die();
            }
            echo '0';
            die();
        }
    }

    public function getDownloads($name = 'wdm-downloads', $only_downloads = 0)
    {
        $str = '';
        $args = array(
           'post_type' => 'download',
           'post_status' => 'publish',
           'posts_per_page' => -1,
           'orderby' => 'title',
           'order' => 'ASC',
        );

        $loop = new \WP_Query($args);
        $options[0] = 'Select download...';
        foreach ($loop->posts as $loop_value) {
            $options[$loop_value->ID] = $loop_value->post_title;
        }
        $str .= '<select name="'.$name.'">';
        foreach ($options as $key => $option) {
            if (!$only_downloads) {
                $str .= '<option value="'.$key.'" '. (isset($_POST['save_reminder'])?selected($_POST['wdm-downloads'], $key, false):'').'>'.$option.'</option>';
            } else {
                $str .= '<option value="'.$key.'" >'.$option.'</option>';
            }
        }
        $str .= '</select>';
        return $str;
    }

    public function getExistingDownloadsEmails($name, $active_only = 0)
    {
        global $wpdb;
        $results = $wpdb->get_results("SELECT option_name, option_value FROM `wp_options` WHERE `option_name` LIKE '%wdm_edd_manual_renewal_email_settings_%' OR `option_name` LIKE '%wdm_edd_renewal_email_settings_%'");
        $str = '<select name="'.$name.'">';
        $notices = new \EDD_Recurring_Reminders();
        $notices = $notices->get_notice_periods('renewal');
        $notices['after'] = 'Immediately after renewal';
        $options = [];
        if ($results) {
            $exploded_option_name = '';
            foreach ($results as $email_settings) {
                if ($active_only && !maybe_unserialize($email_settings->option_value)['enabled']) {
                    continue;
                }
                $exploded_option_name = explode('_', $email_settings->option_name);
                $exploded_option_name_count = count($exploded_option_name);
                if (!empty($exploded_option_name[$exploded_option_name_count-2])) {
                    if (strpos($email_settings->option_name, '_manual_')!==false) {
                        $option_value = $exploded_option_name[$exploded_option_name_count-2].'_'.$exploded_option_name[$exploded_option_name_count-1].'_manual';
                        $option_name = get_the_title($exploded_option_name[$exploded_option_name_count-2]).' ** Manual ** '. $notices[$exploded_option_name[$exploded_option_name_count-1]];
                    } else {
                        $option_value = $exploded_option_name[$exploded_option_name_count-2].'_'.$exploded_option_name[$exploded_option_name_count-1].'_auto';
                        $option_name = get_the_title($exploded_option_name[$exploded_option_name_count-2]).' ** Auto ** '. $notices[$exploded_option_name[$exploded_option_name_count-1]] ;
                    }
                }
                $options[$option_value] = $option_name;
            }
            sort($options);
            foreach ($options as $option_value => $option_name) {
                $str .= '<option value="'.$option_value.'" >'.$option_name.'</option>';
            }
            unset($exploded_option_name);
        }
        $str .= '</select>';
        return $str;
    }

    public function getUpgradeRenewEmails($name,$active_only = 0){
        ob_start();
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'upgrade-renew-emails',
            'orderby' 		=> 'title', // or 'date', 'rand'
            'order' 		=> 'ASC', // or 'DESC'
        );
        if($active_only){
            $args['post_status'] = 'publish'; 
        }
        $UpgradeRenewEmails = get_posts( $args );
        $notices = new \EDD_Recurring_Reminders();
        $notices = $notices->get_notice_periods('renewal');
        $notices['after'] = 'Immediately after renewal';
        if ( $UpgradeRenewEmails ) {
            ?>
            <select name="<?php echo $name; ?>" id="<?php echo $name; ?>" >
                <?php
                foreach($UpgradeRenewEmails as $post){
                    $post_id = $post->ID;
                    $post_title = $post->post_title;
                    ?>
                    <option value="<?php echo $post_id; ?>">
                        <?php echo $post_title; ?>
                    </option>
                    <?php
                }
                ?>
            </select>
            <?php
        } 
        
        $result = ob_get_contents();
        ob_clean();
        return $result;
    }

    public function getPeriods()
    {
        $notices = new \EDD_Recurring_Reminders();
        // $reminder_type = 'renewal';
        $str = '';
        $str .='<select name="type" id="edd-notice-type">';
        foreach ($notices->get_notice_types() as $type => $label) :
            $str .='<option value="'.esc_attr($type).'" '.selected($type, $_POST['period'], 0).'>'.esc_html($label).'</option>';
        endforeach;
        $str .= '</select>';
        return $str;
    }

    public function getSubject()
    {
        $str = '<input type="text" name="wdm-renewal-subject" value="">';
        return $str;
    }

    public function getBody()
    {
        $editor_id = 'wdm-renewal-email-body';
        $settings  = array (
            'wpautop'          => true,   // Whether to use wpautop for adding in paragraphs. Note that the paragraphs are added automatically when wpautop is false.
            'media_buttons'    => true,   // Whether to display media insert/upload buttons
            'textarea_name'    => $editor_id,   // The name assigned to the generated textarea and passed parameter when the form is submitted.
            'textarea_rows'    => get_option('default_post_edit_rows', 10),  // The number of rows to display for the textarea
            'tabindex'         => '',     // The tabindex value used for the form field
            'editor_css'       => '',     // Additional CSS styling applied for both visual and HTML editors buttons, needs to include <style> tags, can use "scoped"
            'editor_class'     => '',     // Any extra CSS Classes to append to the Editor textarea
            'teeny'            => false,  // Whether to output the minimal editor configuration used in PressThis
            'dfw'              => false,  // Whether to replace the default fullscreen editor with DFW (needs specific DOM elements and CSS)
            'tinymce'          => true,   // Load TinyMCE, can be used to pass settings directly to TinyMCE using an array
            'quicktags'        => true,   // Load Quicktags, can be used to pass settings directly to Quicktags using an array. Set to false to remove your editor's Visual and Text tabs.
            'drag_drop_upload' => true    // Enable Drag & Drop Upload Support (since WordPress 3.9)
        );
        ob_start();
 
        wp_editor('', $editor_id, $settings);
     
        $str = ob_get_clean();
        // $str .= \_WP_Editors::enqueue_scripts();
        // $str .= print_footer_scripts();
        // $str .= \_WP_Editors::editor_js();
        return $str;
    }

    public function wdmEnqueueCustomAdminJS()
    {
        if (!empty($_GET['page']) && $_GET['page']=='renewal-emails-settings') {
            wp_enqueue_script('wdm-edd-renewal-admin-js', plugins_url('assets/js/wdm-edd-renewal-admin.js', __FILE__), array('jquery'), '1.0.0');
            wp_localize_script('wdm-edd-renewal-admin-js', 'wdm_ajax_object', array( 'ajax_url' => admin_url('admin-ajax.php') ));
        }
    }

    public function saveReminder()
    {
        if (isset($_POST['save_reminder'])) {
            if (! wp_verify_nonce($_POST['edd-recurring-renewal-reminder-notice-nonce'], 'wdm_edd_recurring_renewal_reminder')) {
                wp_die(__('Nonce verification failed', 'edd-recurring'), __('Error', 'edd-recurring'), array( 'response' => 401 ));
            }
            $this->processSaveReminder();
        }
        if (isset($_POST['delete_reminder'])) {
            if (! wp_verify_nonce($_POST['edd-recurring-renewal-reminder-notice-nonce'], 'wdm_edd_recurring_renewal_reminder')) {
                wp_die(__('Nonce verification failed', 'edd-recurring'), __('Error', 'edd-recurring'), array( 'response' => 401 ));
            }
            $this->processDelReminder();
        }
    }

    public function processSaveReminder()
    {
        $err = $this->checkErrors();
        if (!empty($err)) {
            $this->showErrorMessages($err);
        } else {
            $data = array(
                            // 'subject' => $_POST['subject'],
                            // 'body' => $_POST['wdm-renewal-message'],
                            'renewal_email_template_id' => $_POST['wdm-renewal-email-template'],
                            'enabled' => (isset($_POST['status']) && $_POST['status']=='on')?1:0
                        );
            if (isset($_POST['auto-manual']) && $_POST['auto-manual']=='manual') {
                update_option('wdm_edd_manual_renewal_email_settings_'.$_POST['wdm-downloads'].'_'.$_POST['period'], $data, false);
                if (strpos($_POST['period'], '-') !== false) {
                    $message = 'Hello {name},

Your license key for {product_name} is expired.

If you wish to renew your license, simply click the link below and follow the instructions.

Your license expires on: {expiration}.

Your expiring license key is: {license_key}.

Renew now: {renewal_link}.';

                    $subject = __('Your License Key is Expired', 'edd-recurring');
                } else {
                    $message = 'Hello {name},

Your license key for {product_name} is about to expire.

If you wish to renew your license, simply click the link below and follow the instructions.

Your license expires on: {expiration}.

Your expiring license key is: {license_key}.

Renew now: {renewal_link}.';

                    $subject = __('Your License Key is About to Expire', 'edd-recurring');
                }
                $this->saveGeneralReminders($subject, $message);
            } elseif (isset($_POST['auto-manual']) && $_POST['auto-manual']=='auto') {
                update_option('wdm_edd_renewal_email_settings_'.$_POST['wdm-downloads'].'_'.$_POST['period'], $data, false);
                if (strpos($_POST['period'], '-') !== false) {
                    $message = 'Hello {name},

                    Your subscription for {subscription_name} expired on {expiration}.';

                    $subject = __('Your Subscription is Expired', 'edd-recurring');
                } else {
                    $message = 'Hello {name},

                    Your subscription for {subscription_name} will renew on {expiration}.';

                    $subject = __('Your Subscription is About to Renew', 'edd-recurring');
                }
                // Save General Reminders
                $this->saveGeneralReminders($subject, $message, 1);
            }
            echo '<div class="updated notice"><p>Notice added</p></div>';
            return;
        }
    }

    public function saveGeneralReminders($subject, $message, $auto = 0)
    {
        $data = array(
            'send_period' => $_POST['period'],
            'subject'     => $subject,
            'message'     => $message,
            'type'        => 'renewal'
        );
        if ($auto) {
            $notices = get_option('wdm_edd_recurring_reminder_notices', array());
            foreach ($notices as $key => $value) {
                if ($key == $data['send_period']) {
                    unset($value);
                    unset($notices[$key]);
                }
            }
            $notices[$data['send_period']] = $data;
            update_option('wdm_edd_recurring_reminder_notices', $notices);
        } else {
            $notices = get_option('wdm_edd_manual_recurring_reminder_notices', array());
            foreach ($notices as $key => $value) {
                if ($key == $data['send_period']) {
                    unset($value);
                    unset($notices[$key]);
                }
            }
            $notices[$data['send_period']] = $data;
            update_option('wdm_edd_manual_recurring_reminder_notices', $notices);
        }
    }

    public function checkErrors()
    {
        $err = array();
        if (empty($_POST['wdm-downloads'])) {
            $err[] = 'Please select a download.';
        }
        if (empty($_POST['wdm-renewal-email-template'])) {
            $err[] = 'Please select an Email Template.';
        }
        if (empty($_POST['period'])) {
            $err[] = 'Please select a period.';
        }
        if (empty($_POST['auto-manual'])) {
            $err[] = 'Please select whether the reminder is for auto or for manual renewals.';
        }
        return $err;
    }

    public function showErrorMessages($err)
    {
        foreach ($err as $msg) {
            echo '<div class="error notice">
                    <p>'.$msg.'</p>
                </div>';
        }
        return;
    }

    public function processDelReminder()
    {
        if (empty($_POST['wdm-downloads'])) {
            $err[] = 'Please select a download.';
        }
        if (empty($_POST['period'])) {
            $err[] = 'Please select a period.';
        }
        if (!empty($err)) {
            foreach ($err as $msg) {
                echo '<div class="error notice">
                        <p>'.$msg.'</p>
                    </div>';
            }
            return;
        } else {
            if (isset($_POST['auto-manual']) && $_POST['auto-manual']=='auto') {
                delete_option('wdm_edd_renewal_email_settings_'.$_POST['wdm-downloads'].'_'.$_POST['period']);
                $notices = get_option('wdm_edd_recurring_reminder_notices', array());
                foreach ($notices as $key => $value) {
                    if ($key == $_POST['period']) {
                        unset($value);
                        unset($notices[$key]);
                    }
                }
                update_option('wdm_edd_recurring_reminder_notices', $notices);
            } elseif (isset($_POST['auto-manual']) && $_POST['auto-manual']=='manual') {
                delete_option('wdm_edd_manual_renewal_email_settings_'.$_POST['wdm-downloads'].'_'.$_POST['period']);
                $notices = get_option('wdm_edd_manual_recurring_reminder_notices', array());
                foreach ($notices as $key => $value) {
                    if ($key == $_POST['period']) {
                        unset($value);
                        unset($notices[$key]);
                    }
                }
                update_option('wdm_edd_manual_recurring_reminder_notices', $notices);
            }
            echo '<div class="updated notice"><p>Notice deleted</p></div>';
            return;
        }
    }
    /** * @SuppressWarnings(PHPMD) */
    public static function wdmScheduledReminders()
    {
        //error_log('wdmScheduledReminders called');
        if (defined('EDD_RECURRING_PLUGIN_DIR')) {
            //error_log('EDD_RECURRING_PLUGIN_DIR defined');
            $reminders_enabled = $this->remindersEnabled();

            edd_debug_log('Running EDD_Recurring_Reminders::scheduled_reminders.', true);
            //error_log('reminders_enabled '. json_encode($reminders_enabled));
            foreach ($reminders_enabled as $type => $enabled) {
                if (! $enabled) {
                    continue;
                }

                $notices = $this->getNotices($type);

                edd_debug_log('Beginning reminder processing. Found ' . count($notices) . ' reminder templates.', true);
                //error_log('notices '. json_encode($notices));
                foreach ($notices as $notice_id => $notice) {
                    edd_debug_log('Processing ' . $notice['send_period'] . ' reminder template.', true);

                    $subscriptions = $this->getReminderSubscriptions($notice['send_period'], $type);
                    //error_log('$subscriptions TAriq' . json_encode($subscriptions));
                    edd_debug_log('Found ' . count($subscriptions) . ' subscriptions to send reminders for.', true);

                    if (! $subscriptions) {
                        continue;
                    }

                    $processed_subscriptions = 0;
                    foreach ($subscriptions as $subscription) {
                        //error_log('Inside foreach');
                        // Ensure the subscription should renew based on payments made and bill times
                        if ($type == 'renewal' && $subscription->bill_times != 0 && $subscription->get_total_payments() >= $subscription->bill_times) {
                            edd_debug_log('Ignored renewal notice for subscription ID ' . $subscription->id . ' due being billing times being complete.', true);
                            continue;
                        }
                        $download_notice = $this->wdmGetDownloadRenewalEmailData(edd_get_download($subscription->product_id).'_'.$notice['send_period']);
                        if ($download_notice==0 || empty($download_notice['enabled'])) {
                            continue;
                        }
                        //error_log('After first if');
                        // Ensure an expiration notice isn't sent to an auto-renew subscription
                        if ($type == 'expiration' && $subscription->get_status() == 'active' && ( $subscription->get_total_payments() < $subscription->bill_times || $subscription->bill_times == 0 )) {
                            edd_debug_log('Ignored expiration notice for subscription ID ' . $subscription->id . ' due to subscription being active.', true);
                            continue;
                        }
                        //error_log('After second if');

                        // Ensure an expiration notice isn't sent to a still-trialling subscription
                        if ($type == 'expiration' && $subscription->get_status() == 'trialling') {
                            edd_debug_log('Ignored expiration notice for subscription ID ' . $subscription->id . ' due subscription still trialling.', true);
                            continue;
                        }

                        if (!$this->canSendEmailToUser($subscription->customer->email)) {
                            edd_debug_log('Ignored expiration notice for subscription ID ' . $subscription->id . ' due email is unsubscribed from our list.', true);
                            continue;
                        }

                        $sent_time = get_user_meta($subscription->customer->user_id, sanitize_key('_edd_recurring_reminder_sent_' . $subscription->id . '_' . $notice_id . '_' . $subscription->get_total_payments()), true);
                        //error_log('$sent_time ' . $sent_time);

                        if ($sent_time) {
                            edd_debug_log('Skipping renewal reminder for subscription ID ' . $subscription->id . ' and reminder ' . $notice['send_period'] . '. Previously sent on ' . date_i18n(get_option('date_format'), $sent_time), true);
                            continue;
                        }

                        edd_debug_log('Renewal reminder not previously sent for subscription ID ' . $subscription->id . ' for reminder ' . $notice['send_period'], true);
                        //error_log('$notice Tariq ' . $notice_id);
                        $this->sendReminder($subscription->id, $notice_id, $notice['send_period']);
                        $processed_subscriptions++;
                    }

                    edd_debug_log('Finished processing ' . $processed_subscriptions . ' for ' . $notice['send_period'] . ' reminder template.', true);
                }
            }

            edd_debug_log('Finished EDD_Recurring_Reminders::scheduled_reminders.', true);
        }
    }
    /** * @SuppressWarnings(PHPMD) */
    public function remindersEnabled()
    {
        $types = $this->getNoticeTypes();
        $ret = array();
        foreach ($types as $type => $label) {
            $ret[ $type ] = edd_get_option('recurring_send_' . $type . '_reminders', false);
        }
        return apply_filters('edd_recurring_send_reminders', $ret);
    }

    public function getNoticeTypes()
    {
        $types = array(
            'renewal'    => __('Renewal', 'edd-recurring'),
            'expiration' => __('Expiration', 'edd-recurring'),
        );
        return apply_filters('edd_recurring_get_reminder_notice_types', $types);
    }

    public function getNotices($type = 'all')
    {
        $notices = get_option('wdm_edd_recurring_reminder_notices', array());
        if ($type != 'all') {
            $notices_hold = array();

            foreach ($notices as $key => $notice) {
                if ($notice['type'] == $type) {
                    $notices_hold[ $key ] = $notice;
                }
            }

            $notices = $notices_hold;
        }

        return apply_filters('edd_recurring_get_reminder_notices', $notices, $type);
    }

    public function getManualReminderNotices($type = 'all')
    {
        $notices = get_option('wdm_edd_recurring_reminder_notices', array());
        if ($type != 'all') {
            $notices_hold = array();

            foreach ($notices as $key => $notice) {
                if ($notice['type'] == $type) {
                    $notices_hold[ $key ] = $notice;
                }
            }

            $notices = $notices_hold;
        }

        return apply_filters('edd_recurring_get_reminder_notices', $notices, $type);
    }

    public function getReminderSubscriptions($period = '+1month', $type = false)
    {

        if (! $type) {
            return false;
        }
        if (!class_exists('EDD_Subscriptions_DB')) {
            require_once EDD_RECURRING_PLUGIN_DIR . 'includes/edd-subscriptions-db.php';
        }
        $args = array();
        if (!(strpos($period, '-') === false)) {
            $type = 'expiration';
        }

        //error_log('$type is : '.$type);
        //error_log('$period is : '.$period);
        switch ($type) {
            case "renewal":
                // Doesn't make sense to give someone a notice of an autorenewal if it has already expired
                if (stristr($period, '-') === true) {
                    return false;
                }

                $args['renewal'] = array(
                    'number'     => 99999,
                    'status'     => 'active',
                    'expiration' => array(
                        'start' => $period . ' midnight',
                        'end'   => date('Y-m-d H:i:s', strtotime($period . ' midnight') + ( DAY_IN_SECONDS - 1 )),
                    ),
                );
                break;

            case "expiration":
                // If we are looking at expired subscriptions then we need to swap our start and end period checks
                if (stristr($period, '-') === true) {
                    $start = date('Y-m-d H:i:s', strtotime($period . ' midnight') + ( DAY_IN_SECONDS - 1 ));
                    $end   = $period . ' midnight';
                } else {
                    $start = $period . ' midnight';
                    $end   = date('Y-m-d H:i:s', strtotime($period . ' midnight') + ( DAY_IN_SECONDS - 1 ));
                }

                $args[ 'expiration' ] = array(
                    'number'        => 99999,
                    'expiration'    => array(
                        'start'     => $start,
                        'end'       => $end
                    ),
                );
                break;
        }

        $args = apply_filters('edd_recurring_reminder_subscription_args', $args);
        //error_log('$args Tariq ' . json_encode($args));
        $subs_db = new \EDD_Subscriptions_DB();
        $subscriptions = $subs_db->get_subscriptions($args[ $type ]);

        if (! empty($subscriptions)) {
            return $subscriptions;
        }
        return false;
    }
    /** * @SuppressWarnings(PHPMD) */
    public function sendReminder($subscription_id = 0, $notice_id = 0, $send_period = '')
    {

        if (empty($subscription_id) || $send_period=='') {
            return;
        }
        if (!class_exists('EDD_Subscription')) {
            //error_log('Included EDD_Subscription class');
            require_once EDD_RECURRING_PLUGIN_DIR . 'includes/edd-subscription.php';
        }

        $this->subscription = new \EDD_Subscription($subscription_id);

        if (empty($this->subscription)) {
            //error_log('empty($this->subscription)');
            return;
        }

        // $notices = new \EDD_Recurring_Reminders();
        $send    = true;
        $user    = get_user_by('id', $this->subscription->customer->user_id);
        
        if (! $user || ! in_array('edd_subscriber', $user->roles, true) || ! $send || ! empty($user->post_parent)) {
            //error_log('edd_subscriber role is applicable)');
            return;
        }

        $email_to   = $this->subscription->customer->email;
        // $notice     = $notices->get_notice( $notice_id );
        $download      = edd_get_download($this->subscription->product_id);
        $notice = $this->wdmGetDownloadRenewalEmailData($download->ID.'_'.$send_period);
        //error_log('$notice in sendReminder ' . json_encode($notice));
        if (!empty($notice['body']) && !empty($notice['subject']) && !empty($notice['enabled'])) {
            $message    = stripslashes($notice['body']);
            $message    = $this->filterReminderTemplateTags($message, $download, $subscription_id);

            $subject    = stripslashes($notice['subject']);
            $subject    = $this->filterReminderTemplateTags($subject, $download, $subscription_id);

            EDD()->emails->send($email_to, $subject, $message);
            
            $log_id = wp_insert_post(
                array(
                    'post_title'   => __('LOG - Subscription Reminder Notice Sent', 'edd-recurring'),
                    'post_name'    => 'log-subscription-reminder-notice-' . $subscription_id . '_sent-' . $this->subscription->customer_id . '-' . md5(time()),
                    'post_type'    => 'edd_subscription_log',
                    'post_status'  => 'publish'
                 )
            );

            add_post_meta($log_id, '_edd_recurring_log_customer_id', $this->subscription->customer_id);
            add_post_meta($log_id, '_edd_recurring_log_subscription_id', $subscription_id);
            add_post_meta($log_id, '_edd_recurring_reminder_notice_id', (int) $notice_id);

            if (isset($notice[ 'type' ])) {
                add_post_meta($log_id, '_edd_recurring_reminder_notice_type', $notice[ 'type' ]);
            }

            wp_set_object_terms($log_id, 'subscription_reminder_notice', 'edd_log_type', false);

            // Prevents reminder notices from being sent more than once
            add_user_meta($this->subscription->customer->user_id, sanitize_key('_edd_recurring_reminder_sent_' . $subscription_id . '_' . $notice_id . '_' . $this->subscription->get_total_payments()), time());
        }
    }

    public function filterReminderTemplateTags($text = '', $download = '', $subscription_id = 0)
    {
        if (empty($download)) {
            return '';
        }
        $customer_name = $this->subscription->customer->name;
        $expiration    = strtotime($this->subscription->expiration);

        $text = str_replace('{name}', $customer_name, $text);
        $text = str_replace('{subscription_name}', $download->get_name(), $text);
        $text = str_replace('{expiration}', date_i18n('F j, Y', $expiration), $text);
        $text = str_replace('{amount}', edd_currency_filter(edd_format_amount($this->subscription->recurring_amount)), $text);

        return apply_filters('edd_recurring_filter_reminder_template_tags', $text, $subscription_id);
    }

    public function wdmEddRecurringSendReminder($send, $subscription_id, $notice_id)
    {
        if ($subscription_id) {
            $this->subscription = new \EDD_Subscription($subscription_id);
            $download = edd_get_download($this->subscription->product_id);
            $notices = new \EDD_Recurring_Reminders();
            $notice = $notices->get_notice($notice_id);
            $notice = $this->wdmGetDownloadRenewalEmailData($download->ID.'_'.$notice['send_period']);
            if (!empty($notice['body']) && !empty($notice['subject']) && !empty($notice['enabled'])) {
                add_user_meta($this->subscription->customer->user_id, sanitize_key('_edd_recurring_reminder_sent_' . $subscription_id . '_' . $notice_id . '_' . $this->subscription->get_total_payments()), time());
                return false;
            }
        }
        return $send;
    }

    public function wdmSendPaymentReceived($subscription_id = 0, $expiration = '0000-00-00 00:00:00', EDD_Subscription $subscription = null, $payment_id = 0)
    {

        // Since it's possible to renew a subscription without a payment, we should not send an email if none is specified.
        if (empty($payment_id)) {
            return;
        }

        $this->subscription = new \EDD_Subscription($subscription_id);
        $payment            = edd_get_payment($payment_id);
        $download = edd_get_download($this->subscription->product_id);
        $notice = $this->wdmGetDownloadRenewalEmailData($download->ID.'_after');
        $email_to = $this->subscription->customer->email;
        if (!empty($notice['body']) && !empty($notice['subject']) && !empty($notice['enabled'])) {
            $subject  = stripslashes($notice['subject']);
            $subject  = $this->paymentReceivedTemplateTags($subject, $payment->total, $download);
            $message  = stripslashes($notice['body']);
            $message  = $this->paymentReceivedTemplateTags($message, $payment->total, $download);
        } else {
            $subject  = apply_filters('edd_recurring_payment_received_subject', edd_get_option('payment_received_subject'));
            $message  = edd_get_option('payment_received_message');
            $message  = $this->paymentReceivedTemplateTags($message, $payment->total, $download);
        }
        EDD()->emails->send($email_to, $subject, $message);
        unset($expiration);
        unset($subscription);
    }

    public function paymentReceivedTemplateTags($text = '', $amount = '', $download = null)
    {

        // $download      = edd_get_download( $this->subscription->product_id );
        $customer_name = $this->subscription->customer->name;
        $expiration    = strtotime($this->subscription->expiration);

        $text = str_replace('{name}', $customer_name, $text);
        $text = str_replace('{subscription_name}', $download->get_name(), $text);
        $text = str_replace('{expiration}', date_i18n('F j, Y', $expiration), $text);
        $text = str_replace('{amount}', edd_currency_filter(edd_format_amount($amount)), $text);

        return apply_filters('edd_recurring_payment_received_template_tags', $text, $amount, $this->subscription->id);
    }

    public function wdmGetDownloadRenewalEmailTestSend()
    {
        if (wp_doing_ajax()) {
            if (isset($_POST['email_template']) && isset($_POST['to'])) {
                $email_to = $_POST['to'];
                $email_template_id = $_POST['email_template'];

                $template = $this->get_update_renew_email($email_template_id);
                if(!empty($template)){
                    $subject = $template->subject;
                    $body = $template->body;
                    EDD()->emails->send($email_to, stripslashes($subject), stripslashes($body));
                    echo 1;
                    die();
                }   
                echo '0';
                die();
            }
            echo '0';
            die();
        }
        return;
    }

    public function wdmCopyEmailData()
    {
        if (wp_doing_ajax()) {
            if (isset($_POST['copy-wdm-downloads'])) {
                $exploded_selected_option = explode('_', $_POST['copy-wdm-downloads']);
                if ($exploded_selected_option[2]=='auto') {
                    $wdm_edd_renewal_email_settings = get_option('wdm_edd_renewal_email_settings_'.$exploded_selected_option[0].'_'.$exploded_selected_option[1], 0);
                } elseif ($exploded_selected_option[2]=='manual') {
                    $wdm_edd_renewal_email_settings = get_option('wdm_edd_manual_renewal_email_settings_'.$exploded_selected_option[0].'_'.$exploded_selected_option[1], 0);
                }
                if(isset($wdm_edd_renewal_email_settings['renewal_email_template_id'])){
                    $template_id = $wdm_edd_renewal_email_settings['renewal_email_template_id'];
                    $email_template = $this->get_update_renew_email($template_id);
                    $subject = ($email_template->subject);
                    $body = ($email_template->body);
                }
                else{
                    $subject = ($wdm_edd_renewal_email_settings['subject']);
                    $body = ($wdm_edd_renewal_email_settings['body']);
                }

                $data = array(
                            'subject' => stripslashes($subject),
                            'body' => stripslashes($body),
                            'enabled' => !empty($wdm_edd_renewal_email_settings['enabled'])?$wdm_edd_renewal_email_settings['enabled']:0,
                            'auto' => ($_POST['automanual']=='auto')?1:0
                            );
                echo empty($wdm_edd_renewal_email_settings)?0:json_encode($data);
                die();
            }
            echo '0';
            die();
        }
        return;
    }

    public function canSendEmailToUser($email)
    {
        if ($this->isUserUnsubscribed($email)) {
            return 0;
        }
        return 1;
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

    public function wdmEddSlScheduledReminders()
    {
        // error_log('Tariq Testing');
        global $edd_options;

        // error_log('Tariq wdmEddSlScheduledReminders Called');
        if (! isset($edd_options['edd_sl_send_renewal_reminders'])) {
            return;
        }
        // $edd_sl_emails = new EDD_SL_Emails;

        $notices = $this->wdmGetManualRenewalGeneralNotices();
        // error_log(print_r($notices,true));
        foreach ($notices as $notice_id => $notice) {
            if ('expired' == $notice['send_period']) {
                continue; // Expired notices are triggered from the set_license_status() method of EDD_Software_Licensing
            }

            $keys = $this->wdmEddSlGetExpiringLicenses($notice['send_period']);
            
            if (! $keys) {
                continue;
            }
            // error_log(print_r($keys,true));

            foreach ($keys as $license_id) {
                // error_log('Tariq Inside For ');
                if (! apply_filters('edd_sl_send_scheduled_reminder_for_license', true, $license_id, $notice_id)) {
                    continue;
                }
                
                $license = edd_software_licensing()->get_license($license_id);
                $download_notice = $this->wdmGetDownloadManualRenewalEmailData($license->download_id,$notice['send_period']);
                if ($download_notice==0 || empty($download_notice['enabled'])) {
                    // error_log('Tariq Inside If ');
                    // error_log($license_id);
                    // error_log($notice['send_period']);
                    // error_log($license->download_id.'_'.$notice['send_period']);
                    continue;
                }

                // Sanity check to ensure we don't send renewal notices to people with lifetime licenses
                if ($license->is_lifetime) {
                    continue;
                }
                // error_log('Outside '.$license_id);
                $sent_time = $license->get_meta(sanitize_key('_edd_sl_renewal_sent_' . $notice['send_period']));
                if ($sent_time) {
                    $expire_date = strtotime($notice['send_period'], $sent_time);

                    if (current_time('timestamp') < $expire_date) {
                        // The renewal period isn't expired yet so don't send again
                        continue;
                    }

                    $license->delete_meta(sanitize_key('_edd_sl_renewal_sent_' . $notice['send_period']));
                }
                $this->wdmSendRenewalReminder($license->ID, $notice_id);
            }
        }
    }

    public function wdmGetManualRenewalGeneralNotices()
    {
        $notices = get_option('wdm_edd_manual_recurring_reminder_notices', array());
        return apply_filters('edd_manual_recurring_get_reminder_notices', $notices);
    }

    public function wdmGetManualRenewalGeneralNotice($notice_id)
    {
        $notices = get_option('wdm_edd_manual_recurring_reminder_notices', array());
        if (!empty($notices[$notice_id])) {
            return $notices[$notice_id];
        } else {
            return '';
        }
    }

    public function wdmEddSlGetExpiringLicenses($period = '+1month')
    {

        $args = array(
            'number'     => - 1,
            'fields'     => 'ids',
            'parent'     => 0,
            'expiration' => array(
                'start' => strtotime($period . ' midnight', current_time('timestamp')),
                'end'   => strtotime($period . ' midnight', current_time('timestamp')) + ( DAY_IN_SECONDS - 1 ),
            )
        );

        $args  = apply_filters('edd_sl_expiring_licenses_args', $args);
        $keys  = edd_software_licensing()->licenses_db->get_licenses($args);

        if (! $keys) {
            return false; // no expiring keys found
        }

        return $keys;
    }

    public function wdmSendRenewalReminder($license_id = 0, $notice_id = 0)
    {
        global $edd_options;
        if (empty($license_id) || !$edd_options['edd_sl_send_renewal_reminders']) {
            return false;
        }
        $send    = true;
        $license = edd_software_licensing()->get_license($license_id);
        $email_to = $this->getCustomerEmailFromLicense($license);

        if ($license->is_lifetime || $this->isUserUnsubscribed($email_to) || 'disabled' === $license->status) {
            $send = false;
        }

        $send = apply_filters('edd_sl_send_renewal_reminder', $send, $license->ID, $notice_id);

        if (! $license || ! $send || ! empty($license->parent)) {
            return false;
        }


        // Will check in subscribe.wisdmlabs.com
        if ($this->isUserUnsubscribed($email_to)) {
            return false;
        }
        $sent = $this->processSendReminder($license, $notice_id, $email_to);
        return $sent;
    }

    public function getCustomerEmailFromLicense($license)
    {
        $customer = false;
        if (class_exists('EDD_Customer')) {
            $customer = new \EDD_Customer($license->customer_id);
        }

        if (empty($customer->id)) {
            // Remove the post title to get just the email
            $title      = $license->get_name();
            $title_pos  = strpos($title, '-') + 1;
            $length     = strlen($title);
            $email_to   = substr($title, $title_pos, $length);
        }
        return ! empty($customer->id) ? $customer->email : $email_to;
    }

    public function processSendReminder($license, $notice_id, $email_to)
    {
        $notice     = $this->wdmGetDownloadManualRenewalEmailData($license->download_id, $notice_id);
        if (empty($notice['body'])||empty($notice['subject'])||empty($notice['enabled'])) {
            $notice = $this->wdmGetManualRenewalGeneralNotice($notice_id);
            if (!empty($notice['message'])) {
                $notice['body'] = $notice['message'];
            }
        }
        $message    = ! empty($notice['body']) ? $notice['body'] : __("Hello {name},\n\nYour license key for {product_name} is about to expire.\n\nIf you wish to renew your license, simply click the link below and follow the instructions.\n\nYour license expires on: {expiration}.\n\nYour expiring license key is: {license_key}.\n\nRenew now: {renewal_link}.", "edd_sl");
        $message    = $this->wdmFilterReminderTemplateTags($message, $license->ID);

        $subject    = ! empty($notice['subject']) ? $notice['subject'] : __('Your License Key is About to Expire', 'edd_sl');
        $subject    = $this->wdmFilterReminderTemplateTags($subject, $license->ID);


        $message = stripslashes($message);
        $subject = stripslashes($subject);
        $sent = '';
        if (class_exists('EDD_Emails')) {
            $sent = EDD()->emails->send($email_to, $subject, $message);
            edd_debug_log('Manual Reminder Email: ', true);
            edd_debug_log('$email_to: '.$email_to, true);
            edd_debug_log('$subject: '.$subject, true);
            edd_debug_log('$message: '.$message, true);
        } else {
            $from_name  = get_bloginfo('name');
            $from_email = get_bloginfo('admin_email');
            $headers    = "From: " . stripslashes_deep(html_entity_decode($from_name, ENT_COMPAT, 'UTF-8')) . " <$from_email>\r\n";
            $headers   .= "Reply-To: ". $from_email . "\r\n";

            $sent = wp_mail($email_to, $subject, $message, $headers);
            edd_debug_log('Manual Reminder Email: ', true);
            edd_debug_log('$email_to: '.$email_to, true);
            edd_debug_log('$subject: '.$subject, true);
            edd_debug_log('$message: '.$message, true);
        }

        if ($sent) {
            $log_id = $license->add_log(__('LOG - Renewal Notice Sent', 'edd_sl'), null, 'renewal_notice');
            add_post_meta($log_id, '_edd_sl_renewal_notice_id', $notice_id);

            $license->update_meta(sanitize_key('_edd_sl_renewal_sent_' . $notice['send_period']), current_time('timestamp')); // Prevent renewal notices from being sent more than once
        }
        return $sent;
    }

    public function wdmFilterReminderTemplateTags($text = '', $license_id = 0)
    {
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
        $renewal_link    = apply_filters('edd_sl_renewal_link', $license->get_renewal_url());
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
        if (! empty($discount)) {
            $text = str_replace('{renewal_discount}', $discount . '%', $text);
        };
        $html_link = sprintf('<a href="%s">%s</a>', $renewal_link, $renewal_link);
        $text = str_replace('{renewal_link}', $html_link, $text);
        $text = str_replace('{renewal_url}', $renewal_link, $text);
        $text = str_replace('{unsubscribe_url}', $license->get_unsubscribe_url(), $text);

        return apply_filters('edd_sl_renewal_message', $text, $license->ID);
    }

    public function wdmGetDownloadManualRenewalEmailData($download, $notice_id = 0)
    {
        if ($download) {
            $result = get_option('wdm_edd_manual_renewal_email_settings_'.$download.'_'.$notice_id, 0); 
            if(isset($result['renewal_email_template_id'])){
                $template_id = $result['renewal_email_template_id'];
                $email_template = $this->get_update_renew_email($template_id);
                $result['subject'] = ($email_template->subject);
                $result['body'] = ($email_template->body);
            }
            return $result;
        }
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new EddSettingsCustomRenewalEmails;
        }
        return self::$instance;
    }
}
