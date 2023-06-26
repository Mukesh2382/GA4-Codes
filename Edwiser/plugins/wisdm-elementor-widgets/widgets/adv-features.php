<?php
namespace Elementor;
use Elementor\Core\Base\Base_Object;

class MYEW_Advanced_Features_Widget extends Widget_Base {
    public function get_name() {
        return  'myew-adv-features-id';
    }

    public function get_title() {
        return esc_html__( 'Advanced Features', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script'
        ];
    }
   
    public function get_icon() {
        return 'eicon-featured-image';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
            'features-section',
            [
                'label' => __( 'Features', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
            $feature_repeater = new \Elementor\Repeater();
            $feature_repeater->add_control(
                'feature_image',
                [
                    'label' => __( 'Image', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ],
                    'label_block' => true
                ]
            );
            $feature_repeater->add_control(
                'feature_title',
                [
                    'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Feature title', 'wisdm-elementor-widgets' ),
                    'label_block' => true
                ]
            );
            $feature_repeater->add_control(
                'feature_subtitle',
                [
                    'label' => __( 'Subtitle', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Feature subtitle', 'wisdm-elementor-widgets' ),
                    'label_block' => true
                ]
            );
            $feature_repeater->add_control(
                'feature_desc',
                [
                    'label' => __( 'Description', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => __( 'Feature Description', 'wisdm-elementor-widgets' ),
                    'label_block' => true
                ]
            );
            $feature_repeater->add_control(
                'feature_block_style',
                [
                    'label' => __( 'Image text pattern', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'default' => __( 'image-text', 'wisdm-elementor-widgets' ),
                    'options' => [
                        'image-text' => [
                            'title' => __( 'Image First', 'wisdm-elementor-widgets' ),
                        ],
                        'text-image' => [
                            'title' => __( 'Text First', 'wisdm-elementor-widgets' ),
                        ]
                    ],
                   'description' => 'This will not work if zigzag pattern is selected'
                ]
            );
            $feature_repeater->add_control(
                'show_ribbon',
                [
                    'label' => __( 'Show Ribbon', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes',
                    'default' => 'no',
                ]
            );
            $feature_repeater->add_control(
                'ribbon_text',
                [
                    'label' => __( 'Ribbon Text', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                    'default' => 'Best Seller',
                    'condition' => [
                        'show_ribbon' => 'yes'
                    ]
                ]
            );
            $feature_repeater->add_control(
                'screenshots',
                [
                    'label' => __( 'Screenshots', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::GALLERY,
                    'default' => []
                ]
            );

            $this->add_control(
                'features_pattern',
                [
                    'label' => __( 'Pattern', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        'zigzag' =>  __( 'Zig-zag', 'wisdm-elementor-widgets' ),
                        'custom' =>  __( 'Custom', 'wisdm-elementor-widgets' ),
                    ],
                    'default' => 'zigzag',
                    'toggle' => true,
                ]
            );
            $this->add_control(
                'fold_limit',
                [
                    'label' => __( 'Fold Limit', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'min' => 1,
                    'max' => 50,
                    'step' => 1,
                    'default' => 2,
                ]
            );

            $this->add_control(
                "features",
                [
                    'label' => __( 'Features', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $feature_repeater->get_controls(),
                    'default' => [],
                    'title_field' => "Feature - {{{ feature_title.substring(0,24) }}}",
                ]
            );

           
        $this->end_controls_section();
        // Style Tab
        $this->style_tab();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $features = $settings['features'];
        $features_pattern = $settings['features_pattern'];
        $fold_limit = $settings['fold_limit'];
        $text_first = true;
        if(empty($features)){
            return false;
        }
        $this->reset_slide_index();
        ?>

            <div class="edw-adv-featuresec edw-slider-wrap" >
                <div class="sec-bl-wrap">
                    <?php foreach ($features as $key => $feature) {
                        $pattern = 'custom';
                        $text_first = !$text_first;
                        if($features_pattern == 'zigzag'){
                            $pattern = ($text_first) ? "text-image" : "image-text";
                        }
                        $this->render_feature($feature,$pattern); 
                    } 

                    $this->reset_slide_index();
                    ?>
                </div>  

                <div class="edw-slider-popup" id="edw-slider-popup">
                    <i class="btn-cf-popup fa fa-times fa-2x"></i>
                    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"><span class="slider__label sr-only"></span></div>
                    <div class="cont-wrap">
                        <div class="slider-container">
                            <div class="feture-cont">
                                <h3></h3>
                                <h5></h5>
                                <p></p>
                            </div>
                            <div class="ss-cont">
                            <?php 
                                foreach ($features as $key => $feature) {
                                    $this->render_popup_section($feature); 
                                } 
                            ?>
                            </div>
                        </div>
                    </div>

                </div>
                <?php if(!empty($fold_limit) && $fold_limit < count($features)) {?>
                <div class="feature-tooggle">
                    <button data-vt="<?php echo $fold_limit;?>" data-hvl="1" class="btn-show-more-features">Show More Features</button>
                </div>
                <?php } ?>
            </div>

        <?php

       
    }

    function render_feature($feature,$pattern='custom'){
        $feature_title = $feature['feature_title'];
        $feature_subtitle = $feature['feature_subtitle'];
        $feature_desc = $feature['feature_desc'];
        $feature_image = $feature['feature_image'];
        $feature_image_url = $feature_image['url'];
        $feature_image_id = $feature_image['id'];
        $screenshots = $feature['screenshots'];
        // $screenshots = array_merge([$feature_image] , $screenshots);
        $show_ribbon = ($feature['show_ribbon'] == 'yes');
        $ribbon_text = $feature['ribbon_text'];
        $feature_block_style = $feature['feature_block_style'];
        if($pattern!='custom'){
            $feature_block_style = $pattern;
        }
        $popup_attributes =   $this->fetch_popup_data_attr($feature_title,$feature_subtitle,$feature_desc,$this->slide_index());
        $this->increment_slide_index(count($screenshots));
        ?>
        <div class="edw-adv-feature is-style-<?php echo $feature_block_style; ?>" >
            <div class="blk-container">
                <div class="feature-img show-edw-slider-popup" <?php echo $popup_attributes; ?> >
                    <img src="<?php echo $feature_image_url; ?>" id="<?php echo $feature_image_id; ?>">
                    <noscript>
                        <img src="<?php echo $feature_image_url; ?>" id="<?php echo $feature_image_id; ?>"/>
                    </noscript>
                </div>
                <div class="feature-text" >
                    <div class="content">
                        <h3 class="heading"><?php echo $feature_title; ?></h3>
                        <h4 class="sub_title"><?php echo $feature_subtitle; ?></h4>
                        <p class="desc"><?php echo $feature_desc; ?></p>
                    </div>
                    <div class="child-blk">
                        <?php if(count($screenshots) > 0) { ?>
                        <div class="wp-block-edwiseradvancedblock-slidegotolink">
                            <a class="slider_link  btn-view-fss show-edw-slider-popup" 
                                <?php echo $popup_attributes; ?>
                            >
                                View <?php echo count($screenshots); ?> Screenshot
                            </a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    function fetch_popup_data_attr($feature_title,$feature_subtitle,$feature_desc,$slide_index){
        $popup_attributes =   " data-heading='$feature_title' ";
        $popup_attributes .=  " data-description_title='$feature_subtitle' ";
        $popup_attributes .=  " data-description='$feature_desc' ";
        $popup_attributes .=  " data-si='$slide_index' ";
        return $popup_attributes;
    }
    
    function render_popup_section($feature){
        $feature_title = $feature['feature_title'];
        $feature_subtitle = $feature['feature_subtitle'];
        $feature_desc = $feature['feature_desc'];
        $feature_image = $feature['feature_image'];
        $feature_image_url = $feature_image['url'];
        $feature_image_id = $feature_image['id'];
        $screenshots = $feature['screenshots'];
        // $screenshots = array_merge([$feature_image] , $screenshots);
        foreach ($screenshots as  $screenshot) {
            
            $screenshot_image_url = $screenshot['url'];
            $screenshot_image_id = $screenshot['id'];
            $popup_attributes =   $this->fetch_popup_data_attr($feature_title,$feature_subtitle,$feature_desc,$this->slide_index());
            $this->increment_slide_index();

            ?>
            <div class="slide-img-wrap" <?php echo $popup_attributes; ?> >
                <img class="snps-img" 
                    data-no-lazy="1" 
                    src="" 
                    data-lazy="<?php echo $screenshot_image_url;?>" 
                    alt="" id="<?php echo $screenshot_image_id;?>"/>
            </div>
            <?php
        }
    }

    protected function _content_template() {

    }

    private function style_tab() {
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
                    'label' => __( 'Width', 'plugin-domain' ),
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'description' => 'Desfault: 50%',
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
                        'unit' => '%',
                        'size' => 50,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .blk-container .feature-img' => 'width: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            // // Height
            // $this->add_responsive_control(
            //     'image_height',
            //     [
            //         'label' => __( 'Height', 'plugin-domain' ),
            //         'type' => Controls_Manager::SLIDER,
            //         'size_units' => [ 'px', '%' ],
            //         'description' => 'Desfault: 380px',
            //         'range' => [
            //             'px' => [
            //                 'min' => 0,
            //                 'max' => 1000,
            //                 'step' => 1,
            //             ],
            //             '%' => [
            //                 'min' => 0,
            //                 'max' => 100,
            //             ],
            //         ],
            //         'default' => [
            //             'unit' => 'px',
            //             'size' => 380,
            //         ],
            //         'selectors' => [
            //             '{{WRAPPER}} .blk-container .feature-img' => 'height: {{SIZE}}{{UNIT}};',
            //         ],
            //     ]
            // );

            // // Padding
            // $this->add_responsive_control(
            //     'image_padding',
            //     [
            //         'label' => __( 'Padding', 'plugin-domain' ),
            //         'type' => Controls_Manager::DIMENSIONS,
            //         'size_units' => [ 'px', '%', 'em' ],
            //         'default' => [
            //             'top' => 0,
            //             'right' => 0,
            //             'bottom' => 0,
            //             'left' => 0,
            //         ],
            //         'selectors' => [
            //             '{{WRAPPER}} .blk-container .feature-img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            //         ],
            //     ]
            // );

            // // Border Type
            // $this->add_group_control(
            //     \Elementor\Group_Control_Border::get_type(),
            //     [
            //         'name' => 'image_border',
            //         'label' => __( 'Border', 'plugin-domain' ),
            //         'selector' => '{{WRAPPER}} .blk-container .feature-img',
            //     ]
            // );

            // // Border Radius
            // $this->add_responsive_control(
            //     'image_border_radius',
            //     [
            //         'label' => __( 'Border Radius', 'plugin-domain' ),
            //         'type' => Controls_Manager::DIMENSIONS,
            //         'size_units' => [ 'px', '%', 'em' ],
            //         'default' => [
            //             'top' => 0,
            //             'right' => 0,
            //             'bottom' => 0,
            //             'left' => 0,
            //         ],
            //         'selectors' => [
            //             '{{WRAPPER}} .blk-container .feature-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            //             '{{WRAPPER}} .blk-container .feature-img ' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            //         ],
            //     ]
            // );

            // // Box Shadow
            // $this->add_group_control(
            //     \Elementor\Group_Control_Box_Shadow::get_type(),
            //     [
            //         'name' => 'image_box_shadow',
            //         'label' => __( 'Box Shadow', 'plugin-domain' ),
            //         'selector' => '{{WRAPPER}} .blk-container .feature-img',
            //     ]
            // );

        $this->end_controls_section();
    }

    function reset_slide_index(){
        $this->slide_index = 0;
    }

    function slide_index(){
        return $this->slide_index;
    }
    function increment_slide_index($increased_by=1){
        $this->slide_index += $increased_by;
    }

}

Plugin::instance()->widgets_manager->register_widget_type( new MYEW_Advanced_Features_Widget() );
