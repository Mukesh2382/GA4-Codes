<?php
namespace WDMCommonFunctions;

/**
 * UpgradeShortcodeButton to create upgrade button shortcode
 */
class UpgradeShortcodeButton
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
        // Upgrade Button shortcode Adding
        add_action('init', array($this,'upgrade_button_shortcode'));
    }
    
    /**
     * upgrade_button_shortcode
     *
     * @return void
     */
    public function upgrade_button_shortcode()
    {
        add_shortcode('wdm_upgrade_button', array($this,'wdm_upgrade_func'));
    }

    
    /**
     * wdm_upgrade_func
     *
     * @param  array $atts
     * @return void
     */
    public function wdm_upgrade_func($atts)
    {
        $a = shortcode_atts(array(
            'existing_download' => '',
            'upgrade_id' => 0,
            'user_id' => get_current_user_id(),
            'button_text' => 'Buy Now',
            'default_url' => ''
        ), $atts);
        $url = '';
        $style = 'font-size:14px;border-radius:3px;border-radius:3px;';
        if (!empty($a['existing_download']) && !empty($a['upgrade_id']) && !empty($a['user_id'])) {
            $license = $this->get_downloads_active_license($a['user_id'], $a['existing_download']);
            $upgrades = edd_sl_get_upgrade_paths($a['existing_download']);

            foreach ($upgrades as $upgrade_id => $upgrade) {
                if ($a['upgrade_id']==$upgrade_id) {
                    $url = edd_sl_get_license_upgrade_url($license, $upgrade_id);
                }
            }
            if ($url) {
                return '<a class="wdm-upgrade-btn" href="'.$url.'"><button style="'.$style.'" class="alt-btn btn-secondary">'.$a['button_text'].'</button></a>';
            } elseif ($a['default_url']) {
                return '<a class="wdm-upgrade-btn" href="'.$a['default_url'].'"><button style="'.$style.'" class="alt-btn btn-secondary">'.$a['button_text'].'</button></a>';
            }
        }
        if (empty($a['user_id'])) {
            global $wp;
            
            $url = esc_url(add_query_arg('redirect_to', urlencode(home_url($wp->request)), home_url('login')));
            return '<a class="wdm-upgrade-btn" href="'.$url.'"><button style="'.$style.'" class="alt-btn btn-secondary">'.$a['button_text'].'</button></a>';
        }
        return '';
    }
    
    /**
     * get_downloads_active_license
     *
     * @param  int $user
     * @param  int $download
     * @return void
     */
    public function get_downloads_active_license($user, $download)
    {
        $customer = EDD()->customers->get_customer_by('user_id', $user);
        if ($customer) {
            $licenses = edd_software_licensing()->licenses_db->get_licenses(array(
                'number'      => -1,
                'customer_id' => $customer->id,
                'orderby'     => 'id',
                'order'       => 'ASC',
                // 'status'      => 'active'
            ));
            if (! empty($licenses)) {
                foreach ($licenses as $license) {
                    if ($license->download_id==$download && ($license->status=='active' || $license->status=='inactive')) {
                        return $license->ID;
                    }
                }
            }
        }
        return 0;
    }
    /**
     * getInstance
     *
     * @return void
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new UpgradeShortcodeButton;
        }
        return self::$instance;
    }
}
