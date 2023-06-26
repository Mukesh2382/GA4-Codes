<?php
namespace Elementor;
class Edd_upgrades extends Widget_Base {

    public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);
        $this->edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode() ;    
	}

    public function get_name() {
        return  'wisdm-edd-upgrades-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Edd Upgrades', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [ 'edd-upgrades-script' ];
    }

    public function get_style_depends() {
        return [ 'edd-upgrades-style'];
    }

    public function get_icon() {
        return 'eicon-upload-circle-o';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {

        $downloads = \WisdmEW_Edd::get_downloads_with_variables();

        $this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Timer Settings', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
            $repeater = new \Elementor\Repeater();
            $repeater->add_control(
                'download',
                [
                    'label' => __( 'Select Download', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $downloads,
                    'label_block' => true
                ]
            );
            $repeater->add_control(
                'upgrade',
                [
                    'label' => __( 'Select Upgrade', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $downloads,
                    'label_block' => true
                ]
            );
            $this->add_control(
                "download_upgrades",
                [
                    'label' => __( 'Upgrades', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $repeater->get_controls(),
                    'default' => [],
                    'title_field' => "{{{ download }}} - {{{ upgrade }}}",
                ]
            );

            $this->add_control(
                "button_title",
                [
                    'label' => __( 'Upgrade Button Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'label_block' => true,
                    'default' => 'Upgrade License',
                ]
            );
        $this->end_controls_section();

        $this->style_tab();
    }

    private function style_tab() {
       
    }

    protected function render() {
        global $edd_receipt_args;

        $settings = $this->get_settings_for_display();
        $button_title = $settings['button_title'];

        // check upgrades mapping
        $download_upgrades = $settings['download_upgrades'];
        $upgrades_result = $this->checkUpgrades($download_upgrades);
        $upgrades = $upgrades_result['upgrades'];
        $errors = $upgrades_result['errors'];
        
        if ( $this->edit_mode ) {
            $this->upgrade_btn($button_title,"#");
            foreach($errors as $error){
                ?>
                <p style='color:red'><?php echo $error;?></p>
                <?php
            }
        }
        else{
            $payment  = get_post($edd_receipt_args['id']);
            $payment_id = $payment->ID;
            $edd_sl = edd_software_licensing();
            if( edd_is_payment_complete( $payment_id ) && $edd_sl->get_licenses_of_purchase( $payment_id ) ) {
    
                $meta   = edd_get_payment_meta($payment->ID);
                $payment_downloads = $meta['downloads'];
    
                if(!empty($payment_downloads)){
                    $keys   = $edd_sl->get_licenses_of_purchase( $payment_id );
                    $keys   = apply_filters( 'edd_sl_manage_template_payment_licenses', $keys, $payment_id );
    
                    foreach ( $keys as $license ){
                        $license_id = $license->ID;
                        $download_id = $edd_sl->get_download_id( $license_id );
                        $price_id    = $edd_sl->get_price_id( $license_id );
                        $download_price_id = $download_id;
                        if(!empty($price_id) ){
                            $download_price_id .= "_". $price_id;
                        }
                        if(isset($upgrades[$download_price_id])){
                            $upgrade_data =  $upgrades[$download_price_id];
                            ?>
                            <div class="wdm-elementor-edd-upgrades" style='text-align:center' >
                            <?php
                                foreach($upgrade_data as $upgrade_download_id => $upgrade_id){
                                    $link = esc_url( edd_sl_get_license_upgrade_url( $license_id, $upgrade_id ) ); 
                                    $this->upgrade_btn($button_title,$link);
                                }
                            ?>
                            </div>
                            <?php
                        }
                    }
                }
            }
        }
        ?>
        </div>
        <?php
    }

    function checkUpgrades($download_upgrades){
        $upgrades = [];
        $errors = [];
        foreach ($download_upgrades as $index => $array) {
            $download = $array['download'];
            $download_id = explode("_",$download)[0]; 
            $upgrade_paths = \WisdmEW_Edd::getUpgrades( $download_id );
            $upgrade = $array['upgrade'];

            if(!empty($download) && !empty($upgrade)){
                if(isset($upgrade_paths[$upgrade])){
                    if(!isset($upgrades[$download])){
                        $upgrades[$download] = [];
                    }
                    $upgrade_id = $upgrade_paths[$upgrade]['upgrade_id'];
                    $upgrades[$download][$upgrade] = $upgrade_id;
                }
                else{
                    $errors[] = "Invalid mapping of upgrade path. Check entry no : " . ($index +1);
                }
            }
            else{
                $errors[] = "Both fields are required. Check entry no : " . ($index +1);
            }
        }
        return [
            'upgrades' => $upgrades,
            'errors' => $errors
        ];
    }

    protected function upgrade_btn($button_title,$link){
        ?>
        <a 
            class="button" 
            href="<?php echo $link; ?>" 
            title="<?php esc_attr( $button_title ); ?>">
                <?php echo $button_title; ?>
        </a>
        <?php
    }
    protected function _content_template() {}
}

Plugin::instance()->widgets_manager->register_widget_type( new Edd_upgrades() );