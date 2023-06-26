<?php
namespace Elementor;
class Wisdm_Features_Screenshots_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-features-screenshots-widget-id';
    }

    public function get_title() {
        return esc_html__( 'Wisdm Features Screenshots', 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [
            'myew-script','wisdm-features-screenshots'
        ];
    }

    public function get_icon() {
        return 'eicon-price-table';
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
                'template_format',
                [
                    'label' => __( 'Select Format', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        "image-first" => __( "Image First", "wisdm-elementor-widgets" ),
                        "text-first" => __( "Text First", "wisdm-elementor-widgets" ),
                    ],
                    'default' => "text-first"
                ]
            );
            $features = new \Elementor\Repeater();
            $features->add_control(
                'feature_title',
                [
                    'label' => __( 'Feature title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => "Feature title",
                    'label_block' => true,
                ]
            );
            $features->add_control(
                'feature_desc',
                [
                    'label' => __( 'Feature description', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => "Feature description",
                    'label_block' => true,
                ]
            );
            $features->add_control(
                'feature_screenshots',
                [
                    'label' => __( 'Screenshots', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::GALLERY,
                    'default' => []
                ]
            );
            $this->add_control(
                "features",
                [
                    'label' => __( 'Features', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $features->get_controls(),
                    'title_field' => "{{{ feature_title.substring(0,24) }}}",
                ]
            );
    
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $features = $settings['features'];
        $template_format = isset($settings['template_format']) ? $settings['template_format'] : "text-first";
        if(empty($features)) {return;};

        $lastSlideNo = 1;
        ?>
        
        <div class="wisdm-fs">
            <div class="wisdm-fs-wrap <?php echo $template_format;?>">
                <div class="wisdm-fs-content-boxes">
                    <?php foreach($features as $index => $feature) { 
                        $total_ss = count($feature['feature_screenshots']);
                        ?>
                        <div 
                            class="wisdm-fs-content <?php echo ($index == 0) ? "active" : ""; ?> " 
                            data-feature_slide_index="<?php echo $lastSlideNo; ?>"
                            data-feature_slide_last_index="<?php echo $lastSlideNo + ($total_ss -1); ?>">
                            <h4 class="wisdm-fs-title"><?php echo $feature['feature_title']; ?></h4>
                            <p class="wisdm-fs-desc"><?php echo $feature['feature_desc']; ?></p>
                        </div>
                    <?php 
                        $lastSlideNo += $total_ss;
                        } ?>
                </div>
                <div class="wisdm-fs-screenshots-boxes">
                        <div  class="wisdm-fs-screenshots-slider" >
                            <div class="swiper wisdm-fs-swiper">
                                <div class="swiper-wrapper">
                                    <?php foreach($features as $index => $feature) { ?>
                                        <?php foreach ($feature['feature_screenshots'] as $key => $value) { ?>
                                            <div class="swiper-slide">
                                                <?php 
                                                    $is_mp4 = (strpos($value['url'], 'mp4') !== false);
                                                ?>
                                                <?php if($is_mp4) { ?>
                                                    <video autoplay loop muted playsinline width="640" height="360">
                                                        <source src="<?php echo $value['url']; ?>" type="video/mp4">
                                                    </video>
                                                <?php } else { ?>
                                                    <img src="<?php echo $value['url']; ?>" width="640" height="360"/>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                                <div class="swiper-pagination"></div>
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
Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Features_Screenshots_Widget() );
