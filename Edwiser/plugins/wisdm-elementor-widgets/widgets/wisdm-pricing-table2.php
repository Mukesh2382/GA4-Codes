<?php
namespace Elementor;
class Wisdm_Pricing_Table2_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-pricing-table2-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Pricing Table 2', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script','wdm-pricing-table2-script'
        ];
    }

    public function get_icon() {
        return 'eicon-price-table';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->_pricing_toggle_btns_setting();
        $this->_pricing_columns_setting();
        $this->_pricing_features();
    }

    function _pricing_features(){
        
        $columns = $this->fetch_columns();
        foreach ($columns as $key => $value) {
            $this->start_controls_section(
                'pricing_features_column_'.$key,
                [
                    'label' => esc_html__( 'Features - ', 'wisdm-elementor-widgets' ).' '.$value,
                    'tab' => Controls_Manager::TAB_CONTENT,
                ]
            );
            $this->add_control(
                'visible_features_count_'.$key,
                [
                    'label' => esc_html__( 'Visible Features Count', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::NUMBER,
                    'default' => '5',
                    'min' => '1',
                    'max' => '15'
                ]
            );
            $features = new \Elementor\Repeater();
            $features->add_control(
                'feature_title',
                [
                    'label' => esc_html__( 'Feature Title', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::TEXT,
                    'default' => esc_html__( 'Feature Title', 'wisdm-elementor-widgets' ),
                ]
            );
            $features->add_control(
                'feature_type',
                [
                    'label' => esc_html__( 'Feature Type', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SELECT,
                    'options' => [
                        "single" => "Single",
                        "parent" => "Parent",
                        "child" => "Child",
                        "pack" => "Pack",
                    ],
                    'default' => 'single',
                ]
            );
            $features->add_control(
                'visible_subfeatures_count',
                [
                    'label' => esc_html__( 'Visible Subfeatures Count', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::NUMBER,
                    'condition' => [
                        'feature_type' => 'parent',
                    ],
                ]
            );
            $features->add_control(
                'show_info',
                [
                    'label' => esc_html__( 'Show Description', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::SWITCHER,
                    'label_on' => esc_html__( 'Show', 'wisdm-elementor-widgets' ),
                    'label_off' => esc_html__( 'Hide', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes',
                    'default' => 'no',
                    'condition' => [
                        'feature_type' => ['single','child'],
                    ],
                ]
            );
            $features->add_control(
                'info_title',
                [
                    'label' => esc_html__( 'Info Title', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::TEXT,
                    'default' => esc_html__( '', 'wisdm-elementor-widgets' ),
                    'condition' => [
                        'show_info' => 'yes',
                    ],
                ]
            );
            $features->add_control(
                'info_desc',
                [
                    'label' => esc_html__( 'Info Description', 'wisdm-elementor-widgets' ),
                    'type' => Controls_Manager::TEXT,
                    'default' => esc_html__( '', 'wisdm-elementor-widgets' ),
                    'condition' => [
                        'show_info' => 'yes',
                    ],
                ]
            );

            $features->add_control(
                'show_price',
                [
                    'label' => esc_html__( 'Show Price', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes'
                ]
            );
            $features->add_control(
                'annual_price',
                [
                    'label' => esc_html__( 'Annual Price', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'default' => '99',
                    'condition' => [
                        'show_price' => 'yes',
                    ],
                ]
            );
            $features->add_control(
                'lifetime_price',
                [
                    'label' => esc_html__( 'Lifetime Price', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'default' => '199',
                    'condition' => [
                        'show_price' => 'yes',
                    ],
                ]
            );
            $this->add_control(
                'pricing_features_'.$key,
                [
                    'label' => __( 'Features', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $features->get_controls(),
                    'default' => [],
                    'title_field' => 
                        "{{{ (feature_type == 'child' ? '- ' : '') }}} 
                         {{{ (feature_type == 'pack' ? '+ ' : '') }}}
                         {{{ (feature_type == 'parent' ? '* ' : '') }}}
                         {{{ feature_title.substring(0,24) }}} ",
                ]
            );
            $this->end_controls_section();
            unset($features);
        }
    }

    function _pricing_toggle_btns_setting(){
        $this->start_controls_section(
            'general_settings',
            [
                'label' => __( 'General Settings', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'annual_toggle_button_title',
            [
                'label' => __( 'Annual toggle button title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => "Annual"
            ]
        );
        $this->add_control(
            'lifetime_toggle_button_title',
            [
                'label' => __( 'Lifetime toggle button title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => "Lifetime"
            ]
        );
        $this->add_control(
            "show_note",
            [
                'label' => __( 'Show note', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                'return_value' => 'yes',
                'default' => '',
            ]
        );
        $this->add_control(
            'note_text',
            [
                'label' => __( 'Note Text', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => 'Note: ‘For Bulk Purchases, change ” License Quantity” on Checkout Page’.',
                'condition' => [
                    'show_note' => 'yes'
                ]
            ]
        );
        $this->end_controls_section();
    }

    function fetch_columns(){
        $columns = [
            "1" => "Column 1",
            "2" => "Column 2",
            "3" => "Column 3",
            "4" => "Column 4",
        ];
        return $columns;
    }

    function _pricing_columns_setting(){
        $this->start_controls_section(
            'pricing_columns_settings',
            [
                'label' => __( 'Products', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $columns = new \Elementor\Repeater();
        $columns_options = $this->fetch_columns();
        $downloads = \WisdmEW_Edd::get_downloads_with_variables();
        $columns->add_control(
            'column_no',
            [
                'label' => __( 'Pricing column no', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $columns_options,
                'default' => 1,
            ]
        );
        $columns->add_control(
            'product_title',
            [
                'label' => __( 'Product Title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => "Elumine"
            ]
        );
     
        $columns->add_control(
            "highlight_column",
            [
                'label' => __( 'Highlight column', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        $columns->add_control(
            "show_ribbon",
            [
                'label' => __( 'Show ribbon', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                'return_value' => 'yes',
                'default' => '',
            ]
        );
        $columns->add_control(
            'ribbon_text',
            [
                'label' => __( 'Ribbon Text', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Best Seller',
                'condition' => [
                    'show_ribbon' => 'yes'
                ]
            ]
        );
        $columns->add_control(
            "free_download",
            [
                'label' => __( 'Free Download', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Yes', 'wisdm-elementor-widgets' ),
                'label_off' => __( 'No', 'wisdm-elementor-widgets' ),
                'return_value' => 'yes',
                'default' => '',
            ]
        );
     
        // Annual options
        $columns->add_control(
			'annual_options',
            [
                'label' => __( 'Annual options', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
               
            ]
		);
        $columns->add_control(
            'annual_price_btn_title',
            [
                'label' => __( 'Annual price button title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => "Buy Now",
            ]
        );
        $columns->add_control(
            'annual_strikethrough_price',
            [
                'label' => __( 'Annual strikethrough price', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => "199",
                'condition' => [
                    'free_download' => ''
                ]
            ]
        );
        $columns->add_control(
            'annual_price',
            [
                'label' => __( 'Annual price', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => "99",
                'condition' => [
                    'free_download' => ''
                ]
            ]
        );
        $columns->add_control(
            'annual_product',
            [
                'label' => __( 'Select Product', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => $downloads,
            ]
        );
        // Lifetime options
        $columns->add_control(
			'lifetime_options',
            [
                'label' => __( 'Lifetime options', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
                'condition' => [
                    'free_download' => ''
                ]
               
            ]
		);

        $columns->add_control(
            'lifetime_price_btn_title',
            [
                'label' => __( 'Lifetime price button title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => "Buy Now",
                'condition' => [
                    'free_download' => ''
                ]
               
            ]
        );

        $columns->add_control(
            'lifetime_strikethrough_price',
            [
                'label' => __( 'Lifetime strikethrough price', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => "199",
                'condition' => [
                    'free_download' => ''
                ]
            ]
        );
        $columns->add_control(
            'lifetime_price',
            [
                'label' => __( 'Lifetime price', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => "99",
                'condition' => [
                    'free_download' => ''
                ]
            ]
        );
        $columns->add_control(
            'lifetime_product',
            [
                'label' => __( 'Select Product', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'label_block' => true,
                'options' => $downloads,
                'condition' => [
                    'free_download' => ''
                ]
            ]
        );

        $this->add_control(
            "pricing_columns",
            [
                'label' => __( 'Pricing columns', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $columns->get_controls(),
                'default' => [],
                'title_field' => "Col {{{column_no}}} - {{{ product_title.substring(0,24) }}}",
            ]
        );

        $this->end_controls_section();
    }
    private function style_tab() {
        
    }

    public function pricing_tables_data($settings) {
        $pricing_columns =  $settings['pricing_columns'];

        $result = [];
        foreach($pricing_columns as $data){
            $data['highlight_column'] = ($data['highlight_column'] == 'yes') ? true : false;
            $data['show_ribbon'] = ($data['show_ribbon'] == 'yes') ? true : false;
            $data['free_download'] = ($data['free_download'] == 'yes') ? true : false;
            $data['annual_strikethrough_price'] = empty(trim($data['annual_strikethrough_price'])) ? false : $data['annual_strikethrough_price'];
            $data['lifetime_strikethrough_price'] = empty(trim($data['lifetime_strikethrough_price'])) ? false : $data['lifetime_strikethrough_price'];
            $pricing_feature_index =  "pricing_features_".$data['column_no'];
            $visible_features_count_index = "visible_features_count_".$data['column_no'];
            $annual_product_split = explode("_",$data['annual_product']);
            $data['annual_product_download_id'] = $annual_product_split[0];
            $data['annual_product_price_id'] = $annual_product_split[1];
            $lifetime_product_split = explode("_",$data['lifetime_product']);
            $data['lifetime_product_download_id'] = $lifetime_product_split[0];
            $data['lifetime_product_price_id'] = $lifetime_product_split[1];
            $features = $settings[$pricing_feature_index];
            $data['visible_features_count'] = $settings[$visible_features_count_index];
            foreach($features as $index => $feature){
                $features[$index]['show_price'] = $feature['show_price'] == 'yes' ? true : false;
            }
            $data['features'] = $features;
            $data['filtered_features'] = $this->filter_features($features);

            $result[] = $data;
        }
        return $result;
    }

    public function filter_features($features){
        $general_features = [];
        $packs = [];
        $groups = []; // group of child features with single parent
        $active_parent = false;
        foreach($features as $index => $feature){
            $feature['index'] = $index;
            if($feature['feature_type'] == 'single'){
                $general_features[] = $feature;
            }else if($feature['feature_type'] == 'pack'){
                $packs[] = $feature;
            }else {
                if($feature['feature_type'] == 'parent'){
                    $active_parent = $index;
                    $groups[$active_parent] = $feature; 
                    $groups[$active_parent]['sub_features'] = [];
                }
                if($feature['feature_type'] == 'child'){
                    if($active_parent){
                        $groups[$active_parent]['sub_features'][] = $feature;
                    }
                }
            }
        }
        return [
            "general_features" => $general_features,
            "packs" => $packs,
            "groups" => $groups,
        ];
    }

    public function pricing_head($column,$pricing_table_data,$sticky_header=false){
        $imageDir = WDM_WIDGETS_PLUGIN_PATH . "assets/wdm-pricing-table2/images/";
        $highlighted_column_bg = $imageDir."highlighted-ptable.png"; 
        $show_ribbon = $column['show_ribbon'] ;
        $highlight_column = $column['highlight_column'];
        $ribbon_text = $column['ribbon_text'];
        $product_title = $column['product_title'];
        $column_head_style =  ($highlight_column) ? "background-image: url($highlighted_column_bg);" : ""; 
        $annual_options = [
            'strikethrough_price' => $column['annual_strikethrough_price'],
            'price' => $column['annual_price'],
            'download_id' => $column['annual_product_download_id'],
            'price_id' => $column['annual_product_price_id'],
            'price_btn_title' => $column['annual_price_btn_title'],
        ];
        $lifetime_options = [
            'strikethrough_price' => $column['lifetime_strikethrough_price'],
            'price' => $column['lifetime_price'],
            'download_id' => $column['lifetime_product_download_id'],
            'price_id' => $column['lifetime_product_price_id'],
            'price_btn_title' => $column['lifetime_price_btn_title'],
        ];
        ?>
        <!-- Sticky Header -->
        <?php if($sticky_header) { ?>
            <div class="wdm-p2-head-outer">
                <div class="wdm-p2-head" style="<?php echo $column_head_style;?>">
                    <h4 class="wdm-p2-product-title"><?php echo $product_title;?></h4>
                    <!-- <div class="wdm-p2-lifetime">
                        (Lifetime)
                    </div> -->
                    <div class="wdm-p2-annual">
                        <?php $this->pricing_section($annual_options , $column); ?>
                    </div>
                    <div class="wdm-p2-lifetime">
                        <?php $this->pricing_section($lifetime_options , $column); ?>
                    </div>
                </div>
            </div>
        <?php } else { ?>
    
            <div class="wdm-p2-head-outer">
                <div class="wdm-p2-head" style="<?php echo $column_head_style;?>">
                 <!-- pricing header -->
                    <?php if ($show_ribbon) { ?>
                        <div class="wdm-p2-ribbon-outer">
                            <span class="wdm-p2-popular-plan-col"><?php echo $ribbon_text;?></span>
                        </div>
                    <?php } ?>
                    <h4 class="wdm-p2-product-title"><?php echo $product_title;?></h4>
                    <div class="wdm-p2-annual">
                        <?php $this->pricing_section($annual_options , $column); ?>
                    </div>
                    <div class="wdm-p2-lifetime">
                        <!-- (Lifetime) -->
                        <?php $this->pricing_section($lifetime_options , $column); ?>
                    </div>
                </div>
            </div>

        <?php }
    }

    public function get_action_button($column,$pricing_table_data , $pricing_option = "annual"){

        $annual_options = [
            'strikethrough_price' => $column['annual_strikethrough_price'],
            'price' => $column['annual_price'],
            'download_id' => $column['annual_product_download_id'],
            'price_id' => $column['annual_product_price_id'],
            'price_btn_title' => $column['annual_price_btn_title'],
        ];
        $lifetime_options = [
            'strikethrough_price' => $column['lifetime_strikethrough_price'],
            'price' => $column['lifetime_price'],
            'download_id' => $column['lifetime_product_download_id'],
            'price_id' => $column['lifetime_product_price_id'],
            'price_btn_title' => $column['lifetime_price_btn_title'],
        ];
        $pricing_data = ($pricing_option == "annual") ? $annual_options : $lifetime_options;
        $download_id = $pricing_data['download_id'];
        $price_btn_title        = $pricing_data['price_btn_title']; 
        $price_id    = $pricing_data['price_id']; 
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
                        <button id="wdm-free-download" class="wdm-p2-cta-btn " data-download-id="<?php echo $download->ID; ?>"><?php echo $price_btn_title; ?></button>
                    </span>
                <?php } else { ?>
                    <!-- Trigger/Open The Modal -->
                    <button data-modal-id='wdm-free-download-modal' class=" wdm-p2-cta-btn modal-backdrop-btn "><?php echo $price_btn_title; ?></button>
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
                            class="wdm-p2-cta-btn  <?php echo "wdm-pricing-button-" . $price_id; ?>" 
                            style="width: auto;" 
                            name="edd_purchase_download"  
                            data-action="edd_add_to_cart" 
                            data-download-categories="[]" 
                            data-download-id="<?php echo $download_id; ?>" 
                            data-variable-price="yes" 
                            data-price-mode="<?php echo $price_name; ?>">
                            <?php echo $price_btn_title; ?>
                        </button>
                    </a>
                </form>
                <?php
            }
        }
        $data = ob_get_clean();
        return $data;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $pricing_table_data = $this->pricing_tables_data($settings);
        $pricing_tables_count = count($pricing_table_data);
        $pricing_table_width = (100 / $pricing_tables_count ); 
        // Toggle buttons
        $annual_toggle_button_title = $settings['annual_toggle_button_title'];
        $lifetime_toggle_button_title = $settings['lifetime_toggle_button_title'];
        $annual_toggle_button_title = empty($annual_toggle_button_title)?"Annual":$annual_toggle_button_title;
        $lifetime_toggle_button_title = empty($lifetime_toggle_button_title)?"Lifetime":$lifetime_toggle_button_title;
        $show_note = $settings['show_note'] == 'yes';;
        $note_text = ($show_note) ? $settings['note_text'] : "";
        // Pricing columns
        if(!empty($settings)){
            ?>
            <div class="wdm-p2-wrap">
                <div class="wdm-p2-sticky-header">
                    <div class="wdm-p2-pricing-controls">
                        <div class="wdm-p2-toggle-btn-group">
                            <button class="wdm-p2-annual-btn active"><?php echo $annual_toggle_button_title; ?></button>
                            <button class="wdm-p2-lifetime-btn"><?php echo $lifetime_toggle_button_title; ?></button>
                        </div>
                    </div>
                    <div class="wdm-p2">
                        <div class="wdm-p2-columns">
                            <?php  foreach($pricing_table_data as $column){  
                                    $highlight_column = $column['highlight_column'];
                                    $column_highlight_class = ($highlight_column) ? "highlighted" : "";
                                ?>
                                <div class="wdm-p2-column <?php echo $column_highlight_class;?>" style="width:<?php echo $pricing_table_width;?>%">
                                    <?php $this->pricing_head($column,$pricing_table_data,true); ?>
                                </div>
                            <?php } ?>
                        </div> 
                    </div> 
                </div>

                <div class="wdm-p2 wdm-p2-main">
                    <div class="wdm-p2-pricing-controls">
                        <div class="wdm-p2-toggle-btn-group">
                            <button class="wdm-p2-annual-btn active"><?php echo $annual_toggle_button_title; ?></button>
                            <button class="wdm-p2-lifetime-btn"><?php echo $lifetime_toggle_button_title; ?></button>
                        </div>
                    </div>
                
                    <div class="wdm-p2-columns">
                        <?php  foreach($pricing_table_data as $column){ ?>
                            <?php 
                                $highlight_column = $column['highlight_column'];
                                $column_highlight_class = ($highlight_column) ? "highlighted" : "";
                                $filtered_features = $column['filtered_features'];
                                $general_features = $filtered_features['general_features'];
                                $packs = $filtered_features['packs'];
                                $groups = $filtered_features['groups'];
                                $visible_features_count = $column['visible_features_count'];
                            ?>
                            <div class="wdm-p2-column <?php echo $column_highlight_class;?>" style="width:<?php echo $pricing_table_width;?>%">
                                
                                <!-- Pricing head -->
                                <?php $this->pricing_head($column,$pricing_table_data,false); ?>

                                <!-- Features -->
                                <div class="wdm-p2-features">
                                    <!-- packs -->
                                    <?php foreach($packs as $feature){ ?>
                                        <?php $this->render_feature($feature , $column); ?>
                                    <?php }?>
                                    <!-- general features - single -->
                                    <div class="wdm-p2-features-general">
                                        <?php
                                            $general_features_count = count($general_features);
                                            $visible_features_count = $column['visible_features_count'];
                                            // if visible features count is zero then set this limit to max
                                            $visible_features_count = ($visible_features_count == 0) ? 99 : $visible_features_count ;
                                            $visble_all_features = ($visible_features_count > $general_features_count) ;
                                            $visible_features_count = ($visble_all_features) ? $general_features_count : $visible_features_count ;
                                            $general_visible_features = array_slice($general_features, 0, $visible_features_count);
                                            $general_hidden_features = [];
                                            if(!$visble_all_features){
                                                $hidden_features_count = $general_features_count - $visible_features_count;
                                                $general_hidden_features = array_slice($general_features,$visible_features_count,$hidden_features_count);
                                            }
                                        ?>
                                        <?php foreach($general_visible_features as $feature){ ?>
                                            <?php $this->render_feature($feature , $column); ?>
                                        <?php }?>
                                        <?php if(!empty($general_hidden_features)){ ?>
                                            <div class="wdm-p2-hidden-features">
                                                <?php foreach($general_hidden_features as $feature){ ?>
                                                    <?php $this->render_feature($feature , $column); ?>
                                                <?php } ?>
                                                <a class="wdm-p2-showless-features">
                                                    show less
                                                </a>
                                            </div>
                                            <a class="wdm-p2-showmore-features">
                                                +<?php echo $hidden_features_count; ?> more
                                            </a>
                                        <?php }?>
                                    </div>
                                    <!-- groups -->
                                    <div class="wdm-p2-features-groups">
                                        <?php foreach($groups as $group){ 
                                            $visible_subfeatures_count = $group['visible_subfeatures_count'];
                                            $index = $group['index'];
                                            $hidden_features_class = "wdm-p2-hidden-features_$index" ;
                                            $showmore_btn_class = "wdm-p2-showmore_$index" ;

                                            $this->render_feature($group , $column);
                                            $sub_features = $group['sub_features'];
                                            if(!empty($sub_features)){
                                                $subfeatures_count = count($sub_features);
                                                $subfeatures_count = ($subfeatures_count == 0) ? 99 : $subfeatures_count ;
                                                $visble_all_subfeatures = ($visible_subfeatures_count > $subfeatures_count) ;
                                                $visible_subfeatures_count = ($visble_all_subfeatures) ? $subfeatures_count : $visible_subfeatures_count ;
                                                $visible_subfeatures = array_slice($sub_features, 0, $visible_subfeatures_count);
                                                $hidden_subfeatures = [];
                                                if(!$visble_all_subfeatures){
                                                    $hidden_subfeatures_count = $subfeatures_count - $visible_subfeatures_count;
                                                    $hidden_subfeatures = array_slice($sub_features,$visible_subfeatures_count,$hidden_subfeatures_count);
                                                }
                                                ?>
                                                    <?php foreach($visible_subfeatures as $feature){ ?>
                                                    <?php $this->render_feature($feature , $column); ?>
                                                    <?php }?>
                                                    <?php if(!empty($hidden_subfeatures)){ ?>
                                                        <div class="wdm-p2-hidden-features <?php echo $hidden_features_class?>">
                                                            <?php foreach($hidden_subfeatures as $feature){ ?>
                                                                <?php $this->render_feature($feature , $column); ?>
                                                            <?php } ?>
                                                            <a class="wdm-p2-showless-subfeatures " data-showclass="<?php echo $showmore_btn_class?>">
                                                                show less
                                                            </a>
                                                        </div>
                                                        <a class="wdm-p2-showmore-subfeatures <?php echo $showmore_btn_class?> " data-hideclass="<?php echo $hidden_features_class?>">
                                                            +<?php echo $hidden_subfeatures_count; ?> more
                                                        </a>
                                                    <?php }?>
                                                <?php
                                            }
                                        }?>
                                    </div>
                                </div>

                                <!-- Pricing cta -->
                                <div class="wdm-p2-cta">
                                    <div class="wdm-p2-annual">
                                        <?php echo $this->get_action_button($column,$pricing_table_data , "annual"); ?>
                                    </div>
                                    <div class="wdm-p2-lifetime">
                                        <?php echo $this->get_action_button($column,$pricing_table_data , "lifetime"); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php if ($show_note) { ?>
                <div class='wdm-p2-pricing-note'>
                    <?php echo $note_text; ?>
                </div>
                <?php } ?>
                <?php // $this->sticky_footer($column , $pricing_table_data, $pricing_table_width); ?>
                
            </div>
            <?php
        
        }
    }

    public function sticky_footer($column , $pricing_table_data,$pricing_table_width){
        ?>
        <div class="wdm-p2-sticky-footer">
            <div class="wdm-p2">
                <div class="wdm-p2-columns">
                    <?php  foreach($pricing_table_data as $column){  
                            $highlight_column = $column['highlight_column'];
                            $column_highlight_class = ($highlight_column) ? "highlighted" : "";
                        ?>
                        <div class="wdm-p2-column <?php echo $column_highlight_class;?>" style="width:<?php echo $pricing_table_width;?>%">
                            <!-- Pricing cta -->
                            <div class="wdm-p2-cta">
                                <div class="wdm-p2-annual">
                                    <?php echo $this->get_action_button($column,$pricing_table_data , "annual"); ?>
                                </div>
                                <div class="wdm-p2-lifetime">
                                    <?php echo $this->get_action_button($column,$pricing_table_data , "lifetime"); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div> 
            </div> 
        </div>
        <?php
    }
    public function render_feature($feature){
        $imageDir = WDM_WIDGETS_PLUGIN_PATH . "assets/wdm-pricing-table2/images/";
        $tickimage =  $imageDir. "tick.png";
        $infoImage =  $imageDir. "info.png";

        ?>
        <div class="wdm-p2-feature <?php echo $feature['feature_type'];?>">
            <!-- tick -->
            <span class="wdm-p2-tick">
                <img src="<?php echo $tickimage;?>" alt="">
            </span>
            <span class="wdm-p2-feature-title">
                <!-- title -->
                <?php echo $feature['feature_title'];?>
                <!-- feature product price -->
                <?php if($feature['show_price']) { ?>
                    <span class="wdm-p2-feature-price">
                        <strong class="wdm-p2-annual"> - $<?php echo $feature['annual_price']; ?></strong>
                        <strong class="wdm-p2-lifetime"> - $<?php echo $feature['lifetime_price']; ?> </strong>
                    </span>
                <?php } ?>
                 <!-- info -->
                <?php if( in_array($feature['feature_type'] , ['single','child']) ) { ?>
                    <?php if($feature['show_info'] == 'yes' && !empty($feature['info_title'])) { ?>
                    <div class="wdm-p2-i-w">
                        <img class="wdm-p2-i-btn"  src="<?php echo $infoImage;?>" alt="">
                        <div class="wdm-fs-i-popup-w">
                            <div class="wdm-fs-i-popup">
                                <div class="wdm-fs-i-popup-c">
                                    <div class="wdm-fs-i-t">
                                        <?php echo $feature['info_title'];?>                                       
                                    </div>
                                    <?php if( !empty($feature['info_desc'])) { ?>
                                    <div class="wdm-fs-i-desc">
                                        <?php echo $feature['info_desc'];?>                                       
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <span class="wdm-fs-i-popup-arrow"></span>
                        </div>
                    </div>
                    <?php  } ?>
                <?php } ?>
            </span>
        </div>
         <!-- package sign -->
         <?php if($feature['feature_type'] == 'pack') { ?>
            <div class="wdm-p2-pack-plus">+</div>
        <?php } ?>
        <?php
    }

    public function pricing_section($product_details , $column){
        $strikethrough_price = $product_details['strikethrough_price'];
        $price = $product_details['price'];

        ?>
        <span class="wdm-p2-prices ">
            <?php if($strikethrough_price) { ?>
            <s class="wdm-p2-strikethrough-price">$<?php echo $strikethrough_price; ?></s> 
            <?php } ?>
            <strong class="wdm-p2-price"> $<?php echo $price; ?></strong>
        </span>
            <?php if($strikethrough_price) { 
                $your_saving = $strikethrough_price - $price;
                $your_saving_perc = round(($your_saving / $strikethrough_price) * 100);
                ?>
            <span class="wdm-p2-saving">You save <?php echo $your_saving_perc; ?>%</span>
            <?php } ?>
        <?php
    }

    protected function _content_template() {

    }

}

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Pricing_Table2_Widget() );
