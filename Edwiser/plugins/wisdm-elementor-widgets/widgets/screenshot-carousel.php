<?php
namespace Elementor;
class Wisdm_Screenshot_Carousel_Widget extends Widget_Base {

    public function get_name() {
        return  'wisdm-screenshot-carousel-widget-id';
    }

    public function get_title() {
        return esc_html__( "Wisdm Features Screenshot Carousel", 'wisdm-elementor-widgets' );
    }

    public function get_script_depends() {
        return [ 'wdm-screenshot-carousel-script', 'slider-popup-script' ];
    }

    public function get_style_depends() {
        return [ 'wdm-screenshot-carousel-style' , 'slider-popup-style'];
    }

    public function get_icon() {
        return 'eicon-testimonial-carousel';
    }

    public function get_categories() {
        return [ 'myew-for-elementor' ];
    }

    public function register_controls() {
        $this->start_controls_section(
            'content-section',
            [
                'label' => __( 'Content Settings', 'wisdm-elementor-widgets' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

            $this->add_control(
                'title',
                [
                    'label' => __( 'Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => '<h2 class="wp-block-edw-ssc-heading-text">Enter Your Title</h2>',
                    'label_block' => true
                ]
            );
            $this->add_control(
                'desc',
                [
                    'label' => __( 'Description', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::WYSIWYG,
                    'default' => WDM_WIDGETS_DEFAULT_DESC,
                    'label_block' => true
                ]
            );
            $this->add_control(
                'show_button',
                [
                    'label' => __( 'Show Button', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Show', 'wisdm-elementor-widgets' ),
                    'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );

            $this->add_control(
                'button_title',
                [
                    'label' => __( 'Button Title', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'label_off' => __( 'Hide', 'wisdm-elementor-widgets' ),
                    'label_block' => true,
                    'default' => 'View more',
                    'condition' => [
                        'show_button' => 'yes'
                    ]
                ]
            );

            $this->add_control(
                'button_link',
                [
                    'label' => __( 'Button Link', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::URL,
                    'placeholder' => __( 'https://your-link.com', 'wisdm-elementor-widgets' ),
                    'show_external' => true,
                    'default' => [
                        'url' => home_url(),
                        'is_external' => true,
                        'nofollow' => true,
                    ],
                    'condition' => [
                        'show_button' => 'yes'
                    ]
                ]
            );
    
            $this->add_control(
                'gallery',
                [
                    'label' => __( 'Add Images', 'wisdm-elementor-widgets' ),
                    'type' => \Elementor\Controls_Manager::GALLERY,
                    'default' => [],
                ]
            );
           
        $this->end_controls_section();
        // Style Tab
        $this->style_tab();
    }

    private function style_tab() {
        // Button Style
        \CommanStyles::button($this,[
            "section_label" => 'Button Style',
            "prefix" => 'view_more',
            "selector" => 'a.viewmore_btn',
        ]);

        // Description
        \CommanStyles::box($this,[
            "section_label" => 'Description box',
            "prefix" => 'description',
            "selector" => '.content .description',
        ]);

    }


    protected function render() {
        $settings = $this->get_settings_for_display();
        $gallary = $settings['gallery']; 
        $show_button = ($settings['show_button'] == 'yes'); 
        $button_link = $settings['button_link']; 
        $button_link_is_external = $button_link['is_external']; 
        $link_target = ($button_link_is_external) ? "target='_blank'" : ""; 
        $button_link_url = $button_link['url']; 
        $button_title = $settings['button_title']; 
        $title = $settings['title']; 
        $desc = $settings['desc']; 
        ?>
        <div class="edw-screenshot-carousel">
            <div class="content-wrap" >
            
                <div class="content-head">
                    <div class="tagline"><?php echo $title;?></div>
                    <?php if($show_button) { ?>
                    <div class="viewmore_wrap">
                        <a 
                            class='viewmore_btn'
                            <?php echo $link_target;?>
                            href="<?php echo $button_link_url;?>">
                            <?php echo $button_title;?>
                        </a>
                    </div>
                    <?php } ?>
                </div>
                
                <div class="content">
                    <div class="description">
                        <?php echo $desc; ?>
                    </div>
                    <?php 
                        $screenshot_index = -1;
                        foreach($gallary as $screenshot){ 
                            $screenshot_index++;
                            $image_url = $screenshot['url'];
                            $image_id = $screenshot['id'];
                    ?>
                            <div class="ss-img-wrap" 
                                data-si="<?php echo $screenshot_index;?>" 
                                data-furl="<?php echo $image_url;?>">
                                <img class="feature-img" 
                                    height='200px'
                                    src="<?php echo $image_url;?>" 
                                    id="<?php echo $image_id;?>" alt=""/>
                            </div>
                    <?php } ?>
                </div>
            </div>

            <div class="ss-carousel-popup">
                    <i class="btn-cf-popup fa fa-times fa-2x"></i>
                    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"><span class="slider__label sr-only"></span></div>
                    <div class="cont-wrap">
                        <div class="slider-container">
                            <div class="ss-cont">
                                <?php 
                                    $screenshot_index = -1;
                                    foreach($gallary as $screenshot){ 
                                        $screenshot_index++;
                                        $image_url = $screenshot['url'];
                                        $image_id = $screenshot['id'];
                                ?>
                                        <div class="slide-img-wrap" 
                                            data-si="<?php echo $screenshot_index;?>" >
                                            <img class="snps-img" 
                                                data-no-lazy="1" 
                                                src="" 
                                                data-lazy="<?php echo $image_url;?>" 
                                                alt="" id="<?php echo $image_id;?>"/>
                                        </div>
                                <?php } ?>
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

Plugin::instance()->widgets_manager->register_widget_type( new Wisdm_Screenshot_Carousel_Widget
() );