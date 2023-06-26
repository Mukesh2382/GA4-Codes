<?php
namespace WDMCommonFunctions;

/**
 * DowngradeIR to show text and link to download older version of IR
 */
class EddSubCancelFeedback
{
    private static $instance;
    public $feedback_form;
    /**
     * __construct includes hook calls on ajax processes to process upgrade requests
     *
     * @return void
     */
    private function __construct()
    {
        if(!empty(get_field('subscription_cancellation_form','option'))){
            $this->feedback_form = get_field('subscription_cancellation_form','option');
        }
        add_action('edd_profile_editor_after',array($this,'edd_profile_editor_after'));
        // add_shortcode('wdm_downgrade_ir', array($this,'wdm_downgrade_ir'));
        add_action('wp_enqueue_scripts', array($this,'wdm_enqueue_js'),99);
    }

    public function wdm_enqueue_js(){
        global $post;
        $page_slug = $post->post_name;
        if('my-account'==$page_slug){
            global $wp_scripts;
            $wp_scripts->registered['edd-frontend-recurring']->src = plugins_url('assets/js/edd-frontend-recurring.js', __FILE__);
            wp_enqueue_script('wdm-edd-sub-can-feedback', plugins_url('assets/js/wdm-edd-unsub-fb.js', __FILE__), array('jquery'), '1.0.0');
            wp_localize_script('wdm-edd-sub-can-feedback', 'wdm_ajax_object', array( 'wdm_sub_url' => admin_url('edit.php?post_type=download&page=edd-subscriptions&id='), 'ajax_url' => admin_url('admin-ajax.php'), 'fb_form' => $this->feedback_form ));
            wp_enqueue_style('wdm-edd-sub-can-feedback', plugins_url('assets/css/wdm-edd-unsub-fb.css', __FILE__), false, CHILD_THEME_VERSION);
        }
    }

    public function edd_profile_editor_after(){
        $this->generate_popup();
    }

    public function generate_popup(){
        ?>
        <div id="unsub-feedback-popup-container" class="unsub-feedback-popup-container" style="display: none;">
            <div class="unsub-feedback-content-wrap unsub-feedback-content-wrap-with-linkcta">
                <span class="unsub-feedback-close">
                    <img draggable="false" role="img" class="emoji" alt="✖" src="<?php echo plugin_dir_url( __FILE__ )?>assets/images/2716.svg" data-lazy-load="false" data-pagespeed-url-hash="1859759222" onload="pagespeed.CriticalImages.checkImageForCriticality(this);">
                </span>
                <div class="popup_content">
                    <?php echo do_shortcode( '[contact-form-7 id="'.$this->feedback_form.'" title="Subscription Cancellation Feedback"]' )?>
                </div>
            </div>
        </div>
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
            self::$instance = new EddSubCancelFeedback;
        }
        return self::$instance;
    }
}
