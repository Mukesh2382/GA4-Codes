<?php
namespace Elementor;
class Wisdm_Product_Slider_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-product-slider-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Product Slider', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'wdm-product-slider-script', 'slick-script'
        ];
    }

    public function get_style_depends() {
        return [ 'wdm-product-slider-style' , 'slick-style'];
    }

    public function get_icon() {
        return 'eicon-slider-push';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Slider Settings', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
            $repeater = new \Elementor\Repeater();
            // Product Image
            $repeater->add_control(
                'product_image', [
                    'label' => __( 'Product Image', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ],
                ]
            );
            
            // Product Title
            $repeater->add_control(
                'product_title', [
                    'label' => __( 'Product Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT ,
                    'default' => __( 'Product Title' , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );
            // Product Description
            $repeater->add_control(
                'product_desc', [
                    'label' => __( 'Product Description', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG ,
                    'default' => __( WDM_WIDGETS_DEFAULT_DESC , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );

            // Check it out link
            $repeater->add_control(
                'product_page_link',
                [
                    'label' => __( 'Product page Url', 'wisdm-elementor-widgets' ),
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

            // Check it out button title
            $repeater->add_control(
                'check_it_out_btn_title',
                [
                    'label' => __( 'Button Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Check It Out' , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );

            $this->add_control(
                'product_slides',
                [
                    'label' => __( 'Slides', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $repeater->get_controls(),
                    'default' => [
                        [
                            'product_image' => [
                                'url' => \Elementor\Utils::get_placeholder_image_src(),
                            ],
                            'product_title' => 'Product Title',
                            'product_desc' => 'Product Description',
                            'product_page_link' => [
                                'url' => '',
                                'is_external' => true,
                                'nofollow' => true,
                            ],
                            'check_it_out_btn_title' => 'Check It Out'
                        ],
                        [
                            'product_image' => [
                                'url' => \Elementor\Utils::get_placeholder_image_src(),
                            ],
                            'product_title' => 'Product Title',
                            'product_desc' => 'Product Description',
                            'product_page_link' => [
                                'url' => '',
                                'is_external' => true,
                                'nofollow' => true,
                            ],
                            'check_it_out_btn_title' => 'Check It Out'
                        ]
                    ],
                    'title_field' => '{{{ product_title }}}',
                ]
            );
        $this->end_controls_section();
        $this->style_tab();
    }


    private function style_tab() {
        $this->slider_configuration();
        $this->product_slide_style();
        $this->action_button_style();
        $this->product_title_style();
        $this->product_icon_style();
        $this->pagination_dots_style();
        $this->product_desc_style();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $product_slides = $settings['product_slides'];

        if(!empty($product_slides)){
            ?>
            <div class='wisdm-product-slider row wisdm-slick-slider' <?php echo \SlickSlider::get_slider_attributes($settings); ?>>
                <?php foreach ($product_slides as $key => $slide) {
                    $slide_image_url       = $slide['product_image']['url'];
                    $slide_image_id        = $slide['product_image']['id'];
                    $product_title         = $slide['product_title'];
                    $product_desc          = $slide['product_desc'];
                    $check_it_out_btn_title     = $slide['check_it_out_btn_title'];
                    $product_page_link     = $slide['product_page_link']['url'];
                    $is_external           = ($slide['product_page_link']['is_external'] == 'on');
                    ?>
                    <div class='wisdm-product-slide column'>
                        <a  href='<?php echo $product_page_link;?>' 
                            target="<?php echo ($is_external) ? '_blank' : '';?>" 
                            class="image-box product-image-icon">
                            <img id='<?php echo $slide_image_id;?>' src="<?php echo $slide_image_url;?>" alt="" />
                        </a>
                        <div class="product-title">
                            <h6><?php echo $product_title;?></h6>
                        </div>
                        <div class="product-desc">
                            <p><?php echo $product_desc;?></p>
                        </div>
                        <div class="product-check-it-out">
                            <a class='menu-link' href='<?php echo $product_page_link;?>' target="<?php echo ($is_external) ? '_blank' : '';?>"><?php echo $check_it_out_btn_title;?></a>
                        </div>
                    </div>
                    <?php
                }?>
                
            </div>
            <?php
        }
    }

    protected function _content_template() {

    }

    // Add Slider configuration controls
    private function slider_configuration(){
        $slider_options = [
            'slider_infinite_looping' => 'yes',
            'slider_visible_slides' => '2',
            'slider_visible_slides_tablet' => '2',
            'slider_visible_slides_mobile' => '1',
        ];
        \SlickSlider::slider_configuration($this,$slider_options);
    }

    // Action Button
    private function action_button_style(){
        $this->start_controls_section(
			'action_button_style',
			[
				'label' => __( 'Action Button', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Typography
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'action_button_typography',
                    'label' => __( 'Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .product-check-it-out a',
                ]
            );
            // Color
            $this->add_control(
                'action_button_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .product-check-it-out a' => 'color: {{VALUE}}',
                    ],
                    'default' => '#FFFFFF'
                ]
            );
            // Color On Hover
            $this->add_control(
                'action_button_color_hover',
                [
                    'label' => __( 'Color on Hover', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .product-check-it-out a:hover' => 'color: {{VALUE}}',
                    ],
                    'default' => WDM_PRIMARY_COLOR
                ]
            );
            // Background Color
            $this->add_control(
                'action_button_background_color',
                [
                    'label' => __( 'Background Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .product-check-it-out a' => 'background-color: {{VALUE}}',
                    ],
                    'default' => WDM_PRIMARY_COLOR
                ]
            );
            // Background  Color On Hover
            $this->add_control(
                'action_button_background_color_hover',
                [
                    'label' => __( 'Background Color on Hover', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .product-check-it-out a:hover' => 'background-color: {{VALUE}}',
                    ],
                    'default' => '#FFFFFF'
                ]
            );

            // padding
            $this->add_control(
                'action_button_padding',
                [
                    'label' => __( 'Padding', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'default' => [
                        'top' => 10,
                        'right' => 10,
                        'bottom' => 20,
                        'left' => 20,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .product-check-it-out a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            // Border Type
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'action_button_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .product-check-it-out a'
                ]
            );

        $this->end_controls_section();
    }

    // Product Title
    private function product_title_style(){
        $this->start_controls_section(
			'product_title_style',
			[
				'label' => __( 'Product Title', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Typography
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'product_title_typography',
                    'label' => __( 'Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wisdm-product-slider .product-title h6',
                ]
            );
            // Color
            $this->add_control(
                'product_title_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-product-slider .product-title h6' => 'color: {{VALUE}}',
                    ],
                ]
            );
            // Height
            $this->add_control(
                'product_title_height',
                [
                    'label' => __( 'Height', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'description' => 'Default: 19%',
                    'size_units' => [ '%'],
                    'range' => [
                        '%' => [
                            'min' => 10,
                            'max' => 30,
                        ]
                    ],
                    'default' => [
                        'unit' => '%',
                        'size' => 19,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .wisdm-product-slider .product-title' => 'height: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
        $this->end_controls_section();
    }

    private function product_desc_style(){
        $this->start_controls_section(
			'product_desc_style',
			[
				'label' => __( 'Product Description', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        // Height
        $this->add_control(
            'product_desc_height',
            [
                'label' => __( 'Height', 'wisdm-elementor-widgets' ),
                'type' => Controls_Manager::SLIDER,
                'description' => 'Default: 32%',
                'size_units' => [ '%'],
                'range' => [
                    '%' => [
                        'min' => 20,
                        'max' => 80,
                    ]
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 32,
                ],
                'selectors' => [
                    '{{WRAPPER}} .wisdm-product-slider .product-desc' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
    }

    private function product_icon_style(){
        $this->start_controls_section(
			'product_icon_style',
			[
				'label' => __( 'Product Image', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->end_controls_section();
    }

    private function pagination_dots_style(){
        $this->start_controls_section(
			'pagination_dots_style',
			[
				'label' => __( 'Pagination Dots', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        $this->end_controls_section();
    }
    
    private function product_slide_style(){
        $this->start_controls_section(
			'product_slide_style',
			[
				'label' => __( 'Product Slide Box', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
        // Height
        $this->add_control(
            'product_slide_height',
            [
                'label' => __( 'Height', 'wisdm-elementor-widgets' ),
                'type' => Controls_Manager::SLIDER,
                'description' => 'Default: 410px',
                'size_units' => [ 'px','%'],
                'range' => [
                    'px' => [
                        'min' => 250,
                        'max' => 700,
                    ],
                    '%' => [
                        'min' => 20,
                        'max' => 80,
                    ]
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 410
                ],
                'selectors' => [
                    '{{WRAPPER}} .wisdm-product-slider .wisdm-product-slide' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
    }
    
}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Product_Slider_Widget() );
