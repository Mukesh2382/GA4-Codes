<?php
namespace Elementor;
class Wisdm_Plugin_Stats_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-plugin-stats-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Plugin Stats', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script'
        ];
    }

    public function get_icon() {
        return 'eicon-kit-details';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Plugin Compatibility', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
			'show_version',
			[
				'label' => __( 'Show Version', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                'return_value' => 'yes',
                'default' => 'yes',
			]
		);

        $this->add_control(
			'version',
			[
				'label' => __( 'Version', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '3.1.1',
                'label_block' => true,
                'condition' => [
                    'show_version' => 'yes'
                ]
			]
		);

        $this->add_control(
			'default_view',
			[
				'label' => __( 'Hide On Mobile', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Hide', 'your-plugin' ),
                'label_off' => __( 'Show', 'your-plugin' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'label_block' => true,
			]
		);

        $this->add_control(
			'changelog_link',
			[
				'label' => __( 'Changelog Link', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
                'label_block' => true,
			]
		);
        
        $faq_repeater = new \Elementor\Repeater();
		$faq_repeater->add_control(
			'plugin_name', [
				'label' => __( 'Name', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Moodle' , 'wisdm-elementor-widgets' ),
				'label_block' => true,
			]
		);
        $faq_repeater->add_control(
			'plugin_version', [
				'label' => __( 'Version', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::TEXT ,
				'default' => __( '3.1.1' , 'wisdm-elementor-widgets' ),
				'label_block' => true,
			]
		);
        $this->add_control(
			'plugins',
			[
				'label' => __( 'Plugins', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $faq_repeater->get_controls(),
				'default' => [
					[
						'plugin_name' => __( 'Moodle', 'wisdm-elementor-widgets' ),
						'plugin_version' => __( '3.1.1', 'wisdm-elementor-widgets' ),
					],
					[
						'plugin_name' => __( 'PHP', 'wisdm-elementor-widgets' ),
						'plugin_version' => __( '7.4', 'wisdm-elementor-widgets' ),
					],
				],
				'title_field' => '{{{ plugin_name }}} : {{{ plugin_version }}}',
			]
		);
        $this->add_control(
			'heading', [
				'label' => __( 'Heading', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Compatibility' , 'wisdm-elementor-widgets' ),
				'label_block' => true,
			]
		);

        $this->add_control(
			'release_date',
			[
				'label' => __( 'Release Date', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::DATE_TIME,
                'picker_options' => [
                    'defaultDate' => '2021-05-06',
                    'enableTime' => false
                ]
			]
		);
        $this->end_controls_section();
    }


    private function style_tab() {}

    protected function render() {
            $settings = $this->get_settings_for_display();
            $plugins = $settings['plugins']; 
            $changelog_link = $settings['changelog_link']; 
            $version = $settings['version']; 
            $show_version = $settings['show_version'] == 'yes'; 
            $hide_on_mobile = $settings['default_view']; 
            $default_view = " is-style-mb-view "; 
            if($hide_on_mobile == 'yes'){
                $default_view .= " is-style-ds-view ";
            }
            $heading = $settings['heading']; 
            $release_date = $settings['release_date']; 
        ?>
        
        <div class="wisdm-elementor-pluginstats <?php echo $default_view;?>">
            <h2 class="blk-heading"><?php echo $heading; ?></h2>
            <div class="ps-wrap">
                <div class="pl-list">
                    <?PHP if($show_version) { ?>
                    <div class="pl-version">
                        <h3 class="th3">Version</h3>
                        <p class="p-ver rel-dt ds"><?php echo $version; ?></h2></p>
                    </div>
                    <?php } ?>
                    <?php foreach($plugins as $plugin){
                        $plugin_name = $plugin['plugin_name'];
                        $plugin_version = $plugin['plugin_version'];
                        ?>
                        <div class="pl-details" 
                            data-name="<?php echo $plugin_name;?>" 
                            data-version=" <?php echo $plugin_version;?> ">
                            <div>
                                <h3 class="th3"> <?php echo $plugin_name;?> </h3>
                            </div>
                            <p class="ds"> <?php echo $plugin_version;?></p>
                        </div>
                        <?php
                    }?>
                </div>
                <div class="ch-log">
                    <div class="up-stat">
                        <h3 class="th3 wisdm-ps-rel-date" data-rel-dt="<?php echo $release_date;?>T05:30:00">Last Updated</h3>
                        <p class="rel-dt ds wisdm-ps-relase_day"></p>
                    </div>
                    <?php if($changelog_link) { ?>
                        <a class="chlg-url" href="<?php echo $changelog_link; ?>">View Changelog</a>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
    }

    protected function _content_template() {

    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Plugin_Stats_Widget() );
