<?php
/**
 * Add login form and social login form details to mautic
 *
 * @package   Wdm_Shortcode_To_Mautic
 * @author    WisdmLabs <support@wisdmlabs.com>
 * @license   GPL-2.0+
 * @link      http://wisdmlabs.com
 * @copyright 2019 WisdmLabs
 */
if (! class_exists('Wdm_Login_Form_To_Mautic')) {
    class Wdm_Login_Form_To_Mautic
    {
        private $form_data;
        private $mautic_login_form;
        private $mautic_social_login_form;
        private $formUrl;
        public function __construct()
        {
            // On login set cookie for non admin users
            add_action('wp_login', array($this,'setMauticLoginCookie'), 10, 2);

            // Handle mautic form submit ajax call
            add_action('wp_ajax_login_form_submit', array($this,'loginFormSubmit'));

            // Insert script to make ajax call
            add_action('wp_footer', array($this,'setMauticScript'));

            // Mautic setup url
            $this->formUrl =  'https://wdmmautic.wisdmlabs.com/form/submit?formId=';

            // Mautic form created for checkout
            $this->mautic_login_form = 19;

            // Site's login form
            /*$this->form_data[$this->mautic_login_form] = array(
                                    'emailusername'    => 'email',
                                    'username'     => 'username',
                                    'formId'        => 'mformid'
                                    );*/
        }

        public function setMauticScript()
        {
            if (isset($_SESSION['mautic_c'])) {
                ?>
                <script>
                    jQuery(document).ready(function(){
                        var data = {
                            'action': 'login_form_submit',
                            'cookie': <?php echo stripslashes($_SESSION['mautic_c']); ?>
                        };
                        jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(response) {
                            //document.cookie = 'mautic_c=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                        });
                    });
                </script>
                <?php
            }
        }

        // On login set cookie for non admin users callback method
        /**
        * @param string $username Username
        * @param object $user_obj WP_User object
        */
        public function setMauticLoginCookie($username, $user_obj)
        {
            if (!$user_obj->has_cap('manage_options')) {
                $mautic_cookie = array($user_obj->user_email,$username);
                // setcookie('mautic_c', json_encode($mautic_cookie), 1 * DAYS_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
                $_SESSION['mautic_c'] = json_encode($mautic_cookie);
            }
        }
      
        /**
         * Add login details from login form to Mautic form.
         *  Ajax call callback method
         */
        public function loginFormSubmit()
        {
            // Check if theme my login form is filled and user is not an admin
            if (!empty($_POST['cookie']) && !empty($_SESSION['mautic_c'])) {
                // $formid	 = filter_input(INPUT_POST, 'mformid');
                $formid = $this->mautic_login_form;
                $mautic_data = array( 'formId'=>$formid, 'emailusername'=>$_POST['cookie'][0], 'username'=> $_POST['cookie'][1] );
                // $mautic_data = $this->getMauticFormFields(array('formid'=>$formid));
                if ($mautic_data) {
                    $ipaddress = getRemoteIpAddress();
                    // Data to send
                    $data = array('mauticform' => $mautic_data);
                    // make curl request to insert the data
                    makeCurl($this->formUrl.$formid, $data, $ipaddress);
                }
            }
            unset($_SESSION['mautic_c']);
            die;
        }
    }
}
