<?php
namespace WDMCommonFunctions;

/**
 * UpgradePopup to add a popup to allow upgrades using shortcode
 */
class UpgradePopup
{
    private static $instance;
    public static $style_script_added=0;
    public static $popup_added=array();

    /**
     * __construct includes hook calls on ajax processes to process upgrade requests
     *
     * @return void
     */
    private function __construct()
    {
        add_action("wp_ajax_wdm_upgrade_popup_process", array($this,"wdmUpgradePopupProcess"));
        add_action("wp_ajax_nopriv_wdm_upgrade_popup_process", array($this,"wdmUpgradePopupProcess"));
        $this->nonce = wp_create_nonce('wdm-upgrade-nonce');
    }
    
    /**
     * wdmUpgradePopupProcess handling ajax process to upgrade licenses
     *
     * @return void
     */
    function wdmUpgradePopupProcess(){
        if(!empty($_POST['nonce'])){
            if(wp_verify_nonce($_POST['nonce'],'wdm-upgrade-nonce') && !empty($_POST['license'])){
                $download = !empty($_POST['download'])?$_POST['download']:0;
                $option = !empty($_POST['option'])?$_POST['option']:0;
                // $upgrades = edd_sl_get_upgrade_paths( $download );
                if($download){
                    $license = edd_software_licensing()->get_license_by_key($_POST['license']);
                    if($license){
                        $upgrades = edd_sl_get_license_upgrades($license);
                        if(edd_has_variable_prices($download) && $option){
                            foreach($upgrades as $id=>$upgrade){
                                if(!empty($upgrade['download_id'])){
                                    if($upgrade['download_id']==$download){
                                        if($upgrade['price_id']==$option){
                                            $data['checkout'] = edd_sl_get_license_upgrade_url($license,$id);
                                            echo json_encode($data);
                                            die;
                                        }
                                    }
                                }
                            }
                        }elseif(!edd_has_variable_prices($download)){
                            foreach($upgrades as $id=>$upgrade){
                                if(!empty($upgrade['download_id'])){
                                    if($upgrade['download_id']==$download){
                                        $data['checkout'] = edd_sl_get_license_upgrade_url($license,$id);
                                        echo json_encode($data);
                                        die;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $data['message'] = 'Invalid license!';
        echo json_encode($data);
        die;
    }
    
    /**
     * shortcodeCallback shortcode callback which adds functionality related with the shortcode to allow upgrades using a license key
     *
     * @param  mixed $atts
     * @return void
     */
    public function shortcodeCallback($atts)
    {
        ob_start();
        $atts = shortcode_atts(
            array(
                    'download'                  => '0',
                    'option'                    => '0',
                    'button_text'               => 'Upgrade',
                    'popup_class'               => '',
                    'title'                     => 'Upgrade',
                    'description'               => '',
                    'button_class'              => '',
                    'source_button_selector'    => '.wdm-upgrade-popup-open'
                ),
            $atts,
            'wdm_upgrade_popup'
        );
        if ($atts['source_button_selector'] && $atts['download']) {
            $atts['random'] = rand();
            $this->generatePopup($atts);
            if(UpgradePopup::$style_script_added==0){
                UpgradePopup::$style_script_added=1;
                $this->generateStyling($atts);
                $this->jQueryProcessing($atts);
            }
        }
        return ob_get_clean();
    }
    
    /**
     * generatePopup generates html for the popup
     *
     * @param  mixed $atts
     * @return void
     */
    public function generatePopup($atts)
    {
        if(!in_array($atts['download'].$atts['option'],UpgradePopup::$popup_added)){
            $html = '<div style="display:none" data-id="upc-'.$atts['download'].$atts['option'].'" id="upc-'.$atts['random'].'" class="wdm-upgrade-popup-container">';
            $html .= '<div class="wdm-upgrade-popup-container-content">';
            $html .= '<span class="wdm-upgrade-popup-close"><img draggable="false" role="img" class="emoji" alt="✖" src="'.get_stylesheet_directory_uri().'/images/popup-cross.png"></span>';
            $html .= '<h4 class="wdm-upgrade-popup-title">'.$atts['title'].'</h4>';
            $html .= '<input type="text" id="upi-'.$atts['random'].'" class="wdm-upgrade-popup-input" placeholder="Enter license key...">';
            if(!empty($atts['description'])){
                $html .= '<p class="wdm-upgrade-popup-description">'.$atts['description'].'</p>';
            }
            $html .= '<span id="upn-'.$atts['random'].'" class="wdm-upgrade-popup-notice">&nbsp;</span>';
            $html .= '<button class="wdm-upgrade-cta" data-download="'.$atts['download'].'" data-option="'.$atts['option'].'" data-nonce="'.$this->nonce.'" data-notice="upn-'.$atts['random'].'" data-input="upi-'.$atts['random'].'">'.$atts['button_text'].'</button>';
            $html .= '<p class="wdm-upgrade-popup-ajax-loader">&nbsp;<img class="wdm-upgrade-ajax-loader" alt="Loading..." src="'.get_stylesheet_directory_uri().'/images/ajax-loader-cf.gif"></p>';
            $html .= '</div></div>';    
            echo $html;
            array_push(UpgradePopup::$popup_added,$atts['download'].$atts['option']);
        }
    }
    
    /**
     * generateStyling generates styling for the popup
     *
     * @param  mixed $atts
     * @return void
     */
    public function generateStyling($atts)
    {
        ?>
        <script>
        jQuery(document).ready(function(){
            if(!(jQuery('#wdm-upgrade-popup-style').length>0)){
                var popup_style = '<style id="wdm-upgrade-popup-style">.wdm-upgrade-popup-description{margin-bottom: 5px}.wdm-upgrade-popup-ajax-loader{margin-bottom:0!important}.wdm-upgrade-popup-container{position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;background-color:#00000085;display:flex;justify-content:center;align-items:center;display:none;overflow:auto}.wdm-upgrade-ajax-loader{display:none}.wdm-upgrade-popup-container-content{width:45%;padding:30px;background-color:#fff;text-align:center;position:relative;max-width:100%;overflow-y:auto;overflow-x:hidden;max-height:500px}.wdm-upgrade-popup-close{background-color:#fff;position:absolute;top:0;right:0;cursor:pointer;width:35px;height:35px;display:flex;align-items:center;justify-content:center;border-radius:50%}.wdm-upgrade-popup-close .emoji{display:inline!important;border:none!important;box-shadow:none!important;height:1em!important;width:1em!important;margin:0 .07em!important;vertical-align:-.1em!important;background:0 0!important;padding:0!important}.wdm-upgrade-popup-input{margin:2% auto;padding:2%}.wdm-upgrade-popup-notice{display:block;padding:1%;color:red}button.wdm-upgrade-cta{padding:1% 2%;border-radius:5px;font-size:1em}@media only screen and (max-width: 600px){.wdm-upgrade-popup-container-content{width:80%}}</style>';
                jQuery('.wdm-upgrade-popup-container').first().append(popup_style);
            }
        });
        </script>
        <?php
    }

    /**
     * jQueryProcessing handles javascript processing of the popup
     *
     * @param  mixed $atts
     * @return void
     */
    public function jQueryProcessing($atts)
    {
        ?>
        <script id="wdm_upgrade_processing">
        jQuery(document).ready(function(){
            var upgrade_processing = 0;
            jQuery('.wdm-upgrade-cta').on('click',function(e){
                var notice = jQuery('#'+jQuery(this).data('notice'));
                notice.html('&nbsp;');
                var license = jQuery('#'+jQuery(this).data('input')).val();
                if(upgrade_processing==0){
                    if(license==''){
                        notice.html('Please enter a valid license.');
                        return false;
                    }
                    jQuery('.wdm-upgrade-ajax-loader').css('display','inline');
                    upgrade_processing=1;
                    var download = jQuery(this).data('download');
                    var option = jQuery(this).data('option');
                    var wdm_nonce = jQuery(this).data('nonce');
                    
                    
                    e.preventDefault();
                    formData = {action:'wdm_upgrade_popup_process',download:download,option:option,nonce:wdm_nonce,license:license};
                    jQuery.ajax({
                        type: "post",
                        dataType: "json",
                        url: "<?php echo admin_url('admin-ajax.php')?>",
                        data: formData,
                        success: function(data){
                            upgrade_processing=0;
                            jQuery('.wdm-upgrade-ajax-loader').css('display','none');
                            if(typeof data.checkout !== 'undefined'){
                                try {
                                    new URL(data.checkout);
                                } catch (_) {
                                    notice.html('Invalid upgrade!');
                                    return false;  
                                }
                                window.location.href = data.checkout;
                            }else if(typeof data.message !== 'undefined'){
                                notice.html(data.message);
                            }
                        }
                    });
                }
            });
            jQuery('.wdm-upgrade-popup-close').on('click',function(){
                jQuery('.wdm-upgrade-popup-container').css('display','none');
            });
            jQuery('<?php echo $atts['source_button_selector']?>').on('click',function(e){
                container = jQuery(this).data('id');
                if(typeof container!=='undefined' && jQuery('div[data-id='+container+']')!=='undefined'){
                    e.preventDefault();
                    jQuery('div[data-id='+container+']').css('display','flex');
                }
            });
        });
        </script>
        <?php
    }

    /**
     * getInstance to get object of the current class
     *
     * @return void
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new UpgradePopup;
        }
        return self::$instance;
    }
}
