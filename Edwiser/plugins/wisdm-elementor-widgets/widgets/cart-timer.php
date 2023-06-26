<?php
namespace Elementor;
class Cart_Timer_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-cart-timer-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Cart Timer Newsletter', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [ 'cart-timer-script' ];
    }

    public function get_style_depends() {
        return [ 'cart-timer-style'];
    }

    public function get_icon() {
        return 'eicon-counter-circle';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $downloads = \WisdmEW_Edd::get_downloads();

        $this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Timer Settings', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
            // Timer Limit
            $this->add_control(
                'timer_limit',
                [
                    'label' => __( 'Timer Limit', 'wisdm-elementor-widgets' ),
                    'description' => __( 'Time in minutes', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'minutes' ],
                    'range' => [
                        'minutes' => [
                            'min' => 5,
                            'max' => 60,
                            'step' => 1,
                        ],
                    ],
                    'default' => [
                        'unit' => 'minutes',
                        'size' => 30,
                    ]
                ]
            );

            // Select Products
            $this->add_control(
                'downloads',
                [
                    'label' => __( 'Downloads', 'plugin-domain' ),
                    'type' => \Elementor\Controls_Manager::SELECT2,
                    'multiple' => true,
                    'options' => $downloads,
                    'label_block' => true,
                ]
            );

            // Select Title
            $this->add_control(
                'title', [
                    'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Title' , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );

            // Select Description
            $this->add_control(
                'description', [
                    'label' => __( 'Description', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => __( 'Description' , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );

            // Next Offer After
            $this->add_control(
                'next_offer_after',
                [
                    'label' => __( 'Next offer after', 'wisdm-elementor-widgets' ),
                    'description' => __( 'Next offer will be available after this value', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'days'],
                    'range' => [
                        'days' => [
                            'min' => 1,
                            'max' => 60,
                            'step' => 1,
                        ]
                    ],
                    'default' => [
                        'unit' => 'days',
                        'size' => 2,
                    ]
                ]
            );

        $this->end_controls_section();

        $this->style_tab();
    }

    private function style_tab() {
        // $this->general_style();
        // $this->title_style();
        // $this->button_style();
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

        

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'button_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .input-group-area',
                ]
            );
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'input_border',
                    'label' => __( 'Email box Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .input-group-icon',
                ]
            );
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        y($settings);
        ?>
        <div class="wdm-elementor-cart-timer" >
           
        </div>
        <?php
    }

    protected function _content_template() {

    }

    
}

Plugin::instance()->widgets_manager->register_widget_type( new Cart_Timer_Widget() );