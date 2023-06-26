<?php
namespace Elementor;
class Wisdm_Pricing_Table_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-pricing-table-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Pricing Table', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script','wdm-pricing-table-script'
        ];
    }

    public function get_icon() {
        return 'eicon-price-table';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->pricing_plans_section();
        $this->features_section();
        $this->style_tab();
    }

    function pricing_plans_section(){
        $this->start_controls_section(
            'pricing_plans_section',
            [
                'label' => __( 'Pricing plans settings', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
            $downloads = \WisdmEW_Edd::get_downloads_with_variables();

            $column_options = $this->fetch_columns();

            $columns = new \Elementor\Repeater();
            // Index
            $columns->add_control(
                'column_index',
                [
                    'label' => __( 'Select Pricing Column', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $column_options,
                    'default' => "1"
                ]
            );
            // Title
            $columns->add_control(
                'column_title',
                [
                    'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Column title', 'wisdm-elementor-widgets' ),
                    'label_block' => true
                ]
            );
            // Description
            $columns->add_control(
                'column_description',
                [
                    'label' => __( 'Description', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'rows' => 5,
                    'default' => __( 'Default description', 'wisdm-elementor-widgets' ),
                    'placeholder' => __( 'Type your description here', 'wisdm-elementor-widgets' ),
                ]
            );
            // Show Popular Ribbon
            $columns->add_control(
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
            // Popular Ribbon Text
            $columns->add_control(
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
            ## Annual Options
            $columns->add_control(
                'annual_options',
                [
                    'label' => __( 'Annual Options', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                ]
            );
            // Annual - Product
            $columns->add_control(
                'annual_product',
                [
                    'label' => __( 'Select Product', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT2,
                    'label_block' => true,
                    'options' => $downloads,
                ]
            );
            // Annual - Sale Price
            $columns->add_control(
                'annual_sale_price',
                [
                    'label' => __( 'Sale price', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'default' => 0,
                ]
            );
            // Annual - Regular Price
            $columns->add_control(
                'annual_regular_price',
                [
                    'label' => __( 'Regular price', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'default' => 0,
                ]
            );
            // Annual - Button Title
            $columns->add_control(
                'annual_price_btn_title',
                [
                    'label' => __( 'Button Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                ]
            );
            # Lifetime Options
            $columns->add_control(
                'lifetime_options',
                [
                    'label' => __( 'Lifetime Options', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );
            // Lifetime - Product
            $columns->add_control(
                'lifetime_product',
                [
                    'label' => __( 'Select Product', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT2,
                    'label_block' => true,
                    'options' => $downloads,
                ]
            );
            // Lifetime - Sale Price
            $columns->add_control(
                'lifetime_sale_price',
                [
                    'label' => __( 'Sale price', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'default' => 0,
                ]
            );
            // Lifetime - Regular Price
            $columns->add_control(
                'lifetime_regular_price',
                [
                    'label' => __( 'Regular price', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'default' => 0,
                ]
            );
            // Lifetime - Button Title
            $columns->add_control(
                'lifetime_price_btn_title',
                [
                    'label' => __( 'Button Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                ]
            );
            
            $this->add_control(
                "pricing_columns",
                [
                    'label' => __( 'Pricing columns', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $columns->get_controls(),
                    'default' => [],
                    'title_field' => "Col {{{column_index}}} - {{{ column_title.substring(0,24) }}}",
                ]
            );

            // Advance Options
            $this->add_control(
                'adv_pricing_options',
                [
                    'label' => __( 'Advance Options', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );
            // Default toggle option
            $this->add_control(
                'default_toggle_option',
                [
                    'label' => __( 'Default Plan Duration', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        'annual' => 'Annual',
                        'lifetime' => 'Lifetime'
                    ],
                    'default' => 'annual',
                ]
            );

            // Toggle text for annual plan
            $this->add_control(
                'toggle_text_annual',
                [
                    'label' => __( 'Toggle text for annual plan', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'Annual',
                ]
            );

            // Toggle text for lifetime plan
            $this->add_control(
                'toggle_text_lifetime',
                [
                    'label' => __( 'Toggle text for lifetime plan', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'Lifetime',
                ]
            );

            // Note
            $this->add_control(
                'pricing_note',
                [
                    'label' => __( 'Price tip', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'rows' => 3,
                    'default' => __( '', 'wisdm-elementor-widgets' ),
                    'placeholder' => __( 'Enter note', 'wisdm-elementor-widgets' )
                ]
            );
            // Highlighted Plan
            $this->add_control(
                "highlighted_plan",
                [
                    'label' => __( 'Highlighted Plan', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => $this->fetch_columns()
                ]
            );

        $this->end_controls_section();
    }

    function features_section(){
        $this->start_controls_section(
            'features_section',
            [
                'label' => __( 'Features settings', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        # General Features Settings
        $this->add_control(
            'general_features_settings',
            [
                'label' => __( 'General Features Settings', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        # General Features Visible
        $this->add_control(
            'general_features_visible',
            [
                'label' => __( 'Default Visible Count', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 2,
                'description' => 'Set this to 0 to visible all features'
            ]
        );

        $general_features = new \Elementor\Repeater();
        // General Features  - Title
        $general_features->add_control(
            "general_feature_title",
            [
                'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Feature Title',
                'label_block' => true
            ]
        );
        // General Features  - SubTitle
        $general_features->add_control(
            "general_feature_subtitle",
            [
                'label' => __( 'Subtitle', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Feature Subtitle',
                'label_block' => true
            ]
        );
        // General Features  - Description
        $general_features->add_control(
            "general_feature_desc",
            [
                'label' => __( 'Description', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 5,
                'default' => __( 'Enter Description', 'wisdm-elementor-widgets' ),
                'placeholder' => __( 'Enter Description', 'wisdm-elementor-widgets' )
            ]
        );
        // General Features  - Applicable For
        $general_features->add_control(
            "general_feature_applicable_for",
            [
                'label' => __( 'Applicable for', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->fetch_columns(),
                'multiple' => true,
                'label_block' => true
            ]
        );
        // General Features
        $this->add_control(
            "general_features",
            [
                'label' => __( 'Features', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $general_features->get_controls(),
                'default' => [],
                'title_field' => "Features - {{{ general_feature_title.substring(0,24) }}}",
            ]
        );

        # Advanced Features Settings
        $this->add_control(
            'advanced_features_settings',
            [
                'label' => __( 'Advanced Features', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $advanced_features = new \Elementor\Repeater();

        // Advanced Features  - Title
        $advanced_features->add_control(
            "advanced_feature_title",
            [
                'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Feature Title',
                'label_block' => true
            ]
        );
          // Advanced Features  - Type
          $advanced_features->add_control(
            "advanced_feature_type",
            [
                'label' => __( 'Feature Type', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'main' => 'Main Feature',   
                    'subfeature' => 'Sub feature'
                ],
                'default' => 'main',
            ]
        );
        
        // Advanced Features  - SubTitle
        $advanced_features->add_control(
            "advanced_feature_subtitle",
            [
                'label' => __( 'Subtitle', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Feature Subtitle',
                'label_block' => true,
                'condition' => [
                    'advanced_feature_type' => 'subfeature'
                ]
            ]
        );
        // Advanced Features  - Description
        $advanced_features->add_control(
            "advanced_feature_desc",
            [
                'label' => __( 'Description', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 5,
                'default' => __( 'Enter Description', 'wisdm-elementor-widgets' ),
                'placeholder' => __( 'Enter Description', 'wisdm-elementor-widgets' ),
                'condition' => [
                    'advanced_feature_type' => 'subfeature'
                ]
            ]
        );
      
        // Advanced Features  - Applicable For
        $advanced_features->add_control(
            "advanced_feature_applicable_for",
            [
                'label' => __( 'Applicable for', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->fetch_columns(),
                'label_block' => true,
                'multiple' => true,
                'condition' => [
                    'advanced_feature_type' => 'main'
                ]
            ]
        );

        // Advanced Features  - Visible
        $advanced_features->add_control(
            "advanced_feature_visible",
            [
                'label' => __( 'Subfeature visible count', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => '2',
                'condition' => [
                    'advanced_feature_type' => 'main'
                ]
            ]
        );

        // Advanced Features  - Annual Price
        $advanced_features->add_control(
            "advanced_feature_annual_price",
            [
                'label' => __( 'Annual Price', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => '0',
                'condition' => [
                    'advanced_feature_type' => 'main'
                ]
            ]
        );

        // Advanced Features  - Lifetime Price
        $advanced_features->add_control(
            "advanced_feature_lifetime_price",
            [
                'label' => __( 'Lifetime Price', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => '0',
                'condition' => [
                    'advanced_feature_type' => 'main'
                ]
            ]
        );

        // Advanced Features
        $this->add_control(
            "advanced_features",
            [
                'label' => __( 'Features', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $advanced_features->get_controls(),
                'default' => [],
                'title_field' => "{{{ (advanced_feature_type == 'main') ? '* ' : '-- ' }}} {{{ advanced_feature_title.substring(0,24) }}}",
            ]
        );

        $this->end_controls_section();
    }

    private function style_tab() {
        // Pricing Button Section
        // $this->pricing_button_style();
        // Advanced Pricing Button Section
        // $this->adv_pricing_button_style();

        
    }

    private function pricing_button_style(){
        $this->start_controls_section(
            'pricing_btn_style_section',
            [
                'label' => __( 'Pricing Button', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
            // Pricing Button Color
            $this->add_control(
                'pricing_btn_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wdm-pricing-button' => 'color: {{VALUE}}',
                    ],
                ]
            );
            // Pricing Button Color on hover
            $this->add_control(
                'pricing_btn_color_on_hover',
                [
                    'label' => __( 'Color on hover', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wdm-pricing-button:hover' => 'color: {{VALUE}}',
                    ],
                ]
            );
            // Pricing Button Background Color
            $this->add_control(
                'pricing_btn_background_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wdm-pricing-button' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            // Pricing Button Background Color on hover
            $this->add_control(
                'pricing_btn_background_color_on_hover',
                [
                    'label' => __( 'Color on hover', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .wdm-pricing-button:hover' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            // Pricing Button Border
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'pricing_btn_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wdm-pricing-button',
                ]
            );
            // Pricing Button Border On Hover
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'pricing_btn_border_on_hover',
                    'label' => __( 'Border On Hover', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wdm-pricing-button:hover',
                ]
            );
            // Box Shadow
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'pricing_btn_shadow',
                    'label' => __( 'Shadow', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wdm-pricing-button',
                ]
            );

            // Box Shadow On Hover
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'pricing_btn_shadow_on_hover',
                    'label' => __( 'Shadow On Hover', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .wdm-pricing-button:hover',
                ]
            );
        $this->end_controls_section();
    }

    private function adv_pricing_button_style(){
        $this->start_controls_section(
            'adv_price_btn_style_section',
            [
                'label' => __( 'Highlighted Pricing Button', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
            // Pricing Button Color
            $this->add_control(
                'adv_price_btn_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .ups-col.highlighted .wdm-pricing-button' => 'color: {{VALUE}}',
                    ],
                ]
            );
            // Pricing Button Color on hover
            $this->add_control(
                'adv_price_btn_color_on_hover',
                [
                    'label' => __( 'Color on hover', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .ups-col.highlighted .wdm-pricing-button:hover' => 'color: {{VALUE}}',
                    ],
                ]
            );
            // Pricing Button Background Color
            $this->add_control(
                'adv_price_btn_background_color',
                [
                    'label' => __( 'Color', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .ups-col.highlighted .wdm-pricing-button' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            // Pricing Button Background Color on hover
            $this->add_control(
                'adv_price_btn_background_color_on_hover',
                [
                    'label' => __( 'Color on hover', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .ups-col.highlighted .wdm-pricing-button:hover' => 'background-color: {{VALUE}}',
                    ],
                ]
            );
            // Pricing Button Border
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'adv_price_btn_border',
                    'label' => __( 'Border', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .ups-col.highlighted .wdm-pricing-button',
                ]
            );
            // Pricing Button Border On Hover
            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'adv_price_btn_border_on_hover',
                    'label' => __( 'Border On Hover', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .ups-col.highlighted .wdm-pricing-button:hover',
                ]
            );
            // Box Shadow
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'adv_price_btn_shadow',
                    'label' => __( 'Shadow', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .ups-col.highlighted .wdm-pricing-button',
                ]
            );

            // Box Shadow On Hover
            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'adv_price_btn_shadow_on_hover',
                    'label' => __( 'Shadow On Hover', 'wisdm-elementor-widgets' ),
                    'selector' => '{{WRAPPER}} .ups-col.highlighted .wdm-pricing-button:hover',
                ]
            );
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        if(!empty($settings)){
            $errors = new \stdClass;
            $pricing = new \stdClass;
            $general_features = new \stdClass;
            $pricing->pricing_columns = [];
            $errors->pricing = [];
            $errors->features = [];

            $pricing->default_toggle_option = trim($settings['default_toggle_option']);
            $pricing->toggle_text_annual    = trim($settings['toggle_text_annual']);
            $pricing->toggle_text_lifetime  = trim($settings['toggle_text_lifetime']);
            $pricing->pricing_note          = trim($settings['pricing_note']);
            $pricing->highlighted_plan      = $settings['highlighted_plan'];

            $pricing_columns = $settings['pricing_columns'];
            if(!empty($pricing_columns)){
                foreach($pricing_columns as $column){
                    $ci = $column['column_index'];
                    $column_title = $column['column_title'];
                    if(!isset($pricing->pricing_columns[$ci])){
                        $pricing->pricing_columns[$ci] = $column;
                        $pricing->pricing_columns[$ci]['highlighted'] =
                            ($pricing->highlighted_plan == $ci);
                    }
                    else{
                        $errors->pricing[] = "Column #$ci is duplicate. Please change column id for '$column_title' plan";
                    }
                }
            }
            else{
                $errors->pricing[] = "Add Pricing Plans";
            }

            $general_features_list = $settings['general_features'];
            $general_features->general_features_visible = $settings['general_features_visible'];
            $general_features->features = [];

            if(!empty($general_features_list)){
                foreach($general_features_list as $feature){
                    $feature_title = $feature['general_feature_title'];
                    $applicable_for = $feature['general_feature_applicable_for'];
                    if(!empty($applicable_for)){
                        $general_features->features[] = $feature;
                    }
                    else{
                        $errors->features[] = "General Feature ($feature_title) is not belong to any pricing plan. Please assign pricing plan or remove this feature";
                    }
                }
            }

            $advanced_features_list = $settings['advanced_features'];
            $advanced_features = [];

            if(!empty($advanced_features_list)){
                $last_parent = -1; 
                foreach($advanced_features_list as $feature){
                    $feature_title = $feature['advanced_feature_title'];
                    $applicable_for = $feature['advanced_feature_applicable_for'];
                    $type = $feature['advanced_feature_type'];
                    if($type == 'subfeature' && $last_parent === -1){
                        $errors->features[] = "No Parent feature found for ($feature_title)";
                        continue; // skip invalid cases
                    }
                    if($type == 'main'){
                        if(empty($applicable_for)){
                            $errors->features[] = "Advanced Feature ($feature_title) is not belong to any pricing plan. Please assign pricing plan or remove this feature";
                        }
                        $last_parent++;
                        $advanced_features[$last_parent] = $feature;
                        $advanced_features[$last_parent]['features'] = [];
                    }
                    else{
                        $advanced_features[$last_parent]['features'][] = $feature;
                    }
                }
            }
        
        }
        $this->load_table($advanced_features,$general_features,$pricing);
    }

    function load_table($advanced_features,$general_features,$pricing){
        $total_pricing_plans = count($pricing->pricing_columns);
        ?>
        <div class='wisdm-pricing-table'>
            <div id="ups-main-id" data-default-toggle='<?php echo $pricing->default_toggle_option;?>'
                class="ups hide-m" 
                style="max-width: 100%; text-align: center; color: #444; letter-spacing: -.04px;">
                <!-- Pricing Sticky Header Menu Start-->
                <?php $this->pricing_sticky_header($pricing,count($pricing->pricing_columns)); ?>

                <!-- Pricing table start Desktop -->
                <div class="ups-table ups-table-<?php echo $total_pricing_plans;?>">
                    <div class="ups-column ups-col-1">
                        <div class="ups-plan-w empty">
                            <div id="ups-plan-w-toggle" class="ups-plan-duration">
                                <?php $this->annual_lifetime_toggle( $pricing); ?>
                            </div>
                            <?php 
                            if(!empty($pricing->pricing_note)){ 
                                $pricing_table_price_tip = apply_filters( 'pricing_table_price_tip', $pricing->pricing_note, $pricing);
                                if(!empty($pricing_table_price_tip)){
                                    echo "<div class='price-tip'>" . $pricing_table_price_tip . "</div>";
                                }
                            } 
                            ?>
                            <?php do_action('pricing_sale_strip_start'); ?>
                        </div>
                        <div class="ups-col-inr">
                            <?php 
                                $this->render_general_features($general_features); 
                                $this->render_advanced_features($advanced_features,count($general_features->features)); 
                            ?>
                        </div>
                        <div class="ups-col-btn-w"></div>
                    </div>
                    
                    <?php foreach ($pricing->pricing_columns as $id => $column) {
                        $highlighted_class = ($column['highlighted']) ? 'highlighted': '';
                        $popular_class = ($column['show_ribbon']) ? 'ups-popular': '';
                        ?>
                        <div class="ups-column ups-col <?php echo "$highlighted_class $popular_class" ?>">
                            <?php if(($column['show_ribbon'])) { ?>
                            <span class="popular-plan-col"><?php echo $column['ribbon_text']; ?></span>
                            <?php } ?>
                            <?php $this->ui_pricing_plan($column); ?>
                            <div class="ups-col-inr">
                                <?php  $this->pricing_ticks($advanced_features,$general_features,$column); ?>
                            </div>
                            <div class="ups-col-btn-w"> 
                                <?php  $this->get_action_buttons($column); ?>
                            </div>
                        </div>
                        <?php
                    }?>
                </div>
            </div>
            <div class="pricing-section-placeholder"></div>
            <!-- Pricing table start Mobile -->
            <?php  $this->ui_pricing_section_mobile($advanced_features,$general_features,$pricing); ?>
        </div>
        <?php
    }

    function pricing_ticks($advanced_features,$general_features,$column){
        $imageDir = WDM_WIDGETS_PLUGIN_PATH . "assets/wdm-pricing-table/images/";
        $visible_features_count = $general_features->general_features_visible;
        $visible = array_slice($general_features->features,0,$visible_features_count);
        $hidden = array_slice($general_features->features,$visible_features_count);
        $hidden_count = count($hidden);
        $feature_index = 0;
        $column_index = $column['column_index'];
        $highlighted = $column['highlighted'];
        // general visible feature
        foreach ($visible as $key => $feature) {
            $feature_index++;
            $applicable = !empty($feature['general_feature_applicable_for']) && in_array($column_index,$feature['general_feature_applicable_for']);
            $this->ui_pricing_tick($feature_index, $applicable, $highlighted);
        }
        // general hidden feature
        if($hidden_count >= 1) {
            ?>
            <div class="ups-viewmore-w empty"> 
                <span class="ups-viewmore-empty"></span> 
            </div>
            <div class="ups-hide-w hide">
                <?php
                foreach ($hidden as $key => $feature) {
                    $feature_index++;
                    $applicable = !empty($feature['general_feature_applicable_for']) && in_array($column_index,$feature['general_feature_applicable_for']);
                    $this->ui_pricing_tick($feature_index, $applicable, $highlighted);
                }
                ?>
                <div class="ups-viewmore-w empty hide"> 
                    <span class="ups-viewmore-empty"></span> 
                </div>
            </div>
            <?php
        }
        // Advanced features
        $index = 0;
        foreach ($advanced_features as $key => $feature) {
            $feature_index++;
            $applicable = !empty($feature['advanced_feature_applicable_for']) && in_array($column_index,$feature['advanced_feature_applicable_for']);
            $this->ui_pricing_tick($feature_index, $applicable, $highlighted);

            $subfeatures = $feature['features'];

            if(!empty($subfeatures)){
                $visible_features_count  = $feature['advanced_feature_visible'];
                $visible = array_slice($subfeatures,0,$visible_features_count);
                $hidden = array_slice($subfeatures,$visible_features_count);
                $hidden_count = count($hidden);
                foreach ($visible as $key => $feature) {
                    $feature_index++;
                    $this->ui_pricing_tick($feature_index, $applicable, $highlighted);
                }
                if ($hidden_count >= 1){
                ?>
                    <div class="ups-viewmore-w1 empty ups-hide-w-subfeatures-<?php echo $index; ?>"> 
                        <span class="ups-viewmore-empty"></span> 
                    </div>
                    <div class="ups-hide-w1 ups-hide-w-subfeatures-<?php echo $index; ?> hide">
                        <?php
                        foreach ($hidden as $key => $feature) {
                            $feature_index++;
                            $this->ui_pricing_tick($feature_index, $applicable, $highlighted);
                        }
                        ?>
                        <div class="ups-viewmore-w1 empty"> 
                            <span class="ups-viewmore-empty"></span> 
                        </div>
                    </div>
                <?php
                }
            }
            $index++;
        }
    }

    protected function _content_template() {

    }

    public function ui_pricing_plan($column){
        $lifetime_sale_price = trim($column['lifetime_sale_price']);
        $annual_sale_price = trim($column['annual_sale_price']);
        $lifetime_regular_price = trim($column['lifetime_regular_price']);
        $annual_regular_price = trim($column['annual_regular_price']);

        $lifetime_sale_price_text = ($lifetime_sale_price == 0) ? "FREE" : "$".$lifetime_sale_price; 
        $annual_sale_price_text = ($annual_sale_price == 0) ? "FREE" : "$".$annual_sale_price; 
        $lifetime_regular_price_text = ($lifetime_regular_price == 0) ? "" : "$".$lifetime_regular_price; 
        $annual_regular_price_text = ($annual_regular_price == 0) ? "" : "$".$annual_regular_price; 

        $annual_discount = '';
        $lifetime_discount = '';
        if($lifetime_regular_price > 0){
            $life_disc = 100 - round( ($lifetime_sale_price/$lifetime_regular_price) * 100 ) ;

            // $lifetime_discount = "You Save: $".($lifetime_regular_price - $lifetime_sale_price);
            $lifetime_discount = $life_disc . "% Off";
        }
        if($annual_regular_price > 0){
            // $annual_discount = "You Save: $". ($annual_regular_price - $annual_sale_price);

            $yr_disc = 100 -  round( ( $annual_sale_price / $annual_regular_price ) * 100 ) ;

            $annual_discount = $yr_disc . "% Off";


        }
     
        ?>
        <div class="upp-plan-w">
            <div class="upp-plan-w-inr">
                <div class="upp-title-w"> 
                    <span class="upp-title" >
                        <?php echo $column['column_title'];?>                  
                    </span>
                    <?php
                    if ( ! empty( $column['column_description'] ) ) {
                        ?>
                        <div class="upp-description">
                            <?php echo esc_html( $column['column_description'] ); ?>
                        </div>
                        <?php
                    }
                    ?> 
                </div>
                <div class="edwiser-pricing-sale-strip"> 
                    <div class="upp-price-w"> 
                        <?php if($annual_regular_price || $lifetime_regular_price) { ?>
                            <span class="upp-reg-price strike">
                                <span class="ups-toggle-txt" data-toggle-txt="<?php echo $lifetime_regular_price_text; ?>">
                                    <?php echo $annual_regular_price_text; ?>                              
                                </span> 
                            </span> 
                        <?php } ?>
                        <span class="upp-sale-price">
                            <span class="ups-toggle-txt" data-toggle-txt="<?php echo $lifetime_sale_price_text; ?>">
                                <?php echo $annual_sale_price_text; ?>                                    
                            </span> 
                        </span>
                    </div> 
                    <?php if(!empty($annual_discount)  || !empty($lifetime_discount)) { ?>
                        <span class="upp-price-diff-w">
                            <span class="upp-price-diff-inr">
                                <span class="ups-toggle-txt" data-toggle-txt="<?php echo $lifetime_discount; ?>">
                                <?php echo $annual_discount; ?>
                                </span> 
                            </span>
                        </span>
                    <?php } ?>
                </div>
                <div class="upp-license-w">
                    <div> </div>
                    <div> </div>
                </div>
            </div>
        </div>
        <?php
    }

    function fetch_columns(){
        $columns = [];
        for($i=1;$i<=4;$i++){
            $columns["$i"] = "Col $i";
        }
        return $columns;
    }

    function get_action_buttons($column){
        $annual_button = $this->get_action_button_details($column);
        $lifetime_button = $this->get_action_button_details($column,true);
        echo $this->get_action_button($annual_button , $lifetime_button->btn_text);
        echo $this->get_action_button($lifetime_button , $lifetime_button->btn_text);
    }

    public function get_action_button($product,$toggle_text){
        $download_id = $product->product_id;
        $text        = $product->btn_text; 
        $price_id    = $product->price_id; 
        $class       = $product->btn_class; 
        $price       = edd_get_price_option_amount($download_id, $price_id);
        $price_name  = edd_get_price_option_name($download_id, $price_id);
        
        $download = new \EDD_Download( $download_id );
        $eddDownload = edd_get_download($download_id);
        $file = $eddDownload->get_files();
        $file = array_shift($file);

        $checkout_url = edd_get_checkout_uri(); 
        $checkout_url = apply_filters('wdm_pricing_table_cta_url', $checkout_url,$download_id,$price_id);
        
        ob_start();
        if(!empty($download_id)){
            if ( edd_is_free_download($download->ID) ) { 
                if ( is_user_logged_in() ) { ?>
                    <span class="wdm-menu-btn-hollow-link">
                        <button id="wdm-free-download" class="ranjit-free-logged-in wdm-pricing-button <?php echo $class;?>" data-download-id="<?php echo $download->ID; ?>"><?php echo $text; ?></button>
                    </span>
                    <a id="download-edd-file" href="" target="_blank" download style="display:none">Free Download</a>
                <?php } else { ?>
                    <!-- Trigger/Open The Modal -->
                    <button data-modal-id='wdm-free-download-modal' class=" wdm-pricing-button modal-backdrop-btn <?php echo $class;?>"><?php echo $text; ?></button>
                    <input type="hidden" id="downloadid" name="downloadid" value="<?php echo $download->ID; ?>">
                    <!-- The Modal -->
                    <div id="wdm-free-download-modal" class=" Modal is-hidden is-visuallyHidden">
                        <!-- Modal content -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 style="color: #fff;margin: 0;">Login and get your plugin</h2>
                                <span class="close modal-backdrop-close-btn">&times;</span>
                            </div>
                            <div class="modal-body">
                                <?php echo do_shortcode('[nextend_social_login align="center" trackerdata='.$download->ID.']'); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php }
            else { ?>
                <form class="wdm-cta-buy-now-button" action="<?php echo $checkout_url; ?>" method="post">
                    <input type="hidden" name="edd_options[price_id][]" class="edd_price_option_<?php echo $download_id; ?>" value="<?php echo $price_id; ?>" data-price="<?php echo $price; ?>">
                    <meta itemprop="price" content="<?php echo $price; ?>">
                    <meta itemprop="priceCurrency" content="USD">
                    <input type="hidden" name="download_id" value="<?php echo $download_id; ?>">
                    <input type="hidden" name="edd_action" class="edd_action_input" value="add_to_cart">
                    <input type="hidden" name="edd_redirect_to_checkout" value="<?php echo $price_id; ?>">
                    <a class='tooltip'>
                        <button type="submit" 
                            class="wdm-pricing-button  <?php echo $class; ?> <?php echo "wdm-pricing-button-" . $price_id; ?>" 
                            style="width: auto;" 
                            name="edd_purchase_download"  
                            data-action="edd_add_to_cart" 
                            data-download-categories="[]" 
                            data-download-id="<?php echo $download_id; ?>" 
                            data-variable-price="yes" 
                            data-price-mode="<?php echo $price_name; ?>">
                            <span class="ups-toggle-txt" data-toggle-txt="<?php echo $toggle_text; ?>"><?php echo $text; ?></span>
                        </button>
                        <?php if($text == 'Get a Free Trial' || $toggle_text == 'Get a Free Trial' ){ ?>
                        <span class='tooltiptext'>Cancel anytime. You’ll only be charged on the 16th day.</span>
                        <?php } ?>
                    </a>
                </form>
                <?php
            }
        }
        $data = ob_get_clean();
        return $data;
    }

    function get_action_button_details($column, $is_lifetime = false){

        $col_btn_class = " alt-btn btn-secondary ";
        if($column['highlighted']){
            $col_btn_class = " alt-btn ";
        }
        if($is_lifetime){
            $col_btn_class .= " wdm-hide ";
        }

        if($is_lifetime){
            $product = trim($column['lifetime_product']);
            $custom_text = trim($column['lifetime_price_btn_title']);
        }
        else{
            $product = trim($column['annual_product']);
            $custom_text = trim($column['annual_price_btn_title']);
        }
        $product_id = explode("_",$product)[0];
        $price_id = isset(explode("_",$product)[1]) ? explode("_",$product)[1] : false;

        $free_trial = edd_recurring()->has_free_trial( $product_id, $price_id );
        $free_download = edd_is_free_download( $product_id, $price_id );

        if(!$custom_text || empty($custom_text)){
            if($free_trial){
                $text = "Get a Free Trial";
            }
            else if($free_download){
                $text = "Download Now";
            }
            else{
                $text = "Buy Now";
            }
        }
        else{
            $text = $custom_text;
        }   

        $result = new \stdClass;
        $result->product_id = $product_id;
        $result->price_id = $price_id;
        $result->free_trial = $free_trial;
        $result->free_download = $free_download;
        $result->btn_text = $text;
        $result->btn_class = $col_btn_class;
        return $result;
    }

    function render_general_features($general_features){
        $imageDir = WDM_WIDGETS_PLUGIN_PATH . "assets/wdm-pricing-table/images/";
        $visible_features_count = $general_features->general_features_visible;
        $visible = array_slice($general_features->features,0,$visible_features_count);
        $hidden = array_slice($general_features->features,$visible_features_count);
        $hidden_count = count($hidden);
        $feature_index = 0;
        foreach ($visible as $key => $feature) {
            $feature_index++;
        ?>
            <!-- Visible Feature -->
            <div class="ups-cell uprh ups-cell-<?php echo $feature_index; ?>" 
                data-highlight="ups-cell-<?php echo $feature_index; ?>">
                <div class="uprh-inr" style="min-width: 252.179px;">
                    <div class="uprh-t"><?php echo $feature['general_feature_title'];?></div>
                    <div class="uprh-info-w"> 
                        <img src="<?php echo $imageDir; ?>/info.png" class="uprh-info-i" alt="info icon" />
                        <div class="uprh-popup-w">
                            <div class="uprh-popup">
                                <div class="uprh-close">
                                    <img src="<?php echo $imageDir; ?>/close.svg" class="uprh-close-i" alt="close icon" />
                                </div>
                                <div class="uprh-popup-c">
                                    <div class="uprh-pc-t"> <?php echo $feature['general_feature_subtitle'];?> </div>
                                    <div class="uprh-pc-ds"> <?php echo $feature['general_feature_desc'];?> </div>
                                </div>
                            </div>
                            <span class="uprh-popup-arrow"></span> 
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }

        if($hidden_count >= 1) {
            ?>
            <!-- View more button -->
            <div class="ups-viewmore-w">
                <button class="ups-viewmore alt-btn"> +<?php echo $hidden_count;?> More </button>
            </div>

            <div class="ups-hide-w hide">
                <!-- Hidden Features -->
                <?php
                foreach ($hidden as $key => $feature) {
                    $feature_index++;
                    ?>
                    <div class="ups-cell uprh ups-cell-<?php echo $feature_index; ?>" 
                        data-highlight="ups-cell-<?php echo $feature_index; ?>">
                        <div class="uprh-inr" style="min-width: 252.179px;">
                            <div class="uprh-t"><?php echo esc_html( $feature['general_feature_title'] );?></div>
                            <div class="uprh-info-w"> 
                                <img src="<?php echo $imageDir; ?>/info.png" class="uprh-info-i" alt="info icon" />
                                <div class="uprh-popup-w">
                                    <div class="uprh-popup">
                                        <div class="uprh-close">
                                            <img src="<?php echo $imageDir; ?>/close.svg" class="uprh-close-i" alt="close icon" />
                                        </div>
                                        <div class="uprh-popup-c">
                                            <div class="uprh-pc-t"> <?php echo $feature['general_feature_subtitle'];?> </div>
                                            <div class="uprh-pc-ds"> <?php echo $feature['general_feature_desc'];?> </div>
                                        </div>
                                    </div> <span class="uprh-popup-arrow"></span> </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="ups-viewmore-w hide">
                    <button class="ups-viewmore alt-btn"> Show Less </button>
                </div>
            </div>

            <?php
        }
    }

    function render_advanced_features($advanced_features,$generate_features_count){
        $feature_index = $generate_features_count; 
        $mainfeature_index = 0;
        $imageDir = WDM_WIDGETS_PLUGIN_PATH . "assets/wdm-pricing-table/images/";
        foreach ($advanced_features as $key => $feature) {

            $feature_index++;
            $subfeatures = $feature['features'];
            $annual_price = $feature['advanced_feature_annual_price'];
            $lifetime_price = $feature['advanced_feature_lifetime_price'];
            ?>
            
            <div class="ups-cell uprh subfeatures-<?php echo $mainfeature_index; ?> feature_heading ups-cell-<?php echo $feature_index;?>" 
            data-highlight="ups-cell-<?php echo $feature_index;?>">
                <div class="uprh-inr" style="min-width: 252.179px;">
                    <div class="uprh-t"> <?php echo $feature['advanced_feature_title'];?>
                        <?php if($annual_price && $lifetime_price) { ?>
                        <span class="ups-toggle-txt" data-toggle-txt="- $<?php echo $lifetime_price; ?>">- $<?php echo $annual_price; ?></span> 
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php

            if(!empty($subfeatures)){
                $visible_features_count  = $feature['advanced_feature_visible'];
                $visible = array_slice($subfeatures,0,$visible_features_count);
                $hidden = array_slice($subfeatures,$visible_features_count);
                $hidden_count = count($hidden);

                foreach ($visible as $key => $subfeature) {
                    $feature_index++;
                ?>
                    <!-- Visible Feature -->
                    <div class="ups-cell uprh ups-cell-<?php echo $feature_index; ?>" 
                        data-highlight="ups-cell-<?php echo $feature_index; ?>">
                        <div class="uprh-inr" style="min-width: 252.179px;">
                            <div class="uprh-t"><?php echo $subfeature['advanced_feature_title'];?></div>
                            <div class="uprh-info-w"> 
                                <img src="<?php echo $imageDir; ?>/info.png" class="uprh-info-i" alt="info icon" />
                                <div class="uprh-popup-w">
                                    <div class="uprh-popup">
                                        <div class="uprh-close">
                                            <img src="<?php echo $imageDir; ?>/close.svg" class="uprh-close-i" alt="close icon" />
                                        </div>
                                        <div class="uprh-popup-c">
                                            <div class="uprh-pc-t"> <?php echo $subfeature['advanced_feature_subtitle'];?> </div>
                                            <div class="uprh-pc-ds"> <?php echo $subfeature['advanced_feature_desc'];?> </div>
                                        </div>
                                    </div>
                                    <span class="uprh-popup-arrow"></span> 
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }

                if($hidden_count >=1){
                    ?>
                    <!-- View More -->
                    <div class="uprh-more-w">
                        <div class="ups-viewmore-w1 ups-viewmore-w-subfeatures-<?php echo $mainfeature_index; ?> sub-feature-viewmore">
                            <button class="ups-viewmore1 ups-viewmore-subfeatures-<?php echo $mainfeature_index; ?> alt-btn"> 
                                +<?php echo $hidden_count; ?> more 
                            </button>
                        </div>
                    </div>

                    <div class="ups-hide-w1 hide ups-hide-w-subfeatures-<?php echo $mainfeature_index; ?>">
                        <?php
                        foreach ($hidden as $key => $subfeature) {
                            $feature_index++;
                            ?>
                            <!-- Advanced Subfeature -->
                            <div class="ups-cell uprh ups-cell-<?php echo $feature_index;?>" 
                                data-highlight="ups-cell-<?php echo $feature_index;?>">
                                <div class="uprh-inr" style="min-width: 252.179px;">
                                    <div class="uprh-t"> <?php echo $subfeature['advanced_feature_title'];?> </div>
                                    <div class="uprh-info-w"> 
                                        <img src="<?php echo $imageDir; ?>/info.png" class="uprh-info-i" alt="info icon" />

                                        <div class="uprh-popup-w">
                                            <div class="uprh-popup">
                                                <div class="uprh-close">
                                                    <img src="<?php echo $imageDir; ?>/close.svg" class="uprh-close-i" alt="close icon" />
                                                </div>
                                                <div class="uprh-popup-c">
                                                    <div class="uprh-pc-t"> <?php echo $subfeature['advanced_feature_subtitle'];?> </div>
                                                    <div class="uprh-pc-ds"> <?php echo $subfeature['advanced_feature_desc'];?> </div>
                                                </div>
                                            </div> 
                                            <span class="uprh-popup-arrow"></span> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <!-- Show less -->
                        <div class="ups-viewmore-w1 ups-viewmore-w-subfeatures-<?php echo $mainfeature_index; ?> sub-feature-viewmore hide">
                            <button class="ups-viewmore1 ups-viewmore-subfeatures-<?php echo $mainfeature_index; ?> alt-btn"> Show Less </button>
                        </div>
                    </div>
                <?php
                }
            }

            $mainfeature_index++;
        }
    }

    function pricing_sticky_header($pricing,$no_of_columns){
        $highlighted_plan = !empty($pricing->toggle_text_lifetime) ? $pricing->toggle_text_lifetime : 0;
        ?>
            <div id="header-pricing-w-id" class="header-pricing-w" style="display: none;">
                <h1 style="display:none; width: 100% !important; margin: 5px 0;"
                    class="pricing-table-heading"></h1>
                <div class="ups-table ups-table-<?php echo $no_of_columns;?>">
                    <div class="ups-column ups-col-1-sticky">
                        <div class="ups-plan-w empty-sticky">
                            <div class="ups-plan-duration">
                                <?php $this->annual_lifetime_toggle( $pricing); ?>
                            </div>
                        </div>
                    </div>
                    <?php 
                        $index = 0;
                        foreach ($pricing->pricing_columns as $column_id => $column) {
                                $highlighted_class = ($column['highlighted']) ? 'highlighted': '';
                                $data_highlight = ($column['highlighted']) ?  "data-highlight='$index'" : "";
                                $annual_sale_price = $column['annual_sale_price'];
                                $lifetime_sale_price = $column['lifetime_sale_price'];
                                $lifetime_sale_price = $column['lifetime_sale_price'];

                                $lifetime_regular_price = trim($column['lifetime_regular_price']);
                                $annual_regular_price = trim($column['annual_regular_price']);
                                $lifetime_regular_price_text = ($lifetime_regular_price == 0) ? "" : "$".$lifetime_regular_price; 
                                $annual_regular_price_text = ($annual_regular_price == 0) ? "" : "$".$annual_regular_price; 

                            ?>
                            <div class="ups-column ups-col ups-col-sticky <?php echo $highlighted_class; ?>" 
                                <?php echo $data_highlight; ?> >
                                <div class="upp-plan-w1" style="min-width: 219px;">
                                    <div class="upp-plan-w-inr">
                                        <div class="upp-title-w"> 
                                            <span class="upp-title upp-title-sticky" >
                                                <?php echo esc_html( $column['column_title'] );?>                 
                                            </span> 
                                        </div>
                                        <div class="upp-price-w1"> 
                                            <span class="regular-price-on-header upp-reg-price strike">
                                                <span class="ups-toggle-txt" data-toggle-txt="<?php echo $lifetime_regular_price_text; ?>">
                                                    <?php echo $annual_regular_price_text; ?>                              
                                                </span> 
                                            </span> 
                                            <span class="upp-sale-price">
                                                <span class="ups-toggle-txt" 
                                                    data-toggle-txt="$<?php echo $lifetime_sale_price; ?>">
                                                    $<?php echo $annual_sale_price; ?>
                                                </span> 
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $index++;
                        }
                    ?>
                </div>
            </div>
        <?php
    }

    function annual_lifetime_toggle( $pricing){
        $annual_toggle_text = !empty($pricing->toggle_text_annual) ? $pricing->toggle_text_annual : "Annual";
        $lifetime_toggle_text = !empty($pricing->toggle_text_lifetime) ? $pricing->toggle_text_lifetime : "Lifetime"; 
        ?>
        <div class="upspd-txt upspd-txt-1">
            <h5 class="upspd-h5 active"><?php echo $annual_toggle_text; ?></h5> 
        </div>
        <div class="upspd-switch-main">
            <input type="checkbox" class="upspd-checkbox">
            <label class="upspd-switch-label">
                <span class="upspd-slider" style='background-color:<?php echo WDM_PRIMARY_COLOR; ?>'></span>
            </label>
        </div>
        <div class="upspd-txt upspd-txt-2">
            <h5 class="upspd-h5"><?php echo $lifetime_toggle_text; ?></h5> 
        </div>
        <?php
    }

    function ui_pricing_tick($index, $applicable = true,$highlighted){
        $imageDir = WDM_WIDGETS_PLUGIN_PATH . "assets/wdm-pricing-table/images/";
        if($highlighted){
            $image = $imageDir . WDM_TICK_IMAGE_LG;
        }
        else{
            $image = $imageDir . WDM_TICK_IMAGE_SM;
        }
        ob_start();
            ?>
            <div class="ups-cell upt-w<?php (!$applicable) ? " empty" : ""; ?> ups-cell-<?php echo $index; ?>" data-highlight="ups-cell-<?php echo $index; ?>">
                <?php if ($applicable) : ?>
                    <span class="upt-icon-w">
                        <img src="<?php echo $image; ?>" alt="tick mark icon" class="up-icon">
                    </span>
                <?php endif; ?>
            </div>
            <?php
        echo ob_get_clean();
    }

    function ui_pricing_section_mobile($advanced_features,$general_features,$pricing){
        ?>
        <div class="hide-d">
            <div class="ups">
                <?php do_action('pricing_sale_strip_start_mobile');?>
                <div class="ups-plan-duration">
                    <?php $this->annual_lifetime_toggle($pricing); ?>
                </div>
                <?php 
                if(!empty($pricing->pricing_note)){ 
                    $pricing_table_price_tip = apply_filters( 'pricing_table_price_tip', $pricing->pricing_note, $pricing);
                    if(!empty($pricing_table_price_tip)){
                        echo "<div class='price-tip'>" . $pricing_table_price_tip . "</div>";
                    }
                } 
                ?>
                <div class="upsm hide-d" style="margin-top: 7px;">
                    <div class="upsm-inr price-slick">  
                        <?php
                        foreach($pricing->pricing_columns as $column){
                            ?>
                            <div class="up-plan">
                                <div class="upp-inr">
                                    <?php $this->ui_pricing_plan($column); ?>
                                    <div class="upp-viewmore">
                                        Features
                                    </div>
                                    <div class="upp-btn-w">
                                        <?php $this->get_action_buttons($column);?>
                                    </div>
                                    <div class="hide upp-popup-c">
                                        <div class="upsmdl-c-count">
                                            Features
                                        </div>
                                        <div class="ufq" role="tablist">
                                            <?php 
                                                // $this->ui_pricing_popup_content($general_features,$advanced_features,$column);
                                            ?>
                                        </div>
                                    </div>
                                </div> 
                            </div> 
                            <?php
                        }
                        ?>
                    </div> 
                </div> 

                <div class="upsm-notes-w">
                    <p class="ups-note"></p>
                </div>
            </div>
        </div>
        <?php
    }

    function ui_pricing_popup_content($general_features,$advanced_features,$column)
    {
        $features = [];
        $index = 0;
        $column_index = $column['column_index'];
        foreach($general_features->features as $feature){
            $applicable = in_array($column_index , $feature['general_feature_applicable_for']);
            if($applicable){
                $index++;
                $features[] = [
                    'index' => $index,
                    'title' => $feature['general_feature_title'],
                    'short_desc' => $feature['general_feature_subtitle'],
                    'parent_feautre' => false
                ];
            }
        }
        foreach($advanced_features as $feature){
            $subfeatures = $feature['features'];
            $parent_feautre = $feature['advanced_feature_title'];
            $applicable = in_array($column_index , $feature['advanced_feature_applicable_for']);
            if($applicable){
                foreach($subfeatures as $subfeature){
                    $index++;
                    $features[] = [
                        'index' => $index,
                        'title' => $subfeature['advanced_feature_title'],
                        'short_desc' => $subfeature['advanced_feature_subtitle'],
                        'parent_feautre' => $parent_feautre
                    ];
                }
            }
        }
        foreach($features as $feature){
            ?>
            <div class="ufq-itm">
                <div class="ufqi-title-w" role="tab" aria-controls="ufqi-c-<?php echo $index; ?>">
                    <h3 class="ufqi-th3"><?php echo $feature["title"] ?></h3>
                    <span class="ufqi-ico-w hide-m">
                        <i class="ufqi-ico closing fas fa-chevron-up"></i>
                        <i class="ufqi-ico opening fas fa-chevron-down"></i>
                    </span>
                    <span class="ufqi-ico-w hide-d">
                        <i class="ufqi-ico closing fas fa-angle-up"></i>
                        <i class="ufqi-ico opening fas fa-angle-down"></i>
                    </span>
                </div>
                <div id="ufqi-c-<?php echo $index; ?>" class="ufqi-c">
                    <?php
                        if ($feature["short_desc"]) {
                            echo $feature["short_desc"]; 
                        }  
                        if($feature["parent_feautre"]){
                            echo "br><br>Add-On: ." . $feature["parent_feautre"];
                        }
                    ?>
                </div>
            </div>
            <?php
        }
    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Pricing_Table_Widget() );
