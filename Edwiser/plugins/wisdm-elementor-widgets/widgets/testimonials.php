<?php
namespace Elementor;
class Wisdm_Testimonials_Widget extends Widget_Base {

    public function get_name() {
        return  'myew-testimonials-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Testimonials', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script'
        ];
    }

    public function get_icon() {
        return 'eicon-testimonial-carousel';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Testimonials Settings', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
            $repeater = new \Elementor\Repeater();
            // Description
            $repeater->add_control(
                'testimonial', [
                    'label' => __( 'Testimonial', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG ,
                    'default' => __( WDM_WIDGETS_DEFAULT_DESC , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );
            // Name
            $repeater->add_control(
                'name', [
                    'label' => __( 'Name', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT ,
                    'default' => __( 'John Doe' , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );
            // Company
            $repeater->add_control(
                'company', [
                    'label' => __( 'Company', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT ,
                    'default' => __( 'CEO , example.com' , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );
            // Image
            $repeater->add_control(
                'image', [
                    'label' => __( 'Image', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ],
                ]
            );

            $this->add_control(
                'testimonials',
                [
                    'label' => __( 'Testimonials', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $repeater->get_controls(),
                    'default' => [
                        [
                            'name' => __( 'John Doe', 'wisdm-elementor-widgets' ),
                            'testimonial' => __( WDM_WIDGETS_DEFAULT_DESC, 'wisdm-elementor-widgets' ),
                            'company' => __( 'CEO , example.com', 'wisdm-elementor-widgets' ),
                            'image' => [
                                'url' => \Elementor\Utils::get_placeholder_image_src(),
                            ],
                        ],
                        [
                            'name' => __( 'John Doe', 'wisdm-elementor-widgets' ),
                            'testimonial' => __( WDM_WIDGETS_DEFAULT_DESC, 'wisdm-elementor-widgets' ),
                            'company' => __( 'CEO , example.com', 'wisdm-elementor-widgets' ),
                            'image' => [
                                'url' => \Elementor\Utils::get_placeholder_image_src(),
                            ],
                        ]
                    ],
                    'title_field' => '{{{ name }}}',
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

            'slider_visible_slides' => '1',
            'slider_visible_slides_tablet' => '1',
            'slider_visible_slides_mobile' => '1',

            'slider_show_arrows' => 'yes',
            'slider_show_arrows_tablet' => 'yes',
            'slider_show_arrows_mobile' => 'yes',

            'slider_center_mode' => 'no',
            'slider_center_mode_tablet' => 'no',
            'slider_center_mode_mobile' => 'no',

            'slider_center_padding' => '5',
            'slider_center_padding_tablet' => '2',
            'slider_center_padding_mobile' => '2',

            'slider_breakpoint' => '0',
            'slider_breakpoint_tablet' => '786',
            'slider_breakpoint_mobile' => '480',

            'slider_show_dots' => 'yes',
            'slider_show_dots_tablet' => 'yes',
            'slider_show_dots_mobile' => 'yes',

            'slider_autoplay' => 'yes',
            'slider_autoplay_tablet' => 'yes',
            'slider_autoplay_mobile' => 'yes',

            'slider_autoplay_speed' => '3000',
            'slider_autoplay_speed_tablet' => '3000',
            'slider_autoplay_speed_mobile' => '3000',
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
                            'max' => 400,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 50,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-tm-image img' => 'width: {{SIZE}}{{UNIT}};',
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
                            'max' => 400,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 50,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-tm-image img' => 'height: {{SIZE}}{{UNIT}};',
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
                        '{{WRAPPER}} .wisdm-tm-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            // Border Type
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'image_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wisdm-tm-image',
                ]
            );

            // Border Radius
            $this->add_responsive_control(
                'image_border_radius',
                [
                    'label' => __( 'Border Radius', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ '%','px'],
                    'description' => 'Default: 50%',
                    'range' => [
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                        'px' => [
                            'min' => 0,
                            'max' => 400,
                            'step' => 1,
                        ]
                    ],
                    'default' => [
                        'unit' => '%',
                        'size' => 50,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-tm-image' => 'border-radius: {{SIZE}}{{UNIT}} ;',
                        '{{WRAPPER}} .wisdm-tm-image img' => 'border-radius: {{SIZE}}{{UNIT}} ;',
                    ],
                ]
            );

            // Box Shadow
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'image_box_shadow',
                    'label' => __( 'Box Shadow', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wisdm-tm-image',
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
            // Padding
            $this->add_responsive_control(
                'content_box_padding',
                [
                    'label' => __( 'Box Padding', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-tm-details' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'description' => 'Default: 5px',
                ]
            );

            // Author Name Bottom Spacing
            $this->add_responsive_control(
                'author_name_bottom_spacing',
                [
                    'label' => __( 'Name Bottom Spacing', 'wisdm-elementor-widgets' ),
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
                        '{{WRAPPER}} .wisdm-tm-author' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

             // Author Name Top Spacing
             $this->add_responsive_control(
                'author_name_top_spacing',
                [
                    'label' => __( 'Name Top Spacing', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'description' => 'Default: 0px',
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
                        'size' => 0,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-tm-author' => 'margin-top: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            // Author Name Typography
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'author_name_typography',
                    'label' => __( 'Author Name Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wisdm-tm-author',
                ]
            );

            // Author Name Color
            $this->add_control(
                'author_name_color',
                [
                    'label' => __( 'Author Name Color', 'plugin-domain' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-tm-author' => 'color: {{VALUE}}',
                    ],
                ]
            );

            //Author Company Typography
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'author_company_typography',
                    'label' => __( 'Author Company Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wisdm-tm-company',
                ]
            );

            // Author Company Color
            $this->add_control(
                'author_company_color',
                [
                    'label' => __( 'Author Company Color', 'plugin-domain' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-tm-company' => 'color: {{VALUE}}',
                    ],
                ]
            );
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $testimonials = $settings['testimonials'];
        ?>
        <div class='wisdm-testimonials row wisdm-slick-slider' <?php echo \SlickSlider::get_slider_attributes($settings); ?>>
            <?php 
            foreach($testimonials as $testimonial){ 
                $name = $testimonial['name'];
                $desc = trim($testimonial['testimonial']);
                $company = $testimonial['company'];
                $image_url = $testimonial['image']['url'];
                $image_id = $testimonial['image']['id'];
            ?>
                <div class="wisdm-testimonial-wrap column">
                    <div class="wisdm-tm-desc">
                        <?php 
                            $desc_length =  strlen($desc);
                            $char_limit = 350; 
                            $visible_desc = $desc;
                            if($desc_length > $char_limit){
                                $visible_desc = substr($desc, 0, $char_limit);
                            }
                        ?>
                        <div class="limited-chars">
                            <?php echo $visible_desc; ?>
                            <?php if($desc_length > $char_limit){ ?>
                                <a  class="read-more">read more...</a>
                            <?php } ?>
                        </div>
                        <div style="display:none" class="full-chars">
                            <?php echo $desc; ?>
                            <a class="view-less">view less...</a>
                        </div>
                    </div>
                    <div class="wisdm-tm-meta">
                        <div class="wisdm-tm-image">
                            <img src="<?php echo $image_url; ?>" id='<?php echo $image_id; ?>' alt="">
                        </div>
                        <div class="wisdm-tm-details">
                            <div class="wisdm-tm-author">
                                <?php echo $name;?>
                            </div>
                            <div class="wisdm-tm-company">
                                <?php echo $company;?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    protected function _content_template() {

    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Testimonials_Widget() );
