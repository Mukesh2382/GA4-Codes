<?php
namespace WDMCommonFunctions {

    /**
    * Class to handle ___
    */
    class MyPopupHandler
    {
        /**
         * Instance of this class.
         *
         * @since    1.0.0
         *
         * @lvar object
         */
        protected static $instance = null;

        public function __construct()
        {
            //For displaying popup HTML.
            add_action('wp_footer', array($this,'showPopupHTML'));
            //Enqueue JS and CSS of popup
            add_action('wp_enqueue_scripts', array($this,'enqueuePopUpScripts'));
        }

        /**
         * Returns an instance of this class.
         *
         * @since     1.0.0
         *
         * @return object A single instance of this class.
         */
        public static function getInstance()
        {
            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
        * Displaying popup HTML.
        */
        public function showPopupHTML()
        {
            ob_start();
            include __DIR__.'/assets/templates/popup_master_popup_html.php';
            echo ob_get_clean();
        }

        public function enqueuePopUpScripts()
        {
            if (!is_page()) {
                return;
            }
            //Enqueuing 'popup.css' file
            $css_url =  plugin_dir_url(__FILE__).'assets/css/popup.css';
            $css_path =  plugin_dir_path(__FILE__).'assets/css/popup.css';

            wp_enqueue_style('popup_css', $css_url, array(), filemtime($css_path));
        }
    }
    MyPopupHandler::getInstance();
}
