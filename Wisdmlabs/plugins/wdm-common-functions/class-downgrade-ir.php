<?php
namespace WDMCommonFunctions;

/**
 * DowngradeIR to show text and link to download older version of IR
 */
class DowngradeIR extends UpgradeShortcodeButton
{
    private static $instance;
    
    /**
     * __construct includes hook calls on ajax processes to process upgrade requests
     *
     * @return void
     */
    private function __construct()
    {
        add_shortcode('wdm_downgrade_ir', array($this,'wdm_downgrade_ir'));
        add_shortcode('wdm_downgrade_ldgr', array($this,'wdm_downgrade_ldgr'));
        add_shortcode('wdm_downgrade_elumine', array($this,'wdm_downgrade_elumine'));
    }

    public function wdm_downgrade_elumine($atts){
        $a = shortcode_atts(array(
            'link' => '#',
            'user_id' => get_current_user_id(),
            'download' => 127679
        ), $atts);
        if(!$this->get_downloads_active_license($a['user_id'],$a['download'])){
            return ;
        }
        ob_start();
        ?>
        <p>
        Hello,<br/>
        WISDM eLumine v2.8.0 has been rolled out. This update brings major changes to the theme in terms of settings and other configurations. Hence, we highly recommend you take a full site backup before updating the theme to avoid loss of content/site data. 
        </p>
        <p>
        In case you have updated to v2.8.0 of the WISDM eLumine theme for LearnDash from an older version and are facing issues on your site due to the update, don't worry we got you. Make sure to follow the steps mentioned below: 
        </p>
        <p>
            <ol>
                <li>Download the v2.7.2 from <a href="<?php echo $a['link']?>" target="_blank" download>here</a> upload it back on your site, to make sure everything is restored back to normal.</li>
                <li>Address the issue you were facing with the v2.8.0  (if any) in a email addressed to <a href="mailto:helpdesk@wisdmlabs.com">helpdesk@wisdmlabs.com</a> and we will resolve it for you.</li>
            </ol>
        </p>
        <?php
        return ob_get_clean();
    }

    public function wdm_downgrade_ir($atts){
        $a = shortcode_atts(array(
            'link' => '#',
            'user_id' => get_current_user_id(),
            'download' => 20277
        ), $atts);
        if(!$this->get_downloads_active_license($a['user_id'],$a['download'])){
            return;
        }
        ob_start();
        ?>
        <p>
        The WISDM Instructor Role v4.0 is out now. This is a major update with many changes on the Instructor Dashboard. We recommend all users to take a full site backup before updating the plugin. 
        </p>
        <p>
        In case you have updated the WISDM Instructor Role plugin to the latest v4.0 from an older version and are facing any issues, please follow the steps below:
            <ol>
                <li>Download the v3.6.2 from <a href="<?php echo $a['link']?>" target="_blank" download>here</a> and replace this on your site to get rid of any urgent issues. </li>
                <li>Address the issue you were facing with the v4.2.0 (if you are facing any) in a mail to helpdesk@wisdmlabs.com and we will resolve it for you.</li>
            </ol>
        </p>
        <?php
        return ob_get_clean();
    }

    public function wdm_downgrade_ldgr($atts){
        $a = shortcode_atts(array(
            'link' => '#',
            'user_id' => get_current_user_id(),
            'download' => 44670
        ), $atts);
        if(!$this->get_downloads_active_license($a['user_id'],$a['download'])){
            return;
        }
        ob_start();
        ?>
        <p>
        Hello,<br/>
        In case you have updated the WISDM Group Registration v4.3.0 from an older version, or if  there are any custom changes made to the plugin that are not working after the latest update, follow these steps:
        </p>
        <p>
            <ol>
                <li>Download the v4.2.3 from <a href="<?php echo $a['link']?>" target="_blank" download>here</a> and upload it back on your site, to make sure everything is restored back to normal.</li>
                <li>Address the issue you were facing with the v4.3.0 (if you are facing any) in a mail to <a href="mailto:helpdesk@wisdmlabs.com">helpdesk@wisdmlabs.com</a> and we will resolve it for you.</li>
            </ol>
        </p>
        <?php
        return ob_get_clean();
    }
    
    
    /**
     * getInstance to get object of the current class
     *
     * @return void
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new DowngradeIR;
        }
        return self::$instance;
    }
}
