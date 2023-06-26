<?php
namespace Elementor;
class Wisdm_Pricing_Table3_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-pricing-table3-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Pricing Table 3 - ESTK', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script','wdm-pricing-table-script'
        ];
    }

    /**
     * Load styles on frontend.
     */
    public function get_style_depends() {
        return [
            'wdm-pricing-table3-style',
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

    protected function _content_template() {
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
            // Annual Options
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
            // Lifetime Options
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

            // Price Tip below Annual-Lifetime Toggle.
            $this->add_control(
                'pricing_note_annual_lifetime',
                [
                    'label' => __( 'Price tip below Annual-Lifetime', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::CODE,
                    'rows' => 3,
                    'default' => __( '', 'wisdm-elementor-widgets' ),
                    'placeholder' => __( 'Enter note', 'wisdm-elementor-widgets' )
                ]
            );

            // Note
            $this->add_control(
                'pricing_note',
                [
                    'label' => __( 'Price tip', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::CODE,
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

        $general_features = new \Elementor\Repeater();
        // General Features  - Title
        $general_features->add_control(
            "general_feature_title",
            [
                'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                // 'type' => \Elementor\Controls_Manager::TEXT,
                'type' => \Elementor\Controls_Manager::CODE,
                'default' => 'Feature Title',
                'label_block' => true
            ]
        );
        // General Features  - SubTitle
        $general_features->add_control(
            "general_feature_subtitle",
            [
                'label' => __( 'Subtitle', 'wisdm-elementor-widgets' ),
                // 'type' => \Elementor\Controls_Manager::TEXT,
                'type' => \Elementor\Controls_Manager::CODE,
                'default' => 'Feature Subtitle',
                'label_block' => true
            ]
        );
        // General Features  - Annual Price
        $general_features->add_control(
            "general_feature_annual_price",
            [
                'label' => __( 'Annual Price', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
            ]
        );
        // General Features  - Lifetime Price
        $general_features->add_control(
            "general_feature_lifetime_price",
            [
                'label' => __( 'Lifetime Price', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
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
        if( ! empty( $settings ) ){
            $errors = new \stdClass;
            $pricing = new \stdClass;
            $general_features = new \stdClass;
            $pricing->pricing_columns = [];
            $errors->pricing = [];
            $errors->features = [];

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
        }
        $this->load_table($general_features,$pricing);
    }

    function load_table($general_features,$pricing){
        $total_pricing_plans = count($pricing->pricing_columns);
        $settings = $this->get_settings_for_display();
        ?>
        <div class='wisdm-pricing-table wpt-3-table-wrapper'>
            <div id="ups-main-id" 
                class="wpt-3-desktop ups hide-m" 
                style="max-width: 100%; text-align: center; color: #444; letter-spacing: -.04px;">

                <!-- Pricing table start Desktop -->
                <div class="ups-table ups-table-<?php echo $total_pricing_plans;?>">
                    <div class="wpt-3-ups-plan-w empty">
                        <div id="ups-plan-w-toggle" class="ups-plan-duration">
                            <?php $this->annual_lifetime_toggle( $pricing ); ?>
                        </div>
                        <?php
                        $pricing_tip_annual_lifetime = trim( $settings['pricing_note_annual_lifetime'] );
                        if ( ! empty( $pricing_tip_annual_lifetime ) ) :
                            echo '<div class="wpt-3-price-tip-annual-lifetime">' . wp_kses_post( $pricing_tip_annual_lifetime ) . '</div>';
                        endif;
                        ?>
                        <?php do_action('pricing_sale_strip_start'); ?>
                    </div>
                    <?php
                        $wpt_3_first_column = $pricing->pricing_columns[1];
                    ?>
                    <div class="wpt-3-upp-plan-w">
                        <div class="wpt-3-upp-plan-w-inr">
                            <div class="wpt-3-upp-title-w"> 
                                <span class="wpt-3-upp-title" >
                                    <?php echo $wpt_3_first_column['column_title'];?>                  
                                </span>
                                <?php
                                if ( ! empty( $wpt_3_first_column['column_description'] ) ) :
                                    ?>
                                    <div class="upp-description">
                                        <?php echo $wpt_3_first_column['column_description'];?> 
                                    </div>
                                    <?php
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="wpt-3-feature-price-wrapper">
                        <div class="wpt-3-features-col ups-column ups-col-1">
                            <div class="ups-col-inr">
                                <?php 
                                    $this->render_general_features_desktop($general_features); 
                                ?>
                            </div>
                            <div class="wpt-3-col-action-buttons-empty"></div>
                        </div>
                        
                        <?php foreach ($pricing->pricing_columns as $id => $column) {
                            $highlighted_class = ($column['highlighted']) ? 'highlighted': '';
                            $popular_class = ($column['show_ribbon']) ? 'ups-popular': '';
                            ?>
                            <div class="wpt-3-product-col ups-column ups-col <?php echo "$highlighted_class $popular_class" ?>">
                                <?php if(($column['show_ribbon'])) { ?>
                                <span class="popular-plan-col"><?php echo $column['ribbon_text']; ?></span>
                                <?php } ?>
                                <?php $this->ui_pricing_plan($column); ?>
                                <div class="ups-col-inr">
                                    <?php $this->show_feature_price($general_features,$column); ?>
                                </div>
                                <div class="wpt-3-col-action-buttons">
                                    <?php $this->show_main_product_annual_lifetime_price($column); ?>
                                    <?php $this->get_action_buttons($column); ?>
                                </div>
                            </div>
                            <?php
                        }?>
                    </div>
                </div>
            </div>
            <div class="pricing-section-placeholder"></div>
            <!-- Pricing table start Mobile -->
            <?php $this->ui_pricing_section_mobile($general_features,$pricing); ?>
            <?php 
            if(!empty($pricing->pricing_note)){ 
                $pricing_table_price_tip = apply_filters( 'pricing_table_price_tip', $pricing->pricing_note, $pricing);
                if(!empty($pricing_table_price_tip)){
                    echo "<div class='wpt-3-price-tip'>" . wp_kses_post( $pricing_table_price_tip ) . "</div>";
                }
            } 
            ?>
        </div>
        <?php
    }

    public function show_main_product_annual_lifetime_price($column) {
        $imageDirUrl            = WDM_WIDGETS_PLUGIN_PATH . 'assets/wdm-pricing-table3/images/';
        $annual_regular_price   = trim($column['annual_regular_price']);
        $annual_sale_price      = trim($column['annual_sale_price']);
        $lifetime_regular_price = trim($column['lifetime_regular_price']);
        $lifetime_sale_price    = trim($column['lifetime_sale_price']);

        $annual_regular_price_text   = ($annual_regular_price == 0) ? "" : "$".$annual_regular_price;
        $annual_sale_price_text      = ($annual_sale_price == 0) ? "FREE" : "$".$annual_sale_price; 
        $lifetime_regular_price_text = ($lifetime_regular_price == 0) ? "" : "$".$lifetime_regular_price;
        $lifetime_sale_price_text    = ($lifetime_sale_price == 0) ? "FREE" : "$".$lifetime_sale_price; 

        ?>
        <div class="wpt-3-edwiser-main-product-annual-lifetime-price"> 
            <div class="upp-price-w"> 
                <?php if($annual_regular_price || $lifetime_regular_price) { ?>
                    <span class="wpt-3-upp-reg-price-wrapper ">
                        <span class="wpt-3-reg-price-label">Total Price</span>
                        <span class="wpt-3-upp-reg-price strike ups-toggle-txt" data-toggle-txt="<?php echo $lifetime_regular_price_text; ?>">
                            <?php echo $annual_regular_price_text; ?>                              
                        </span> 
                    </span> 
                <?php } ?>
                <span class="wpt-3-upp-sale-price-wrapper">
                    <span class="wpt-3-sale-price-label">Our Price</span>
                    <span class="wpt-3-dollar-image-sale-price-wrapper">
                        <img class="wpt-3-dollar-image-sale-price" src="<?php echo esc_url( $imageDirUrl . 'dollar.gif' ); ?>" height="46px" width="46px"/>
                        <span class="wpt-3-upp-sale-price ups-toggle-txt" data-toggle-txt="<?php echo $lifetime_sale_price_text; ?>">
                            <?php echo $annual_sale_price_text; ?>                                    
                        </span> 
                    </span>
                </span>
            </div> 
        </div>
        <?php
    }

    /**
     * Show annual and lifetime price of feature.
     */
    public function show_feature_price($general_features, $column) {
        $features_list = $general_features->features;
        $feature_index   = 0;

        foreach ($features_list as $single_feature) {
            $annual_price_text   = '$0';
            $lifetime_price_text = '$0';
            $feature_index++;

            if ( in_array( $column[ 'column_index' ], $single_feature[ 'general_feature_applicable_for' ] ) ) {
                $annual_price   = trim($single_feature[ 'general_feature_annual_price' ]);
                $lifetime_price = trim( $single_feature[ 'general_feature_lifetime_price' ] );

                $annual_price_text   = empty( $annual_price ) ? $annual_price_text : '$' . $annual_price;
                $lifetime_price_text = empty( $lifetime_price ) ? $lifetime_price_text : '$' . $lifetime_price;
            }
            
            ?>
            <span class="wpt-3-feature-price ups-cell ups-toggle-txt ups-cell-<?php echo esc_attr($feature_index); ?>" data-highlight="ups-cell-<?php echo esc_attr( $feature_index ); ?>" data-toggle-txt="<?php echo esc_attr( $lifetime_price_text ); ?>">
                <?php echo esc_html( $annual_price_text ); ?>
            </span>
            <?php
        }
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
            $lifetime_discount = "You Save: $".($lifetime_regular_price - $lifetime_sale_price);
        }
        if($annual_regular_price > 0){
            $annual_discount = "You Save: $". ($annual_regular_price - $annual_sale_price);
        }
     
        ?>
        
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
        $annual_button   = $this->get_action_button_details($column);
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

        $col_btn_class = " alt-btn ";
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

    function render_general_features_desktop($general_features){
        $features_array  = $general_features->features;
        $feature_index   = 0;
        foreach ($features_array as $key => $feature) {
            $feature_index++;
            ?>
            <div class="wpt-3-feature-item ups-cell uprh ups-cell-<?php echo $feature_index; ?>" 
                data-highlight="ups-cell-<?php echo $feature_index; ?>">
                <div class="wpt-3-feature-inner" style="min-width: 200px;">
                    <div class="wpt-3-feature-title"><?php echo wp_kses_post( $feature['general_feature_title'] );?></div>
                    <?php
                    if ( ! empty( $feature['general_feature_subtitle'] ) ) {
                        ?>
                         <div class="wpt-3-feature-subtitle">
                            <ul>
                                <li>
                                    <span>
                                        <?php echo wp_kses_post( $feature['general_feature_subtitle'] );?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        <?php
        }
    }

    public function render_general_features_mobile($general_features, $column){
        $features_array  = $general_features->features;
        $feature_index   = 0;

        foreach ($features_array as $key => $feature) {
            $feature_index++;
            $annual_price_text   = '$0';
            $lifetime_price_text = '$0';

            if ( in_array( $column[ 'column_index' ], $feature[ 'general_feature_applicable_for' ] ) ) {
                $annual_price   = trim($feature[ 'general_feature_annual_price' ]);
                $lifetime_price = trim( $feature[ 'general_feature_lifetime_price' ] );

                $annual_price_text   = empty( $annual_price ) ? $annual_price_text : '$' . $annual_price;
                $lifetime_price_text = empty( $lifetime_price ) ? $lifetime_price_text : '$' . $lifetime_price;
            }
            ?>
            <div class="wpt-3-single-feature-price-wrapper">
                <div class="wpt-3-feature-item ups-cell uprh ups-cell-<?php echo $feature_index; ?>" 
                    data-highlight="ups-cell-<?php echo $feature_index; ?>">
                    <div class="wpt-3-feature-inner" style="min-width: 200px;">
                        <div class="wpt-3-feature-title"><?php echo wp_kses_post( $feature['general_feature_title'] );?></div>
                        <?php
                   
                        if ( ! empty( $feature['general_feature_subtitle'] ) ) {
                            ?>
                            <div class="wpt-3-feature-subtitle">
                                <ul>
                                    <li>
                                        <span>
                                            <?php echo wp_kses_post( $feature['general_feature_subtitle'] );?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="wpt-3-feature-price-div">
                    <span class="wpt-3-feature-price ups-cell ups-toggle-txt" data-toggle-txt="<?php echo esc_attr( $lifetime_price_text ); ?>">
                        <?php echo esc_html( $annual_price_text ); ?>
                    </span>
                </div>
            </div>
        <?php
        }
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

    function ui_pricing_section_mobile($general_features,$pricing){
        $settings = $this->get_settings_for_display();
        ?>
        <div class="wpt-3-mobile hide-d">
            <div class="ups">
                <?php do_action('pricing_sale_strip_start_mobile');?>
                <div class="ups-plan-duration">
                    <?php $this->annual_lifetime_toggle($pricing); ?>
                </div>
                <?php
                $pricing_tip_annual_lifetime = trim( $settings['pricing_note_annual_lifetime'] );
                if ( ! empty( $pricing_tip_annual_lifetime ) ) :
                    echo '<div class="wpt-3-price-tip-annual-lifetime">' . wp_kses_post( $pricing_tip_annual_lifetime ) . '</div>';
                endif;
                ?>
                <div class="upsm hide-d" style="margin-top: 7px;">
                    <div class="upsm-inr price-slick">  
                        <?php
                        foreach($pricing->pricing_columns as $column){
                            ?>
                            <div class="up-plan">
                                <div class="wpt-3-upp-inr">
                                    <div class="wpt-3-upp-plan-w">
                                        <div class="wpt-3-upp-plan-w-inr">
                                            <div class="wpt-3-upp-title-w"> 
                                                <span class="wpt-3-upp-title" >
                                                    <?php echo $column['column_title'];?>                  
                                                </span>
                                                <?php
                                                if ( ! empty( $column['column_description'] ) ) :
                                                    ?>
                                                    <div class="upp-description">
                                                        <?php echo $column['column_description'];?> 
                                                    </div>
                                                    <?php
                                                endif;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php $this->ui_pricing_plan($column); ?>
                                    <div class="wpt-3-features-price-wrapper-mobile">
                                        <div class="wpt-3-features-mobile">
                                            <?php
                                            $this->render_general_features_mobile($general_features, $column);
                                            ?>
                                        </div>
                                    </div>
                                    <div class="wpt-3-col-action-buttons">
                                        <?php $this->show_main_product_annual_lifetime_price($column); ?>
                                        <?php $this->get_action_buttons($column);?>
                                    </div>
                                    <div class="hide upp-popup-c">
                                        <div class="upsmdl-c-count">
                                            Features
                                        </div>
                                        <div class="ufq" role="tablist">
                                        </div>
                                    </div>
                                </div> 
                            </div> 
                            <?php
                        }
                        ?>
                    </div> 
                </div> 
            </div>
        </div>
        <?php
    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Pricing_Table3_Widget() );
