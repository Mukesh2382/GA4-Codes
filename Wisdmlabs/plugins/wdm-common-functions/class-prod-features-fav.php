<?php
namespace WDMCommonFunctions;

/**
* Class to handle favourite product feature functionality
*/

class ProdFavFeature
{
    // To store current class object
    private static $instance;
    private $model;
    
    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        // Add fav feature jquery
        add_action('wp_enqueue_scripts', array($this,'wdmEnqueueProdFavScripts'));
        
        // Update Fav Feature for logged in users
        add_action('wp_ajax_wdm_prod_fav', array($this,'updateUserFav'));

        // Update Fav Feature for non logged in users
        add_action('wp_ajax_nopriv_wdm_prod_fav', array($this,'updateVisitorFav'));

        // After login map cookiw with logged in user
        add_action('wp_login', array($this,'relateLoggedInUser'), 10, 2);

        // Model to handle database operations
        $this->model = ProdFavFeatureModel::getInstance();
    }

    public function updateUserFav()
    {
        $nonce_name = 'wdm-prod-fav-' . explode('_', $_POST['feature_id'])[0];
        if (! check_ajax_referer($nonce_name, 'wdm_prod_fav', false)) {
            echo 1;
            die();
        } else {
            if (!empty($_POST['rm'])) {
                $args = array(
                            'user'          => get_current_user_id(),
                            'feature_id'    => $_POST['feature_id']
                            );
                if ($this->model->rmFeatureFav($args)) {
                    $this->model->updateFeatureFavCount(array('id'=>$_POST['feature_id'],'reduce'=>1));
                }
                echo 0;
            } else {
                $cookie_key = md5(uniqid(rand(), true)) . 'wdm-p-f-';
                $args = array(
                            'user'          => get_current_user_id(),
                            'feature_id'    => $_POST['feature_id']
                            );
                if ($this->model->addFeatureFav($args)) {
                    $this->model->updateFeatureFavCount(array('id'=>$_POST['feature_id']));
                }
                echo json_encode(array('cookie'=>$cookie_key.$_POST['feature_id']));
            }
        }
        die();
    }

    public function updateVisitorFav()
    {
        $nonce_name = 'wdm-prod-fav-' . explode('_', $_POST['feature_id'])[0];
        if (! check_ajax_referer($nonce_name, 'wdm_prod_fav', false)) {
            echo 1;
            die();
        } else {
            if (!empty($_POST['rm']) && !empty($_POST['user'])) {
                $args = array(
                            'user'          => $_POST['user'],
                            'feature_id'    => $_POST['feature_id']
                            );
                if ($this->model->rmFeatureFav($args)) {
                    $this->model->updateFeatureFavCount(array('id'=>$_POST['feature_id'],'visitor'=>1,'reduce'=>1));
                }
                echo 0;
            } else {
                $cookie_key = md5(uniqid(rand(), true)) . 'wdm-p-f-';
                // $cookie_key = 'wdm-p-f-';
                $args = array(
                            'user'          => $cookie_key.$_POST['feature_id'],
                            'feature_id'    => $_POST['feature_id']
                            );
                if ($this->model->addFeatureFav($args)) {
                    $this->model->updateFeatureFavCount(array('id'=>$_POST['feature_id'],'visitor'=>1));
                }
                echo json_encode(array('cookie'=>$cookie_key.$_POST['feature_id']));
            }
        }
        die();
    }

    // When user loggs in check if the user has marked any feature as
    // fav if yes then relate the user with the fav record
    public function relateLoggedInUser($user_login, $user)
    {
        unset($user_login);
        // loop on cookie with keys containing string and check if found extract
        // feature id and assign the current user id in place of the cookie
        foreach ($_COOKIE as $cookie_key => $cookie_value) {
            if (strpos($cookie_key, 'wdm-p-f-') !== false) {
                $explode_cookie = explode('-p-f-', $cookie_key);
                if (isset($explode_cookie[1]) && !empty($cookie_value)) {
                    // $feature_id = $explode_cookie[1];
                    $args = array(
                                    'where' => array('user'=>$cookie_key),
                                    'data'  => array('user'=> $user->ID)
                                    );
                    $this->model->updateFeatureFav($args);
                    setcookie($cookie_key, null, -1, '/');
                }
            }
        }
    }

    public function wdmEnqueueProdFavScripts()
    {
        global $template;
        if (basename($template)=='product-landing-template.php') {
            wp_enqueue_script('wdm-mark-fav', plugin_dir_url(__FILE__).'assets/js/wdm-mark-fav.js', true);
            wp_localize_script('wdm-mark-fav', 'wdm_js_obj_for_fav_prod', array('wdmuid'=>get_current_user_id(),'ajaxurl' => admin_url('admin-ajax.php')));
        }
    }


    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ProdFavFeature;
        }
        return self::$instance;
    }
}
