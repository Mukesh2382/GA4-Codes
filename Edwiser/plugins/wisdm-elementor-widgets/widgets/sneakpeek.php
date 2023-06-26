<?php
namespace Elementor;
use Elementor\Core\Base\Base_Object;

class MYEW_Sneakpeek_Widget extends Widget_Base {
   
    public function get_name() {
        return  'myew-sneakpeek-id';
    }

    public function get_title() {
        return esc_html__( 'Sneakpeek', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script'
        ];
    }

    public function get_icon() {
        return 'eicon-tabs';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        // Tabs Setting
        $this->tab_settings();
       
        // Tab Sections
        $this->tab_sections();
    }

    private function tab_settings(){
        $this->start_controls_section(
            'tabs_settings',
            [
                'label' => __( 'Tabs Settings', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        // Tabs Repeater
        $repeater = new \Elementor\Repeater();
        $tab_id_options = $this->fetch_tab_ids();
        $repeater->add_control(
            'tab_title',
            [
                'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __( 'Tab title', 'wisdm-elementor-widgets' ),
                'label_block' => true
            ]
        );
        $repeater->add_control(
            'tab_id',
            [
                'label' => __( 'Tab Id', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 1,
                'options' => $tab_id_options,
            ]
        );
        $repeater->add_control(
            'tab_tagline',
            [
                'label' => __( 'Tab tagline', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __( 'Tab tagline', 'wisdm-elementor-widgets' ),
                'label_block' => true
            ]
        );
        $this->add_control(
            'tabs',
            [
                'label' => __( 'Tabs', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'tab_title' => __( 'Tab title', 'wisdm-elementor-widgets' ),
                        'tab_id' => "1",
                    ],
                ],
                'title_field' => 'TAB {{{  tab_id }}} - {{{  tab_title }}}',
            ]
        );
        $this->add_control(
			'hr',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);
        $this->add_control(
            'active_tab',
            [
                'label' => __( 'Active tab', 'wisdm-elementor-widgets' ),
				'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 1,
                'options' => $tab_id_options,
            ]
        );
        $this->end_controls_section();
    }

    function fetch_tab_ids(){
        $tab_id_options = [];
        for($i=1;$i<=7;$i++){
            $tab_id_options["$i"] = "Tab $i";
        }
        return $tab_id_options;
    }

    private function tab_sections(){
        $tab_id_options = $this->fetch_tab_ids();

        $this->start_controls_section(
            "tab_features",
            [
                'label' => __( "Manage Features ", 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

            $feature_repeater = new \Elementor\Repeater();
            // Tab ID
            $feature_repeater->add_control(
                'feature_tab_id',
                [
                    'label' => __( 'Tab Id', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 1,
                    'options' => $tab_id_options,
                ]
            );
            // Image
             $feature_repeater->add_control(
                'image',
                [
                    'label' => __( 'Choose Feature Image', 'plugin-domain' ),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ],
                ]
            );
            // Image Tagline
            $feature_repeater->add_control(
                "feature_title",
                [
                    'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Feature title', 'wisdm-elementor-widgets' ),
                    'label_block' => true
                ]
            );
            // Description Title
            $feature_repeater->add_control(
                'feature_desc_title',
                [
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Descrition title', 'wisdm-elementor-widgets' ),
                    'label' => __( 'Descrition title', 'wisdm-elementor-widgets' ),
                ]
            );
            // Description 
            $feature_repeater->add_control(
                'feature_desc',
                [
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => __( 'Default description', 'wisdm-elementor-widgets' ),
                    'label' => __( 'Feature Description', 'wisdm-elementor-widgets' ),
                ]
            );

            $this->add_control(
                "features",
                [
                    'label' => __( 'Features', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $feature_repeater->get_controls(),
                    'default' => [
                        [
                            "feature_title" => __( 'Feature title', 'wisdm-elementor-widgets' ),
                            "feature_desc" => ''
                        ],
                    ],
                    'title_field' => "TAB {{{ feature_tab_id }}} - Feature {{{ feature_title.substring(0,19) }}}",
                ]
            );

        $this->end_controls_section();
    }

    private function style_tab() {}

    protected function render() {
        $settings = $this->get_settings_for_display();
       
        if($settings){
            $active_tab =  $settings['active_tab'];
            $tabs = [];
            $tabs_issues = [];
            $index = 0;
            foreach($settings['tabs'] as $tab){
                $tab_id = $tab['tab_id'];
                $tab_title = $tab['tab_title'];
                $tab['active_class'] = ($tab_id == $active_tab) ? "active" : "";
                $index++;
                if(!isset($tabs[$tab_id])){
                    $tabs[$tab_id] = $tab;
                }
                else{
                    $tabs_issues[] = "Duplicate Tab id # $tab_id used for - $tab_title";
                }
            }
            $features = [];
            $feature_issues = [];
            $feature_index = 0;
            foreach($settings['features'] as $index => $feature){
                $feature_tab_id = $feature['feature_tab_id'];
                $feature['index'] = $feature_index; 

                if(isset($tabs[$feature_tab_id])){
                    if(!isset($features[$feature_tab_id])){
                        $features[$feature_tab_id] = [];
                    }
                    $features[$feature_tab_id][] = $feature;
                    $feature_index++;
                }
                else{
                    $feature_issues[] = "Tab id $feature_tab_id is not defined but used in feature-$index";
                }
            }
        }
        ?>
            <div class="edw-sneakpeek">
                <ul class="tabs">
                    <?php foreach($tabs as $index => $tab){ ?>
                    <li class="sneakpeek-tabs-list <?php echo $tab['active_class']?>" 
                        data-tid="tab-<?php echo $tab['tab_id'];?>" 
                        data-lbl="<?php echo $tab['tab_title'];?>" 
                        data-tline="<?php echo $tab['tab_tagline'];?>">
                        <span class="tab"><?php echo $tab['tab_title'];?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php foreach($tabs as $index => $tab){ 
                    $tab_id = $tab['tab_id'];
                    if(isset($features[$tab_id])){
                        $tabfeatures = $features[$tab_id];
                    ?>
                        <div class="tab-content-wrap <?php echo $tab['active_class']?>" 
                            id="tab-<?php echo $tab_id;?>" 
                            data-tabid="tab-<?php echo $tab_id;?>">
                            <div class="tagline"><?php echo $tab['tab_tagline'];?></div>
                            <div class="tab-content">
                                <?php foreach($tabfeatures as $feature){ 
                                    $feature_index = $feature['index'];
                                    $image_url = $feature['image']['url'];
                                    $image_id = $feature['image']['id'];
                                ?>

                                <div class="edw-feature-img-wrap" 
                                    data-si="<?php echo $feature_index;?>" 
                                    data-heading="<?php echo $feature['feature_title'];?>" 
                                    data-description="<?php echo $feature['feature_desc'];?>" 
                                    data-description_title="<?php echo $feature['feature_desc_title'];?>" 
                                    data-tabid="tab-<?php echo $tab_id;?>" 
                                    data-furl="<?php echo $image_url;?>">
                                    <img class="feature-img" 
                                        height='200px'
                                        src="<?php echo $image_url;?>" 
                                        id="<?php echo $image_id;?>" alt=""/>
                                    <span class="img-hour-text">
                                        <?php echo $feature['feature_title'];?>
                                    </span>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                <?php }} ?>

                <div class="sneakpeek-popup" id="sneakpeek-popup">
                    <i class="btn-cf-popup fa fa-times fa-2x"></i>
                    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"><span class="slider__label sr-only"></span></div>

                    <div class="cont-wrap">
                        <div class="slider-container">
                            <div class="feture-cont">
                                <span class="tag"></span>
                                <h3></h3>
                                <h5></h5>
                                <p></p>
                            </div>
                            <div class="ss-cont">
                            <?php foreach($tabs as $index => $tab){ 
                                $tab_id = $tab['tab_id'];
                                if(isset($features[$tab_id])){
                                    $tabfeatures = $features[$tab_id];
                                    foreach($tabfeatures as $feature){ 
                                        $feature_index = $feature['index'];
                                        $image_url = $feature['image']['url'];
                                        $image_id = $feature['image']['id'];
                                ?>
                                        <div class="slide-img-wrap" 
                                            data-heading="<?php echo $feature['feature_title'];?>" 
                                            data-description="<?php echo $feature['feature_desc'];?>" 
                                            data-description_title="<?php echo $feature['feature_desc_title'];?>" 
                                            data-si="<?php echo $feature_index;?>" 
                                            data-tabid="tab-<?php echo $tab_id;?>">
                                            <img class="snps-img" 
                                                data-no-lazy="1" 
                                                src="" 
                                                data-lazy="<?php echo $image_url;?>" 
                                                alt="" id="<?php echo $image_id;?>"/>
                                        </div>
                            <?php }}} ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?php
    }

    protected function _content_template() {
    }
}
Plugin::instance()->widgets_manager->register_widget_type( new MYEW_Sneakpeek_Widget() );