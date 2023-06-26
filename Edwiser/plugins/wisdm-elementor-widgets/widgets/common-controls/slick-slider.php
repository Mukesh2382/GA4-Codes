<?php

class SlickSlider{
    private static $instance;

    public static function default_value($slick_options,$key){
        $default = [
            'slider_infinite_looping' => 'no',

            'slider_visible_slides' => '3',
            'slider_visible_slides_tablet' => '2',
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

        if(isset($slick_options[$key])){
            return $slick_options[$key];
        }

        if(isset($default[$key])){
            return $default[$key];
        }
        return false;
    }
    
    // Add Slider configuration controls
    public static function slider_configuration($widget,$slick_options){
        // Slider Configuration
        $widget->start_controls_section(
            'slider_configurataion',
            [
                'label' => __( 'Slider Configuration', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
            // Infinite
            $widget->add_responsive_control(
                'slider_infinite_looping',
                [
                    'label' => __( 'Infinite Looping', 'wisdm-elementor-widgets' ),
                    'description' => 'Enable/Disable Infinite loop sliding',
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Enable', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'Disable', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes',
                    'default' => self::default_value($slick_options,'slider_infinite_looping'),
                ]
            );
            // Slider Visible Slides
            $widget->add_responsive_control(
                'slider_visible_slides',
                [
                    'label' => __( 'Visible slides', 'wisdm-elementor-widgets' ),
                    'description' => 'Default visible slides on slider',
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'min' => '1',
                    'max' => '6',
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                    'desktop_default' => self::default_value($slick_options,'slider_visible_slides'),
                    'tablet_default' => self::default_value($slick_options,'slider_visible_slides_tablet'),
                    'mobile_default' =>self::default_value($slick_options,'slider_visible_slides_mobile')
                ]
            );

            // Slider Arrows
            $widget->add_responsive_control(
                'slider_show_arrows',
                [
                    'label' => __( 'Show Arrows', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes',
                    'desktop_default' => self::default_value($slick_options,'slider_show_arrows'),
                    'tablet_default' => self::default_value($slick_options,'slider_show_arrows_tablet'),
                    'mobile_default' => self::default_value($slick_options,'slider_show_arrows_mobile'),
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                ]
            );

            // Slider Center Mode
            $widget->add_responsive_control(
                'slider_center_mode',
                [
                    'label' => __( 'Center Mode', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'On', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'Off', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes',
                    'desktop_default' => self::default_value($slick_options,'slider_center_mode'),
                    'tablet_default' => self::default_value($slick_options,'slider_center_mode_tablet'),
                    'mobile_default' => self::default_value($slick_options,'slider_center_mode_mobile'),
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                ]
            );

            // Slider Visible Slides
            $widget->add_responsive_control(
                'slider_center_padding',
                [
                    'label' => __( 'Center Padding (px)', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'min' => '1',
                    'max' => '20',
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                    'desktop_default' =>self::default_value($slick_options,'slider_center_padding'),
                    'tablet_default' =>self::default_value($slick_options,'slider_center_padding_tablet'),
                    'mobile_default' =>self::default_value($slick_options,'slider_center_padding_mobile')
                ]
            );

            // Slider Breakpoint
            $widget->add_responsive_control(
                'slider_breakpoint',
                [
                    'label' => __( 'Breakpoint', 'wisdm-elementor-widgets' ),
                    'description' => 'To disable set this to 0 ',
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'min' => '1',
                    'max' => '20',
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                    'desktop_default' =>self::default_value($slick_options,'slider_breakpoint'),
                    'tablet_default' =>self::default_value($slick_options,'slider_breakpoint_tablet'),
                    'mobile_default' =>self::default_value($slick_options,'slider_breakpoint_mobile'),
                ]
            );

            // Slider dots
            $widget->add_responsive_control(
                'slider_show_dots',
                [
                    'label' => __( 'Show dots', 'wisdm-elementor-widgets' ),
                    'description' => 'Show/Hide Dots below slider',
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes',
                    'desktop_default' => self::default_value($slick_options,'slider_show_dots'),
                    'tablet_default' => self::default_value($slick_options,'slider_show_dots_tablet'),
                    'mobile_default' => self::default_value($slick_options,'slider_show_dots_mobile'),
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                ]
            );

            // Slider Autoplay
            $widget->add_responsive_control(
                'slider_autoplay',
                [
                    'label' => __( 'Autoplay', 'wisdm-elementor-widgets' ),
                    'description' => 'Enable/Disable Autoplay',
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Yes', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'No', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes',
                    'desktop_default' => self::default_value($slick_options,'slider_autoplay'),
                    'tablet_default' => self::default_value($slick_options,'slider_autoplay_tablet'),
                    'mobile_default' => self::default_value($slick_options,'slider_autoplay_mobile'),
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                ]
            );

            // Slider Autoplay Speed
            $widget->add_responsive_control(
                'slider_autoplay_speed',
                [
                    'label' => __( 'Autoplay Speed', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'min' => '100',
                    'max' => '10000',
                    'step' => '100',
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                    'desktop_default' =>self::default_value($slick_options,'slider_autoplay_speed'),
                    'tablet_default' =>self::default_value($slick_options,'slider_autoplay_speed_tablet'),
                    'mobile_default' =>self::default_value($slick_options,'slider_autoplay_speed_mobile'),
                ]
            );

        $widget->end_controls_section();
    }

    // fetch slider atributes
    public static function get_slider_attributes($settings){
        $slider_data_attr_array = [
            'slider_infinite_looping' => $settings['slider_infinite_looping'],
            'slider_center_mode' => $settings['slider_center_mode'],
            'slider_visible_slides' => $settings['slider_visible_slides'],
            'slider_visible_slides_tablet' => $settings['slider_visible_slides_tablet'],
            'slider_visible_slides_mobile' => $settings['slider_visible_slides_mobile'],
            'slider_show_arrows' => $settings['slider_show_arrows'],
            'slider_show_arrows_tablet' => $settings['slider_show_arrows_tablet'],
            'slider_show_arrows_mobile' => $settings['slider_show_arrows_mobile'],
            'slider_center_mode_tablet' => $settings['slider_center_mode_tablet'],
            'slider_center_mode_mobile' => $settings['slider_center_mode_mobile'],
            'slider_center_padding' => $settings['slider_center_padding'],
            'slider_center_padding_tablet' => $settings['slider_center_padding_tablet'],
            'slider_center_padding_mobile' => $settings['slider_center_padding_mobile'],
            'slider_breakpoint' => $settings['slider_breakpoint'],

            'slider_breakpoint_tablet' => $settings['slider_breakpoint_tablet'],
            'slider_breakpoint_mobile' => $settings['slider_breakpoint_mobile'],

            'slider_show_dots' => $settings['slider_show_dots'],
            'slider_show_dots_tablet' => $settings['slider_show_dots_tablet'],
            'slider_show_dots_mobile' => $settings['slider_show_dots_mobile'],
            'slider_autoplay_speed' => $settings['slider_autoplay_speed'],
            'slider_autoplay_speed_tablet' => $settings['slider_autoplay_speed_tablet'],
            'slider_autoplay_speed_mobile' => $settings['slider_autoplay_speed_mobile'],
            'slider_autoplay' => $settings['slider_autoplay'],
            'slider_autoplay_tablet' => $settings['slider_autoplay_tablet'],
            'slider_autoplay_mobile' => $settings['slider_autoplay_mobile'],
        ];
        $slider_data_attrs = "";
        foreach($slider_data_attr_array as $attribute => $value){
            $slider_data_attrs .= " data-$attribute='$value' ";
        }
        return $slider_data_attrs;
    }

}





