<?php
/**
 * LMS Addon Library
 * 
 * Integrating Elementor core.
 *
 * @package LMSAddon
 * @since 1.2.1
 */

use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Elementor registration for LMS Addon popup.
 *
 * @since 1.2.1
 */
class Elementor extends base {

	/**
	 * All Activated Plugins
	 *
	 * @since 1.2.0
	 */
	private $activated_plugins = [];
    
	public function __construct() {
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'enqueue_editor_scripts' ] );
		add_action( 'wp_ajax_lms_fetch_tmpl_data', [ $this, 'lms_fetch_tmpl_data']);
		add_action( 'wp_ajax_lms_import_related_tmpl_data', [ $this, 'lms_import_related_tmpl_data']);
		add_action( 'wp_ajax_lms_import_tmpl_data', [ $this, 'lms_import_tmpl_data']);
		add_action( 'wp_ajax_lms_preview_tmpl_data', [ $this, 'lms_preview_tmpl_data']);
	}
	
	
	/**
	 * Load styles and scripts for Elementor modal.
	 */
	public function enqueue_editor_scripts() {

		wp_enqueue_style( 'lmsaddons-elementor-modal', FTLMSA_PLUGIN_URL . 'library/assets/css/elementor-modal.css', [], FTLMSA_PLUGIN_VERSION );
		
		wp_register_script( 'lmsaddons-elementor-modal', FTLMSA_PLUGIN_URL . 'library/assets/js/elementor-modal.js', [ 'jquery' ], FTLMSA_PLUGIN_VERSION );
		
		wp_enqueue_script( 'lmsaddons-elementor-modal');

		$lmsaddons_libray = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( 'lmsaddons-elementor-modal', 'lmsaddons_libray', $lmsaddons_libray );

	}
	
	

	public function lms_fetch_tmpl_data() 
	{
		include 'lms-addon-modal.php';
		wp_die();
	}
	
	public function lms_preview_tmpl_data() 
	{
		$template_id = $_POST['template_id'];
		?>
				<div class="cta-section lmsa-templates-modal-body-mid cta-responsive">
					<button class="back lmsa-btn lmsa-btn-preview-back"><i class="fas fa-long-arrow-alt-left"></i>&nbsp;Back</button>
					<div class="responsive-controls"><i title="Desktop View" class="fas fa-laptop active"></i><i title="Tablet View" class="fas fa-tablet-alt "></i><i title="Mobile View" class="fas fa-mobile-alt "></i><i title="Fullscreen View" class="fas fa-expand"></i></div>
					<button class="back lmsa-btn lmsa-btn-preview-import" data-template_id="<?php echo $template_id; ?>" ><i class="far fa-arrow-alt-circle-down"></i>&nbsp;Import</button>
				</div>
				
				<div class="lmsa-templates-modal-body-main preview-section">
					<iframe src="" frameborder="0" allowfullscreen="" width="" style="width: 100%;background: #fff;"></iframe>
				</div>
		<?php
		wp_die();
	}
	
	public function lms_import_related_tmpl_data() 
	{
		$template_id = $_POST['template_id'];
		
		$library_tmpl = file_get_contents(FTLMSA_PLUGIN_URL.'library/templates/lmsaddon-library.json');
		$library_tmpl = json_decode($library_tmpl,true);
		
		$tmpl_data = $library_tmpl[$template_id];
		
		$tmpl = json_decode(file_get_contents(FTLMSA_PLUGIN_URL.'library/templates/blocks/'.$tmpl_data['related_file']),true);
		
		$content = $this->process_import_ids($tmpl);
		
		$content = $this->process_import_content($tmpl, 'on_import');
		
		print_r(\json_encode(array('html' => $content, 'template' => $tmpl_data)));
		wp_die();
	}
	public function lms_import_tmpl_data() 
	{
		$template_id = $_POST['template_id'];
		
		$library_tmpl = file_get_contents(FTLMSA_PLUGIN_URL.'library/templates/lmsaddon-library.json');
		$library_tmpl = json_decode($library_tmpl,true);
		
		$tmpl_data = $library_tmpl[$template_id];
		
		$tmpl = json_decode(file_get_contents(FTLMSA_PLUGIN_URL.'library/templates/blocks/'.$tmpl_data['filename']),true);
		
		$content = $this->process_import_ids($tmpl);
		
		//$content = $this->process_import_content($tmpl, 'on_import');
		
		print_r(\json_encode(array('html' => $content, 'template' => $tmpl)));
		wp_die();
	}
	protected function process_import_ids($content)
    {
        return \Elementor\Plugin::$instance->db->iterate_data($content, function ($element)
        {
            $element['id'] = \Elementor\Utils::generate_random_string();
            return $element;
        });
	}
	
	protected function process_import_content($content, $method)
    {
        return \Elementor\Plugin::$instance->db->iterate_data($content, function ($element_data) use ($method)
        {
            $element = \Elementor\Plugin::$instance->elements_manager->create_element_instance($element_data);

            if (!$element)
            {
                return null;
            }

            $r = $this->process_import_element($element, $method);
            
            return $r;
        });
	}
	
	protected function process_import_element($element, $method)
    {
        $element_data = $element->get_data();
        if (method_exists($element, $method))
        {
            $element_data = $element->{$method}($element_data);
        }
        foreach ($element->get_controls() as $control)
        {
            $control_class = \ELementor\Plugin::$instance
                ->controls_manager
                ->get_control($control['type']);
            if (!$control_class)
            {
                return $element_data;
            }
            if (method_exists($control_class, $method))
            {
                $element_data['settings'][$control['name']] = $control_class->{$method}($element->get_settings($control['name']) , $control);
            }
		}
        return $element_data;
    }
	
	
}

new Elementor();
