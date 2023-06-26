<?php
namespace Elementor;
class Wisdm_Client_Speak_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-clients-speak-widget-id';
    }

    public function get_title() {
        return esc_html__( "Wisdm Clients Speak", 'wisdm-elementor-widgets' );
    }
  
    public function get_script_depends() {
        return [ 'wdm-clients-speak-script', 'slick-script' ];
    }

    public function get_style_depends() {
        return [ 'wdm-clients-speak-style' , 'slick-style'];
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
				'label' => __( 'Slides Content', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
            $repeater = new \Elementor\Repeater();

            // Photo
            $repeater->add_control(
                'photo', [
                    'label' => __( 'Photo', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ],
                ]
            );
            
            // Author
            $repeater->add_control(
                'name', [
                    'label' => __( 'Name', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT ,
                    'default' => __( 'John Doe' , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );

            // Profile
            $repeater->add_control(
                'profile', [
                    'label' => __( 'Profile / Company', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT ,
                    'default' => __( 'Web Developer - Wisdmlabs' , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );

            // Quote
            $repeater->add_control(
                'quote', [
                    'label' => __( 'Quote', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG ,
                    'default' => __( WDM_WIDGETS_DEFAULT_DESC , 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                ]
            );

            $default = [
                'photo' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'name' => 'John Doe',
                'profile' => 'Web Developer - Wisdmlabs',
                'quote' => WDM_WIDGETS_DEFAULT_DESC,
            ];

            $this->add_control(
                'slides',
                [
                    'label' => __( 'Slides', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $repeater->get_controls(),
                    'default' => [
                        $default,$default
                    ],
                    'title_field' => '{{{ name }}}',
                ]
            );

        $this->end_controls_section();

        $this->slider_configuration();

        $this->style_tab();
    }

    private function style_tab() {
        $this->author_name_style();
        $this->author_profile_style();
        $this->image_style();
    }

    // Author Name
    private function author_name_style(){
        $this->start_controls_section(
			'author_name_style',
			[
				'label' => __( 'Author Name', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Typography
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'author_name_typography',
                    'label' => __( 'Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .cs-author-name .cs-name',
                ]
            );
            // Color
            $this->add_control(
                'author_name_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .cs-author-name .cs-name' => 'color: {{VALUE}}',
                    ],
                ]
            );
        $this->end_controls_section();
    }

    // Author Profile
    private function author_profile_style(){
        $this->start_controls_section(
			'author_profile_style',
			[
				'label' => __( 'Author Profile', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Typography
            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'author_profile_typography',
                    'label' => __( 'Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .cs-author-name .cs-author-profile',
                ]
            );
            // Color
            $this->add_control(
                'author_profile_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .cs-author-name .cs-author-profile' => 'color: {{VALUE}}',
                    ],
                ]
            );
        $this->end_controls_section();
    }

    // Photo
    private function image_style(){
        $this->start_controls_section(
			'image_style',
			[
				'label' => __( 'Photo', 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Height
            $this->add_responsive_control(
                'image_height',
                [
                    'label' => __( 'Height', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'description' => 'Default: 90px',
                    'size_units' => [ 'px'],
                    'range' => [
                        'px'=> [
                            'min' => 70,
                            'max' => 200,
                        ]
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 90,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .cs-photo-wrap' => 'height: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
            // Width
            $this->add_responsive_control(
                'image_width',
                [
                    'label' => __( 'Width', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SLIDER,
                    'description' => 'Default: 90px',
                    'size_units' => [ 'px'],
                    'range' => [
                        'px'=> [
                            'min' => 70,
                            'max' => 200,
                        ]
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 90,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .cs-photo-wrap' => 'width: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $slides = $settings['slides']; 
        ?>
        <div class='clients-speak-slider wisdm-slick-slider' <?php echo \SlickSlider::get_slider_attributes($settings);?> >
            <?php foreach ($slides as $key => $slide) {
                $photo_url      = $slide['photo']['url'];
                $photo_id       = $slide['photo']['id'];
                $profile        = $slide['profile'];
                $name           = $slide['name'];
                $quote          = $slide['quote'];
                ?>
                <div class="clients-speak-slide">
                    <div class="clients-speak-slide-inr">
                        <div class="cs-author-details">
                            <div class="cs-author-details-inr">
                                <span class='cs-photo-wrap'>
                                    <img class='cs-photo' id='<?php echo $photo_id; ?>' src="<?php echo $photo_url; ?>" alt="">
                                </span>
                                <cite class="cs-author-name">
                                    <span class="cs-name"><?php echo $name; ?></span>
                                    <span class="cs-author-profile"><?php echo $profile; ?></span>
                                </cite>
                            </div>
                        </div>
                        <blockquote class="cs-content">
                            <span class='cs-quote'><?php echo $quote; ?></span>
                        </blockquote>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    protected function _content_template() {
        
    }

    // Add Slider configuration controls
    private function slider_configuration(){
        $slider_options = [
            'slider_infinite_looping' => 'yes',

            'slider_visible_slides' => '1',
            'slider_visible_slides_tablet' => '1',
            'slider_visible_slides_mobile' => '1'
        ];
        \SlickSlider::slider_configuration($this,$slider_options);
    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Client_Speak_Widget() );