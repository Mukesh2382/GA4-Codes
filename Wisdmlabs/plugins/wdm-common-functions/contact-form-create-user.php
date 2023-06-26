<?php
namespace WDMCommonFunctions;

// require_once WDMAUTICPATH . '/lib/Mautic/Auth/OAuth.php';
// Client ID: 14_1xz55kn5tsxwokcg4s0cc4k0kkwwgkgcgwcwggosc80wkcc88w
// Client Secret: 4fba72fdc7msog40wskok8g88o0wg0kcogc8kwwkk4o0cg4osg
// use Mautic\Auth\ApiAuth;
/**
* Class to add settings page, settings field and handle auth token
*/

class Cf7CreateUser
{
    // To store current class object
    private static $instance;
    private $api_endpoint;
    private $key;

    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        $this->api_endpoint = "http://content.wisdmlabs.net/wp-json/WDMCreateUser/v1/createusers";
        $this->key = '6KfUpFBrtsXRw9a7BIxYZnDd1';

        add_action('wpcf7_mail_sent', array( $this, 'setCron' ));
        // add_action('wp_login', array($this,'setMauticLoginCron'), 10, 2);
        add_action('wdm_cf7_form_submit_create_user', array($this,'processCron'), 10, 3);
    }

    // Set cron to submit login form on mautic
    public function setCron($contact_form)
    {
        if ($contact_form->id() == 163222) {
            $your_subject = strtolower(filter_input(INPUT_POST, 'your-subject'));
            if ($your_subject == 'i want wisdm app demo') {
                $name  = filter_input(INPUT_POST, 'your-name');
                $email  = filter_input(INPUT_POST, 'your-email');
                $data = array("Name"=>$name,"Email"=>$email,"Key"=>$this->key);
                wp_schedule_single_event(time() + 0, 'wdm_cf7_form_submit_create_user', array($data));
            }
        }
    }

    public function processCron($data)
    {
        $this->makeCurl($this->api_endpoint, $data);
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Cf7CreateUser;
        }
        return self::$instance;
    }

    public function makeCurl($url, $data)
    {
        if (empty($url) || empty($data)) {
            return;
        }
        $curl_hook = curl_init();
        $agents = array(
                        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
                        'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1.9) Gecko/20100508 SeaMonkey/2.0.4',
                        'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)',
                        'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1'
                     
                    );
        curl_setopt($curl_hook, CURLOPT_USERAGENT, $agents[array_rand($agents)]);
        curl_setopt($curl_hook, CURLOPT_URL, $url);
        foreach ($data as $key => $value) {
            $header[] = "$key: $value";
        }
        // error_log(json_encode($header));
        curl_setopt($curl_hook, CURLOPT_HTTPHEADER, $header);
        // curl_setopt($curl_hook, CURLOPT_HEADER, true);
        curl_setopt($curl_hook, CURLOPT_RETURNTRANSFER, 1);
        // error_log(json_encode($curl_hook));
        $response = curl_exec($curl_hook);
        error_log($response);
        curl_close($curl_hook);
    }
}
