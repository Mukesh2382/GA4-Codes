<?php
namespace WDMCommonFunctions;

/**
* Class to handle favourite product feature functionality
*/

class ProdFavFeatureModel
{
    // To store current class object
    private static $instance;
    private $feature_table;
    private $feature_table_fav;
    
    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        $this->feature_table = 'wp_prods_features';
        $this->feature_table_fav = 'wp_prods_features_fav';
    }

    // remove fav feature
    public function rmFeatureFav($params = array())
    {
        global $wpdb;
        if (!empty($params)) {
            return $wpdb->delete($this->feature_table_fav, $params);
        }
    }

    // add fav feature
    public function addFeatureFav($params = array())
    {
        global $wpdb;
        if (!empty($params)) {
            $wpdb->suppress_errors(true);
            $res = $wpdb->insert($this->feature_table_fav, $params);
            $wpdb->suppress_errors(false);
            return $res;
        }
    }

    // update fav feature
    public function updateFeatureFav($params = array())
    {
        global $wpdb;
        if (!empty($params['where']) && !empty($params['data'])) {
            $wpdb->update($this->feature_table_fav, $params['data'], $params['where']);
        }
    }

    // update fav feauture count
    public function updateFeatureFavCount($params = array())
    {
        global $wpdb;
        $action='+1';
        if (!empty($params['visitor'])) {
            $column = 'total_visitors_fav';
        } else {
            $column = 'total_signed_in_users_fav';
        }
        if (!empty($params['reduce'])) {
            $action='-1';
        }
        if (!empty($params['id'])) {
            $wpdb->query($wpdb->prepare("
                                    UPDATE wp_prods_features
                                    SET ".$column."=".$column.$action."
                                    WHERE id = %s
                                    ", $params['id']));
        }
    }
    // remove feature
    public function rmFeature($params = array())
    {
        global $wpdb;
        if (!empty($params)) {
            $wpdb->delete($this->feature_table, $params);
        }
    }

    // add single feature
    public function addFeature($params = array())
    {
        global $wpdb;
        if (!empty($params)) {
            $wpdb->insert($this->feature_table, $params);
        }
    }

    // add multiple features
    public function addFeatures($params = array())
    {
        global $wpdb;
        $values = array();
        $place_holders = array();
        $query = "INSERT IGNORE INTO $this->feature_table (id, total_signed_in_users_fav, total_visitors_fav) VALUES ";
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (!empty($value['id'])) {
                    array_push($values, $value['id'], 0, 0);
                    $place_holders[] = "('%s', %d, %d)";
                }
            }
            $query .= implode(', ', $place_holders);
            $wpdb->query($wpdb->prepare("$query ", $values));
        }
    }

    // update feature
    public function updateFeature($params = array())
    {
        global $wpdb;
        if (!empty($params['where']) && !empty($params['data'])) {
            $wpdb->update($this->feature_table, $params['data'], $params['where']);
        }
    }

    public function getUsersFavFeatures($user_id = 0)
    {
        global $wpdb;
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if ($user_id) {
            $results = $wpdb->get_results("SELECT feature_id FROM $this->feature_table_fav WHERE user='$user_id'", ARRAY_A);
            return $results;
        }
        return null;
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ProdFavFeatureModel;
        }
        return self::$instance;
    }
    // Set A TRIGGER which will make sure that the fav count is greater than 0
    // DELIMITER $$
    // CREATE TRIGGER check_fav_count_validity AFTER UPDATE ON wp_prods_features FOR EACH ROW
    // BEGIN
    //     IF NEW.total_signed_in_users_fav < 0 THEN
    //         UPDATE wp_prods_features SET total_signed_in_users_fav=0 WHERE id = new.id;
    //     END IF;
    //     IF NEW.total_visitors_fav < 0 THEN
    //         UPDATE wp_prods_features SET total_visitors_fav=0 WHERE id = new.id;
    //     END IF;
    // END; $$
}
