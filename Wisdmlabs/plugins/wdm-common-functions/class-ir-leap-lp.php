<?php
namespace WDMCommonFunctions;
include_once get_stylesheet_directory() . "/components/services/heading.php";
include_once get_stylesheet_directory() . "/components/products/pricing-section.php";
/**
 * IrLeapLp to add pricing section on IR Leap LP
 */
class IrLeapLp
{
    private static $instance;
    public $slug;
    public $plans_data;
    public $rows_data;
    public $json_path;

    /**
     * __construct includes hook calls on ajax processes to process upgrade requests
     *
     * @return void
     */
    private function __construct()
    {
        $this->slug = '';
        $this->plans_data = $this->rows_data = array();
        $this->json_path = wp_upload_dir()['basedir'] . '/page-content/products/{{slug}}/json/';
        add_shortcode( 'ir_leap_pricing_section', array( $this, 'ir_leap_pricing_section' ) );
        add_action('save_post', array( $this, 'wdm_create_json_for_product_landing' ), 999);
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
    }

    public function ir_leap_pricing_section( $atts ){
        
        $atts = shortcode_atts( array(
            'slug' => ''
        ), $atts, 'ir_leap_pricing_section' );
        if(!empty($atts['slug'])){
            $this->slug = $atts['slug'];
        }
        if(!empty($this->slug)){
            $this->set_json_data();
        }
    }
    

    public function set_json_data(){
  
        $plans_path = str_ireplace('{{slug}}', $this->slug, $this->json_path . 'pricing_table_plans.json');
        $rows_path = str_ireplace('{{slug}}', $this->slug, $this->json_path . 'pricing_table_rows.json');
        
        $this->plans_data = json_decode( file_get_contents($plans_path), true );
        $this->rows_data = json_decode( file_get_contents($rows_path), true );

        
        if( !empty($this->plans_data) && !empty($this->rows_data) ){
        ?>
        <section id="pricing" class="s9 s8-m">
            <?php ui_pricing_section($this->plans_data, $this->rows_data); ?>
            <div class="pricing-section-placeholder"></div>
        </section>
        <?php
        }
    }

    // ---------------------------- To save product landing page related data into json files ----------------
    public function wdm_create_json_for_product_landing($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }
        if (empty($_POST['acf'])) {
            return;
        }
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // Footer CTA Section Fields are in Older Landing Pages
        // $footer_cta_field = get_post_meta($post_id, 'footer_cta_footer_cta_title', true);
        // $all_meta = get_post_meta($post_id, '', true);

        // if (get_post_status($post_id) == 'publish') {
        //     $folder_path = wp_upload_dir()['basedir'] . '/page-content/products/' . get_post_field('post_name', $post_id) . '/json';
        // } else {
        //     $folder_path = wp_upload_dir()['basedir'] . '/page-content/products/' . $post_id . '/json';
        // }
        // if (!empty($footer_cta_field)) {
        //     // Old Landing Pages ACF Form
        //     wdmProductLandingPagesJsonProcess($folder_path, $all_meta, 1);
        // } else {
        //     wdmProductLandingPagesJsonProcess($folder_path, $all_meta);
        // }
    }

    public function wp_enqueue_scripts(){
        $styleUri = get_stylesheet_directory_uri();
        
        // $src = '/css/' . $requested_page . '/style.css';
        // $version = plt_get_version($src);

        // wp_enqueue_style('plt-css', $styleUri . $src, array(), $version);
        $src = '/js/swiper.js';
        // $version = plt_get_version($src);

        wp_enqueue_script('ir-swiper', get_stylesheet_directory_uri() . $src, array('jquery'));

        wp_enqueue_script( 'ir_leap_lp', plugins_url('assets/js/wdm-ir-leap-lp.js', __FILE__), array('jquery','ir-swiper'), CHILD_THEME_VERSION );
        wp_enqueue_style( 'ir_leap_lp', plugins_url('assets/css/wdm-ir-leap-lp.css', __FILE__), array(), CHILD_THEME_VERSION );
    }
    

    /**
     * getInstance to get object of the current class
     *
     * @return void
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new IrLeapLp;
        }
        return self::$instance;
    }
}
