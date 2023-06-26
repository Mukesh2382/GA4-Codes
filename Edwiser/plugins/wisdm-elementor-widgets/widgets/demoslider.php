<?php
namespace Elementor;
class Wisdm_Demoslider_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-demoslider-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Demoslider', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script'
        ];
    }

    public function get_icon() {
        return 'eicon-post-slider';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Demoslider Settings', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

            $slides = new \Elementor\Repeater();
            $slides->add_control(
                'demo_image',
                [
                    'label' => __( 'Demo Image', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ]
                ]
            );
            $slides->add_control(
                'demo_title',
                [
                    'label' => __( 'Demo Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'label_block' => true,
                    'default' => __( 'Demo Title', 'wisdm-elementor-widgets' ),
                ]
            );
            $slides->add_control(
                'demo_page_link',
                [
                    'label' => __( 'Demo page url', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::URL,
                    'placeholder' => __( 'https://your-link.com', 'wisdm-elementor-widgets' ),
                    'show_external' => true,
                    'default' => [
                        'url' => '',
                        'is_external' => true,
                        'nofollow' => true,
                    ],
                ]
            );
            
            $this->add_control(
                'slides',
                [
                    'label' => __( 'Slides', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $slides->get_controls(),
                    'default' => [
                       
                    ],
                    'title_field' => '{{{ demo_title }}}',
                ]
            );
        $this->end_controls_section();

        $this->style_tab();
    }

    private function style_tab() {
        $this->slider_configuration();
        $this->slide_image_style();
        $this->slide_title_style();
    }

    private function slider_configuration(){
        $slider_options = [
            'slider_infinite_looping' => 'yes',
            'slider_autoplay' => 'no',
            'slider_autoplay_tablet' => 'no',
            'slider_autoplay_mobile' => 'no',
        ];
        \SlickSlider::slider_configuration($this,$slider_options);
    }

    private function slide_image_style(){
        // Image Style Settings
        $this->start_controls_section(
            'image_style_section',
            [
                'label' => __( 'Image', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

            // Width
            $this->add_responsive_control(
                'image_width',
                [
                    'label' => __( 'Width', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'description' => 'Default: 380px',
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 600,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 235,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .slideimage ' => 'width: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            // Height
            $this->add_responsive_control(
                'image_height',
                [
                    'label' => __( 'Height', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'description' => 'Default: 380px',
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 700,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 380,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .slideimage' => 'height: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            // Padding
            $this->add_responsive_control(
                'image_padding',
                [
                    'label' => __( 'Padding', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'default' => [
                        'top' => 0,
                        'right' => 0,
                        'bottom' => 0,
                        'left' => 0,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .slideimage' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            // Border Type
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'image_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .slideimage',
                ]
            );

            // Border Radius
            $this->add_responsive_control(
                'image_border_radius',
                [
                    'label' => __( 'Border Radius', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'default' => [
                        'top' => 0,
                        'right' => 0,
                        'bottom' => 0,
                        'left' => 0,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .slideimage' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .slideimage' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            // Box Shadow
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'image_box_shadow',
                    'label' => __( 'Box Shadow', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .slideimage',
                ]
            );

        $this->end_controls_section();
    }

    private function slide_title_style(){
         /**
         * Slide TItle
         */
        $this->start_controls_section(
            'content_style_section',
            [
                'label' => __( 'Content', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
            // Position
            $this->add_responsive_control(
                'content_position',
                [
                    'label' => __( 'Content Position', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'top',
                    'options' => [
                        'top' => "Top",
                        'bottom' => "Bottom",
                    ],
                ]
            );
            // Padding
            $this->add_responsive_control(
                'content_padding',
                [
                    'label' => __( 'Padding', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'default' => [
                        'top' => 10,
                        'right' => 10,
                        'bottom' => 10,
                        'left' => 10,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-demoslider .quote' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'description' => 'Default: 10px',
                ]
            );

            // Title Bottom Spacing
            $this->add_responsive_control(
                'content_title_bottom_spacing',
                [
                    'label' => __( 'Bottom Spacing', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'description' => 'Default: 5px',
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 1000,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 5,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-demoslider .quote' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

             // Title Top Spacing
             $this->add_responsive_control(
                'content_title_top_spacing',
                [
                    'label' => __( 'Top Spacing', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'description' => 'Default: 5px',
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 1000,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 5,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-demoslider .quote' => 'margin-top: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            // Title Color
            $this->add_control(
                'content_title_color',
                [
                    'label' => __( 'Title Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-demoslider .quote' => 'color: {{VALUE}}',
                    ],
                    'default' => '#000000',
                ]
            );
        $this->end_controls_section();

    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $slides = $settings['slides'];
        ?>
        <div class='wisdm-demoslider row wisdm-slick-slider' <?php echo \SlickSlider::get_slider_attributes($settings); ?> >
            <?php foreach($slides as $index => $slide) {
                    $slide_image_url        = $slide['demo_image']['url'];
                    $is_external            = ($slide['demo_page_link']['is_external'] == 'on');
                    $slide_image_id         = $slide['demo_image']['id'];
                    $slide_demo_title       = $slide['demo_title'];
                    $slide_demo_page_url    = $slide['demo_page_link']['url'];
                ?>
                <div class=" column" index="<?php echo $index; ?>" >
                    <?php if ($settings['content_position'] == "top") { ?>
                        <p class="quote" style="text-align:center;" >
                            <?php echo $slide_demo_title; ?>
                        </p>
                    <?php } ?>
                    <div class="slideimage">
                        <a href="<?php echo $slide_demo_page_url;?>" target="<?php echo ($is_external) ? '_blank' : '';?>" >
                            <img src="<?php echo $slide_image_url; ?>" 
                                id="<?php echo $slide_image_id; ?>" 
                                alt="" />
                        </a>
                    </div>
                    <?php if ($settings['content_position'] == "bottom") { ?>
                        <p class="quote" style="text-align:center;" >
                            <?php echo $slide_demo_title; ?>
                        </p>
                    <?php } ?>
                </div>
                <?php
            } ?>
        </div>
        <?php
    }

    protected function _content_template() {

    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Demoslider_Widget() );
