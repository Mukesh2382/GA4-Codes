<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WisdmEW_Edd {
    
	// To store current class object
    private static $instance;

    private function __construct(){
        
    }

    public static function getUpgrades($id){
        $upgrade_paths = edd_sl_get_upgrade_paths($id);
        $result = [];
        if(!empty($upgrade_paths)){
            foreach( $upgrade_paths as $index => $path ){
                $download_id = $path['download_id'];
                $price_id = $path['price_id'];
                $key =  $download_id;
                if(!empty($price_id)){
                    $key .= "_". $price_id;
                }
                $path['upgrade_id'] = $index;
                $result[$key] = $path;
            }
        }
        return $result;
    }

    public static function get_downloads(){
        $args = array(
            'post_type' => 'download',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        $query = new \WP_Query($args);
        $downloads =  $query->get_posts();
        $eddDownloadPrices = [];
        foreach ($downloads as $download) {
            $eddDownloadPrices[$download->ID] = $download->post_title; 
        }
        return $eddDownloadPrices;
    }

    public static function get_downloads_with_variables(){
        $args = array(
            'post_type' => 'download',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        $query = new \WP_Query($args);
        $downloads =  $query->get_posts();
        $eddDownloadPrices = [];
        foreach ($downloads as $download) {
            $prices = [];
            if(edd_has_variable_prices($download->ID)){
                $prices = edd_get_variable_prices($download->ID);
            }
            if(!empty($prices)){
                foreach ($prices as  $price_id => $price) {
                    $pricename = $price['name'];
                    $option_index =  $download->ID ."_". $price_id;
                    $option_value =  $download->post_title ." - ". $pricename;
                    $eddDownloadPrices[$option_index] = $option_value . " " . $option_index;; 
                }
            }
            else{
                $option_index =  $download->ID ;
                $option_value =  $download->post_title;
                $eddDownloadPrices[$option_index] = $option_value . " " . $option_index;; 
            }
        }
        return $eddDownloadPrices;
    }

    public static function already_discount_applied(){
        
    }

    public static function auto_disc_code_generation(){
        $already_discount_applied = \WisdmEW_Edd::already_discount_applied();
        if(!$already_discount_applied){
            $asdasd = Array
            (
                "name" => "testing disc code",
                "code" => "code1232456",
                "type" => "percent",
                "amount" => "25",
                "products" => Array
                    (
                        "0" => "0",
                        "1" => "251876",
                        "2" => "76021",
                        "3" => "86327",
                    ),
                "product_condition" => "any",
                "not_global" => "1",
                "start" => "04/01/2021",
                "expiration" => "07/31/2021",
                "min_price" => "",
                "max" => "",
                "auto_apply_title" => "",
                "use_once" => "1",
                "user_name" => "",
                "edd-action" => "add_discount",
                "edd-redirect" => admin_url( 'edit.php?post_type=download&page=edd-discounts' ),
                "edd-discount-nonce" => wp_create_nonce( 'edd_discount_nonce' )
            );
        } 
        
    }

    // To get object of the current class
    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new WisdmEW_Edd;
        }
        return self::$instance;
    }


}

WisdmEW_Edd::getInstance();
