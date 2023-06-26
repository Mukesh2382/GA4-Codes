<?php
namespace WDMCommonFunctions;

/**
* Class to display studies on the frontend
*/

class CaseStudies{

    // To store current class object
    private static $instance;
    private function __construct(){
        add_action('wp_enqueue_scripts', array($this,'wp_enqueue_scripts'),99);
        add_shortcode( 'wdm-sc-case-studies', array($this,'case_studies') );
    }

    public function case_studies($atts){
        $atts = shortcode_atts( array(
            'title' => null,
            'heading' => "",
            'ids' => "",
            'limit' => 10,
        ), $atts );
        $case_studies = array_filter(explode(',',$atts['ids']));
        $args = array(
            'posts_per_page' => $atts['limit'],
            'post_type' => 'case-study',
            'post_status'    => 'publish'
        );
        if(!empty($case_studies)){
            $args['post__in'] = $case_studies;
        }
        $posts = get_posts($args);

        ob_start();
        if(!empty($posts)){
            ui_case_studies_container($posts,$atts);
        }
        return ob_get_clean();
    }
    
    public function wp_enqueue_scripts(){
        wp_enqueue_script('wdm-case-studies-script', plugins_url('assets/js/wdm-case-studies.js', __FILE__), array('jquery'), '1.0.0');
        wp_enqueue_style('wdm-case-studies-style', plugins_url('assets/css/wdm-case-studies.css', __FILE__), false, CHILD_THEME_VERSION);
    }

    // To get object of the current class
    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new CaseStudies;
        }
        return self::$instance;
    }
}

$case_studies = CaseStudies::getInstance();


function ui_case_studies_container($case_studies,$atts){
    ob_start();
    ?>
    <div class="wdm-case-studies">
    <?php if(!empty($atts['heading'])) { ?>
            <h6 class="cs-heading">
                <?php echo $atts['heading'];?>
            </h6>
        <?php } ?>
        <?php if(!empty($atts['title'])) { ?>
            <h2 class="cs-title">
                <?php echo $atts['title'];?>
            </h2>
        <?php } ?>
        <div class="container">
            <div class="swiper case-study-slider">
                <div class="swiper-wrapper">
                    <?php
                    foreach ($case_studies as $key => $cs) {
                        $featured_img_url = get_the_post_thumbnail_url($cs->ID); 
                        // x($cs);
                        $title = $cs->post_title; 
                        $link = home_url() . '/case-study/' . $cs->post_name; 
                        if(!empty($featured_img_url)) {
                        ?>
                            <div class="swiper-slide case-study-item">
                                <div class="case-study-img">
                                    <a href="<?php echo $link; ?>">
                                    <img src="<?php echo $featured_img_url; ?>" alt="<?php echo $title; ?>">
                                    </a>
                                </div>
                                <div class="case-study-content">
                                    <h5 class="case-study-title">
                                        <a href="<?php echo $link; ?>" >
                                        <?php echo $title; ?>
                                        </a>
                                    </h5>
                                    <a class="read-more" href="<?php echo $link; ?>" >Read More </a>
                                </div>
                            </div>
                        <?php
                        }
                    }
                    ?>
                </div>
                <div class="swiper-button-next"> 
                    <img src="https://wisdmlabs.com/site/wp-content/themes/wisdmlabs/images/swiper/right.png" alt="right arrow" class="swiper-arr swiper-arr-r" > </div>
                <div class="swiper-button-prev"> 
                    <img src="https://wisdmlabs.com/site/wp-content/themes/wisdmlabs/images/swiper/left.png" class="swiper-arr swiper-arr-l"> 
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
    <?php 
    echo ob_get_clean();
}
