<?php
class Unsub_EDD_Rest_Server extends WP_REST_Controller {
	private $api_namespace;
	private $base;
	private $api_version;
	private $required_capability;
	
	public function __construct() {
		$this->api_namespace = 'wdm_unsub_edd/v';
		$this->base = 'email';
		$this->api_version = '1';
		// $this->required_capability = 'read';  // Minimum capability to use the endpoint
		$this->init();
	}
	
	
	public function register_routes() {
		// die('Testing');
		register_rest_route( $this->api_namespace . $this->api_version , '/' . $this->base . '/(?P<email_id>[\S]+)', 
				array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array( $this, 'unsubEddFollowupEmail')
				)
			);
	}
	// Register our REST Server
	public function init(){
		// add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	
	
	public function unsubEddFollowupEmail( WP_REST_Request $request ){
		$creds = array();
		$headers = getallheaders();
		$email = $request->get_param( 'email_id' );
		// Get username and password from the submitted headers.
		if ( array_key_exists( 'Username', $headers ) && array_key_exists( 'Password', $headers ) ) {
			$creds['user_login'] = $headers["username"];
			$creds['user_password'] =  $headers["password"];

			if( $headers["Username"] == 'wdm9854564545' && $headers["Password"] == 'sjdfhj374ys444454' ){
				$email = base64_decode(urldecode($email));
				$customer_class_path = ABSPATH . 'wp-content/plugins/easy-digital-downloads/includes/class-edd-customer.php';
				if( file_exists($customer_class_path) && defined('EDD_VERSION') ){
				    $crons  = _get_cron_array();
				    if ( empty( $crons ) ) {
				        return 'ok';
				    }
				    require_once( $customer_class_path );
				    // $email = 'fake.tariq@gmail.com';
				    $customer = new EDD_Customer($email);
				    if( $customer ){
				        $payment_ids = $customer->get_payment_ids();
				        if( $payment_ids ){
				            foreach ( $crons as $time => $cron ) {
				                foreach ( $cron as $hook => $dings ) {
				                    foreach ( $dings as $sig => $data ) {
				                        if( $hook == 'eddfue_send_email_to_users' ){
				                            if( !empty($data['args'][0]) && in_array($data['args'][0], $payment_ids) ){
				                                wp_clear_scheduled_hook( $hook, $data['args'] );
				                            }
				                        }

				                    }
				                }
				            }
					    }
				    }
				}
			}else{
				return new WP_Error( 'rest_forbidden', 'You do not have permissions to view this data.', array( 'status' => 401 ) );
			}

			return 'ok';
		}
		else {
			return new WP_Error( 'invalid-method', 'You must specify a valid username and password.', array( 'status' => 400 /* Bad Request */ ) );
		}
	}
}
 
$unsub_rest_server = new Unsub_EDD_Rest_Server();