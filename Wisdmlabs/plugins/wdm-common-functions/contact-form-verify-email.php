<?php
/**
* Class to verify email before adding into our system through contact form 7 submission
*/ 

class Cf7VerifyEmail {
    // To store current class object
    private static $instance;
    // private $api_endpoint;
    // private $key;

    // To add expensive codes and to prevent direct object instantiation
    private function __construct(){
        // include_once("VerifyEmail.php");
        add_filter('wpcf7_validate_email*', array($this,'verifyEmail'), 20, 2);
    }

    // Set cron to submit login form on mautic
    public function verifyEmail($result, $tag){
        if ( isset($_POST['_wpcf7']) && $_POST['_wpcf7'] != 3158 ) // Only form id 3158 will be validated.
            return $result;
        $tag = new WPCF7_Shortcode($tag);
        if ('your-email' == $tag->name) {
            $your_email = isset($_POST['your-email']) ? trim($_POST['your-email']) : '';
            // $your_email_confirm = isset($_POST['your-email-confirm']) ? trim($_POST['your-email-confirm']) : '';
            $postData = array(
                "user-id" => "wisdmlabs",
                "api-key" => "BsqmCOvSIOYbVRCtG6Dn9k2l5Qu6ZbW2PNhS0a7n0PKRzIt4",
                "email" => $your_email,
                // "fix-typos" => true
            );
            // $json = $this->curl_post_request("https://neutrinoapi.com/email-verify", $postData);
            $json = $this->curl_post_request("https://neutrinoapi.com/email-validate", $postData);

            $json_result = json_decode($json,true);

            // error_log( $json );
            // if( empty($json_result['verified']) ){
            if( isset($json_result['valid']) ){
                if( $json_result['valid'] == false ){
                    $wdm_invalid_email_attempts = get_option( 'wdm_invalid_email_attempts', 0 );
                    update_option('wdm_invalid_email_attempts',$wdm_invalid_email_attempts+1);
                    $result->invalidate($tag, "Are you sure this is the correct address?");
                }
                // error_log( "Are you sure this is the correct address?" );
            }
        }
        // $result->invalidate($tag, "Are you sure this is the correct address?");
        return $result;
    }

    public function curl_post_request($url, $data) 
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); //timeout in seconds
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        // error_log($content);
        curl_close($ch);
        return $content;
    }
     // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Cf7VerifyEmail;
        }
        return self::$instance;
    }

}
