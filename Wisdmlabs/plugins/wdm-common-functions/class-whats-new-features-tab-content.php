<?php

/**
 * Manage Whats New Features Tab Content.
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists('Whats_New_Features_Tab_Content') ) {
    class Whats_New_Features_Tab_Content {
        private static $instance = NULL;

        /**
         * Constructor.
         */
        public function __construct() {
            // add_filter('rest_request_after_callbacks', array($this, 'change_acf_whats_new_features_data'), 10, 3);
        }

        /**
         * Return singleton instance of the class
         *
         * @return Whats_New_Features_Tab_Content Return the instance of the class
         */
        public static function get_instance() {
            if ( NULL == Whats_New_Features_Tab_Content::$instance ) {
                Whats_New_Features_Tab_Content::$instance = new Whats_New_Features_Tab_Content();
            }

            return Whats_New_Features_Tab_Content::$instance;
        }

        /**
         * Callback to filter 'rest_request_after_callbacks'.
         * Change the Whats New Features Tab Data in ACF REST API response.
         */
        public function change_acf_whats_new_features_data( $response, $hander, $request ) {
            if ( ! empty( $response->data['acf']['whats_new_features_data'] ) && ! empty( $request->get_params()['installed_version'] ) ) {
                $whats_new_features_data        = $response->data['acf']['whats_new_features_data'];
                $installed_version              = $request->get_params()['installed_version'];
                $whats_new_features_higher_data = array();
                        
                foreach ($whats_new_features_data as $value) {
                    if ( version_compare($value['version_number'], $installed_version, '>') ) {
                        $whats_new_features_higher_data[] = $value;
                    }
                }

                $response->data['acf']['whats_new_features_data'] = $whats_new_features_higher_data;
            }

            return $response;
        }
    }

    Whats_New_Features_Tab_Content::get_instance();
}

