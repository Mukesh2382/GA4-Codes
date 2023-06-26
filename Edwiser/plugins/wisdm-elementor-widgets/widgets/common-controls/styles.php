<?php

class CommanStyles{
    public static function button($widget,$options){
        $section_label = $options['section_label'];
        $prefix = $options['prefix'];
        $selector = $options['selector'];

        $widget->start_controls_section(
            $prefix.'_button_section',
			[
				'label' => __( $section_label, 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Typography
            $widget->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => $prefix.'_button_typography',
                    'label' => __( 'Typography', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} '.$selector,
                ]
            );
            // Color
            $widget->add_control(
                $prefix.'_button_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} '.$selector => 'color: {{VALUE}}',
                    ],
                    'default' => '#FFFFFF'
                ]
            );
            // Color On Hover
            $widget->add_control(
                $prefix.'_button_color_hover',
                [
                    'label' => __( 'Color on Hover', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        "{{WRAPPER}} {$selector}:hover" => 'color: {{VALUE}}',
                    ],
                    'default' => WDM_PRIMARY_COLOR
                ]
            );
            // Background Color
            $widget->add_control(
                $prefix.'_button_background_color',
                [
                    'label' => __( 'Background Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        "{{WRAPPER}} {$selector}" => 'background-color: {{VALUE}}',
                    ],
                    'default' => WDM_PRIMARY_COLOR
                ]
            );
            // Background  Color On Hover
            $widget->add_control(
                $prefix.'_button_background_color_hover',
                [
                    'label' => __( 'Background Color on Hover', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        "{{WRAPPER}} {$selector}:hover" => 'background-color: {{VALUE}}',
                    ],
                    'default' => '#FFFFFF'
                ]
            );
            // padding
            $widget->add_control(
                $prefix.'_button_padding',
                [
                    'label' => __( 'Padding', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'default' => [
                        'top' => 10,
                        'right' => 10,
                        'bottom' => 10,
                        'left' => 10,
                    ],
                    'selectors' => [
                        "{{WRAPPER}} {$selector}" => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            // Border Type
            $widget->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => $prefix.'_button_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => "{{WRAPPER}} {$selector}"
                ]
            );
        $widget->end_controls_section();
    }

    public static function box($widget,$options){
        $section_label = $options['section_label'];
        $prefix = $options['prefix'];
        $selector = $options['selector'];
        $widget->start_controls_section(
            $prefix.'_box_section',
			[
				'label' => __( $section_label, 'wisdm-elementor-widgets' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
            // Color
            $widget->add_control(
                $prefix.'_box_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} '.$selector => 'color: {{VALUE}}',
                    ],
                ]
            );
            // Background Color
            $widget->add_control(
                $prefix.'_box_background_color',
                [
                    'label' => __( 'Background Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        "{{WRAPPER}} {$selector}" => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            // padding
            $widget->add_control(
                $prefix.'_box_padding',
                [
                    'label' => __( 'Padding', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'default' => [
                        'top' => 10,
                        'right' => 10,
                        'bottom' => 10,
                        'left' => 10,
                    ],
                    'selectors' => [
                        "{{WRAPPER}} {$selector}" => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

             // Margin
             $widget->add_control(
                $prefix.'_box_margin',
                [
                    'label' => __( 'Margin', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%', 'em' ],
                    'default' => [
                        'top' => 10,
                        'right' => 10,
                        'bottom' => 10,
                        'left' => 10,
                    ],
                    'selectors' => [
                        "{{WRAPPER}} {$selector}" => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
            // Border Type
            $widget->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => $prefix.'_box_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => "{{WRAPPER}} {$selector}"
                ]
            );
        $widget->end_controls_section();
    }
}