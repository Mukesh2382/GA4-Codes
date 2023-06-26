<?php
namespace Elementor;
class Sendy_Newsletter_Widget extends Widget_Base {

    public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);
	}

    public function get_name() {
        return  'wisdm-sendy-newsletter-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Sendy Newsletter', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [ 'sendy-newsletter-script' ];
    }

    public function get_style_depends() {
        return [ 'sendy-newsletter-style'];
    }

    public function get_icon() {
        return 'eicon-email-field';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Newsletter Settings', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'title', [
				'label' => __( 'Title', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::WYSIWYG,
				'default' => __( 'Subscribe to our newsletter' , 'wisdm-elementor-widgets' ),
				'label_block' => true,
			]
		);
        $this->add_control(
			'sendy_list_id', [
				'label' => __( 'Sendy List Id', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT ,
                'description' => '',
                'label_block' => true,
			]
		);
        $this->add_control(
			'button_text', [
				'label' => __( 'Button Text', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT ,
                'label_block' => true,
                'default' => __( 'Subscribe' , 'wisdm-elementor-widgets' ),
			]
		);
        $this->end_controls_section();
        $this->style_tab();
    }

    private function style_tab() {
        $this->general_style();
        $this->title_style();
        $this->button_style();
    }

    protected function general_style(){
        $this->start_controls_section(
			'general_style',
			[
				'label' => __( 'General', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->add_responsive_control(
            'title_width',
            [
                'label' => __( 'Title Width', 'wisdm-elementor-widgets' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ '%' ],
                'range' => [
                    '%' => [
                        'min' => 30,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 90,
                ],
                'selectors' => [
                    '{{WRAPPER}} .wdm-sn-title' => 'text-align:center;width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'box_width',
            [
                'label' => __( 'Input Box Width', 'wisdm-elementor-widgets' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ '%' ],
                'range' => [
                    '%' => [
                        'min' => 30,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 90,
                ],
                'selectors' => [
                    '{{WRAPPER}} .input-group' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
    }

    protected function title_style(){
        $this->start_controls_section(
			'title_style',
			[
				'label' => __( 'Title', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Typography
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'title_typography',
                    'label' => __( 'Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wdm-sn-title',
                ]
            );
            // Color
            $this->add_control(
                'title_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wdm-sn-title' => 'color: {{VALUE}}',
                    ],
                ]
            );
        $this->end_controls_section();
    }

    protected function button_style(){
        $this->start_controls_section(
			'button_style',
			[
				'label' => __( 'Subscribe Button', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Typography
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'button_typography',
                    'label' => __( 'Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wdm-sn-button a',
                ]
            );
            // Color
            $this->add_control(
                'button_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wdm-sn-button a' => 'color: {{VALUE}}',
                    ],
                ]
            );
            // Background Color
            $this->add_control(
                'button_bg_color',
                [
                    'label' => __( 'Background Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wdm-sn-button' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            $this->add_control(
                'button_border_heading',
                [
                    'label' => __( 'Button Border', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'button_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .input-group-icon',
                ]
            );
            $this->add_control(
                'input_border_heading',
                [
                    'label' => __( 'Email box Border', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'input_border',
                    'label' => __( 'Email box Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .input-group-area',
                ]
            );
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $title = $settings['title'];
        $button_text = $settings['button_text'];
        $sendy_list_id = $settings['sendy_list_id'];
        $nonce = wp_create_nonce( 'wdm-elem-cart' );
        ?>
        <div class="wdm-elementor-sendy-newsletter" style="text-align: left;">
            <div class='wdm-sn-title'><?php echo $title;?></div>
            <input type='hidden' name='sendy_list_id' id='sendy_list_id' value='<?php echo $sendy_list_id;?>'>
            <div class="input-group" style="text-align: left;">
                <div class="input-group-area" >
                    <input name='subscribe_email_id' id='subscribe_email_id' type="text" placeholder="Email">
                </div>
                <div class="input-group-icon wdm-sn-button">
                    <a data-nonce='<?php echo $nonce;?>'>
                        <?php echo $button_text;?>
                    </a>
                </div>
            </div>
            <p>
                <strong>
                    <span class="sendy-failed-response"></span>
                    <span class="sendy-success-response"></span>
                </strong>
            </p>
        </div>
        <?php
    }

    protected function _content_template() {
        ?>
        <div class="wdm-elementor-sendy-newsletter" style="text-align: left;">
            <div class='wdm-sn-title'>
                {{{ settings.title }}}
            </div>
            <input type='hidden' name='sendy_list_id' id='sendy_list_id' 
            value="{{ settings.sendy_list_id }}">
            <div class="input-group" style="text-align: left;">
                <div class="input-group-area" >
                    <input name='subscribe_email_id' id='subscribe_email_id' type="text" placeholder="Email">
                </div>
                <div class="input-group-icon wdm-sn-button">
                    <a data-nonce=''>
                        {{{ settings.button_text }}}
                    </a>
                </div>
            </div>
            <p>
                <strong>
                    <span class="sendy-failed-response"></span>
                    <span class="sendy-success-response"></span>
                </strong>
            </p>
        </div>
        <?php
    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Sendy_Newsletter_Widget() );