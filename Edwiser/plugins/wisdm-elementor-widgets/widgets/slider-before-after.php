<?php
namespace Elementor;
class Slider_Before_After_Widget extends Widget_Base {

    public function __construct($data = [], $args = null) {
		parent::__construct($data, $args);
	}

    public function get_name() {
        return  'slider-before-after-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Slider Before After', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [ 'slider-before-after-script' ];
    }

    public function get_style_depends() {
        return [ 'slider-before-after-style'];
    }

    public function get_icon() {
        return 'eicon-email-field';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        // Content section

        $this->start_controls_section(
            "tabs_setting",
            [
                'label' => __( "Tabs Setting", 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Title 
        $this->add_control(
            'title',
            [
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => __( 'The Title', 'wisdm-elementor-widgets' ),
                'label' => __( 'Title', 'wisdm-elementor-widgets' ),
            ]
        );
        // Tabs
        $repeater = new \Elementor\Repeater();
        $repeater->add_control(
            'tab_title',
            [
                'label' => __( 'Tab Title', 'wisdm-elementor-widgets' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Tab Title',
            ]
        );
        // After Image
        $repeater->add_control(
            'after_image',
            [
                'label' => __( 'After Image', 'wisdm-elementor-widgets' ),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );
        // Before Image
        $repeater->add_control(
            'before_image',
            [
                'label' => __( 'Before Image', 'wisdm-elementor-widgets' ),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
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
                        'tab_title' => 'Tab 1',
                        'before_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ],
                        'after_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ]
                    ],
                    [
                        'tab_title' => 'Tab 2',
                        'before_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ],
                        'after_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ]
                    ],
                    [
                        'tab_title' => 'Tab 3',
                        'before_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ],
                        'after_image' => [
                            'url' => \Elementor\Utils::get_placeholder_image_src(),
                        ]
                    ],
                ],
                'title_field' => '{{{ tab_title }}}',
            ]
        );
        $this->end_controls_section();

        $this->style_tab();
    }

    private function style_tab() {
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $title = $settings['title'];
        $tabs = $settings['tabs'];
        ?>
        <div class="before-after-slider-wrap">
            <div class='bas-title'><?php echo $title; ?></div>
            <ul class="tabs desktop-tabs">
                <?php foreach ($tabs as $key => $tab) { ?>
                    <li class="bas-tabs-list <?php echo ($key == 0) ? 'active' : '';?> " 
                    data-tid="tab-<?php echo $key;?>">
                        <span class="tab"><?php echo $tab['tab_title']; ?></span>
                    </li>
                <?php }?>
            </ul>
            <ul class="tabs mobile-tabs" style="display:none">
                <select name="" class="select_bas_tab">
                    <?php foreach ($tabs as $key => $tab) { ?>
                        <option value="<?php echo $key;?>"><?php echo $tab['tab_title']; ?></option>
                    <?php }?>
                </select>
            </ul>
            <?php foreach ($tabs as $key => $tab) { ?>
                <div class="tab-content-wrap <?php echo ($key == 0) ? 'active' : '';?>" id="tab-<?php echo $key;?>">
                    <div class="tab-content">
                        <div class='before-after-slider-box'>
                            <div class='img background-img' style='background-image:url("<?php echo $tab['before_image']['url']; ?>");'>
                                <div class='image-text-box background-img-text'>
                                    <span style='margin:5px;'>
                                        Moodle Without RemUI
                                    </span>
                                </div>
                                <div class='image-text-box' style=' position: absolute;
                                        right: 0;
                                        left: auto;
                                        z-index: 0;'>
                                    <span style='margin:5px;'>
                                        Moodle With RemUI
                                    </span>
                                </div>
                                <div class='slide-note' >
                                    <p class='slide-note-text'>Slide to experience <br>the change</p>
                                    <br>
                                    <img class='slide-note-arrow' src="<?php echo WDM_WIDGETS_PLUGIN_PATH. 'assets/slider-before-after/images/slide-arrow.png'?>" >
                                </div>
                            </div>
                            <div class='img foreground-img'
                                style='background-image:url("<?php echo $tab['after_image']['url'];; ?>");'>
                            </div>
                            <input type="range" min="1" max="100" value="50" class="ba-slider" name='ba-slider' id="ba-slider">
                            <div class='slider-button'></div>
                        </div>
                    </div>
                </div>
            <?php }?>
        </div>
        <?php
    }

    protected function _content_template() {
    }
}

Plugin::instance()->widgets_manager->register_widget_type( new Slider_Before_After_Widget() );
