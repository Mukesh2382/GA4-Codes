<?php
namespace Elementor;
class Wisdm_Feature_Blocks_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-features-blocks-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Features Blocks', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script'
        ];
    }

    public function get_icon() {
        return 'eicon-lightbox';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
            'features_section',
            [
                'label' => __( 'Features Section', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
            $this->add_control(
                'feature_title',
                [
                    'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => "Feature title",
                    'label_block' => true,
                ]
            );
            $this->add_control(
                'feature_subtitle',
                [
                    'label' => __( 'Subtitle', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => "Feature subtitle",
                    'label_block' => true,
                ]
            );
            $this->add_control(
                'feature_desc',
                [
                    'label' => __( 'Feature description', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => "Feature description",
                    'label_block' => true,
                ]
            );
            $this->add_control(
                'feature_lb_desc',
                [
                    'label' => __( 'Feature Lightbox description', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => "",
                    'label_block' => true,
                ]
            );
            $this->add_control(
                'feature_screenshot',
                [
                    'label' => __( 'Screenshot', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => \Elementor\Utils::get_placeholder_image_src(),
                    ],
                ]
            );
            
        $this->end_controls_section();
    }

    protected function render() {
        
        $settings = $this->get_settings_for_display();
        $feature_title = @$settings['feature_title'];
        $feature_subtitle = @$settings['feature_subtitle'];
        $feature_desc = @$settings['feature_desc'];
        $feature_lb_desc = empty($settings['feature_lb_desc']) ? $feature_desc : $settings['feature_lb_desc'];

        $feature_screenshot = @$settings['feature_screenshot']['url'];

        ?>
        <div class="wdm-feature-block">
            <h4 class="wdm-fb-title"><?php echo $feature_title; ?></h4>
            <h4 class="wdm-fb-subtitle"><?php echo $feature_subtitle; ?></h4>
            <div class="wdm-fb-para"><?php echo $feature_desc; ?></div>
            <div class="wdm-fb-para-hidden"><?php echo $feature_lb_desc; ?></div>
            <a class="wdm-fb-view-screenshot open-modal"  href='javascript:void(0)' data-modal='feature-box-modal' data-screenshot="<?php echo $feature_screenshot; ?>">View Screenshot</a> 
        </div>
        <?php
    }
    protected function _content_template() {

    }
}
Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Feature_Blocks_Widget() );
