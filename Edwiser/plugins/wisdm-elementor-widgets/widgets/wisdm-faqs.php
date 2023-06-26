<?php
namespace Elementor;
class Wisdm_Faqs_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-faqs-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Faqs', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script'
        ];
    }

    public function get_icon() {
        return 'eicon-toggle';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Faqs', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
        $faq_repeater = new \Elementor\Repeater();
		$faq_repeater->add_control(
			'faq_title', [
				'label' => __( 'Title', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Title' , 'wisdm-elementor-widgets' ),
				'label_block' => true,
			]
		);
        $faq_repeater->add_control(
			'faq_desc', [
				'label' => __( 'Description', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG ,
				'default' => __( 'Description' , 'wisdm-elementor-widgets' ),
				'label_block' => true,
			]
		);
        $this->add_control(
			'faq_list',
			[
				'label' => __( 'Faq List', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $faq_repeater->get_controls(),
				'default' => [
					[
						'faq_title' => __( 'Title #1', 'wisdm-elementor-widgets' ),
						'faq_desc' => __( 'Description', 'wisdm-elementor-widgets' ),
					],
					[
						'faq_title' => __( 'Title #2', 'wisdm-elementor-widgets' ),
						'faq_desc' => __( 'Description', 'wisdm-elementor-widgets' ),
					],
				],
				'title_field' => '{{{ faq_title }}}',
			]
		);

        $this->add_control(
			'faq_section_title',
			[
				'label' => __( 'Heading', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 'FAQs',
                'label_block' => true,
			]
		);
        $this->end_controls_section();
    }


    private function style_tab() {}

    protected function render() {
        $settings = $this->get_settings_for_display();
        // x($settings)
        $faq_list = $settings['faq_list'];
        $faq_heading = $settings['faq_section_title'];
        ?>
        <div id="blk-faq" class="wdm-elementor-faqs">
            <h2 class="blk-heading">
                <strong><?php echo $faq_heading;?></strong>
            </h2>
            <?php foreach($faq_list as $faq){
                $id = $faq['_id'];
                $faq_title = $faq['faq_title'];
                $faq_desc = $faq['faq_desc'];
                ?>
                <div class="wdm-elementor-faq">
                    <input class="wdm-faq-radio" type="radio" name="faq-radio" id="<?php echo $id;?>">
                    <label for="<?php echo $id;?>" class="wdm-faq-q">
                        <h4><?php echo $faq_title; ?></h4>
                        <i class="fa fa-angle-right" aria-hidden="true"></i><i class="fa fa-angle-down" aria-hidden="true"></i>
                    </label>
                    <?php echo "<span class='wdm-faq-a'> ".$faq_desc." </span>"; ?>
                </div>
                <?php
            }?>

        </div>
        <?php
    }

    protected function _content_template() {

    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Faqs_Widget() );