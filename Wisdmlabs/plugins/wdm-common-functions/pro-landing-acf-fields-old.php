<?php
namespace WDMCommonFunctions;

class ProLandingAcfFieldsOld
{
    private $folder_path;
    private $all_meta;
    public static $instance;

    // To add expensive codes and to prevent direct object instantiation
    private function __construct($folder_path, &$all_meta)
    {
        $this->folder_path = $folder_path;
        $this->all_meta = $all_meta;
        unset($all_meta);
        $this->process();
    }

    public function process()
    {
    // Save Banner Data
        $this->wdmSaveBannerJsonOld();

    // Save Compatibility Data
        $this->wdmSaveCompatJsonOld();

    // Save Intro Data
        $this->wdmSaveIntroJsonOld();

    // Save Features Data
        $this->wdmSaveFeatureJsonOld();

    // Save Screenshots Data
        $this->wdmSaveScreenshotsJsonOld();
    
    // Save Demo Data
        // $this->wdmSaveIntegrationJsonOld();

    // Save Documentation Data
        // $this->wdmSaveDocumentationJsonOld();

    // Save Testimonial Data
        $this->wdmSaveTestimonialJsonOld();

    // Save Pricing Table Plan Data
        $this->wdmSavePricingTablePlanJsonOld();
    
    // Save Pricing Table Rows Data
        // $this->wdmSavePricingTableRowsJsonOld();

    // Save Partner Data
        $this->wdmSavePartnerJsonOld();

    // Save Demo Data
        $this->wdmSaveDemoJsonOld();
    
    // Save Developer Insight Data
        $this->wdmSaveDeveloperInsightJsonOld();

    // Save Faq Data
        $this->wdmSaveFaqJsonOld();

    // Save Footer CTA Data
        $this->wdmSaveFooterCTAJsonOld();

    // Unset all meta property
        unset($this->all_meta);
    }

    public function wdmSaveBannerJsonOld()
    {
        $banner = array();
    // str_replace(search, replace, subject)
    // For Banner
        if (isset($this->all_meta['p_b_image'][0])) {
            $banner['background_img'] = wp_get_attachment_url($this->all_meta['product_banner_p_b_image'][0]);
        }
        if (isset($this->all_meta['p_b_title'][0])) {
            $banner['title'] = $this->all_meta['product_banner_p_b_title'][0];
        }
        if (isset($this->all_meta['product_banner_p_b_subtitle'][0])) {
            $banner['subtitle'] = $this->all_meta['product_banner_p_b_subtitle'][0];
        }
        if (isset($this->all_meta['p_b_content'][0])) {
            $banner['content'] = $this->all_meta['product_banner_p_b_content'][0];
        }
        if (isset($this->all_meta['p_review_id'][0])) {
            $banner['item_id'] = $this->all_meta['product_banner_p_review_id'][0];
        }
        if ($banner) {
            file_put_contents($this->folder_path.'/banner.json', json_encode($banner));
        }
        unset($banner);
    }

    public function wdmSaveCompatJsonOld()
    {
        $metadata = array();
        if (isset($this->all_meta['product_compatibility_p_version'][0])) {
            $metadata['version'] = $this->all_meta['product_compatibility_p_version'][0];
        }
        if (isset($this->all_meta['product_compatibility_p_last_update'][0])) {
            $metadata['last_update'] = $this->all_meta['product_compatibility_p_last_update'][0];
        }
        // if (isset($this->all_meta['product_compatibility_p_changelog_link'][0])) {
        //     $metadata['changelog_link'] = $this->all_meta['product_compatibility_p_changelog_link'][0];
        // }
        $metadata = $this->saveCompatibilityList($metadata);
        if ($metadata) {
            file_put_contents($this->folder_path.'/metadata.json', json_encode($metadata));
        }
    }

    public function saveCompatibilityList($metadata)
    {
        if (isset($this->all_meta['product_compatibility_p_compatibility_list'][0]) && $this->all_meta['product_compatibility_p_compatibility_list'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_compatibility_p_compatibility_list'][0]; $i++) {
                $metadata['compatibility_list'][$i]['title'] = $this->all_meta['product_compatibility_p_compatibility_list_'.$i.'_p_compatibility_list_title'][0];
                $metadata['compatibility_list'][$i]['version'] = $this->all_meta['product_compatibility_p_compatibility_list_'.$i.'_p_compatibility_list_version'][0];
                $metadata['compatibility_list'][$i]['is_mandatory'] = ($this->all_meta['product_compatibility_p_compatibility_list_'.$i.'_p_compatibility_is_mandatory'][0]==1)?true:false;
            }
        }
        return $metadata;
    }

    public function wdmSaveIntroJsonOld()
    {
        $intro = array();
        if (isset($this->all_meta['p_intro_p_intro_descr'][0])) {
            $intro['content'] = $this->all_meta['p_intro_p_intro_descr'][0];
        }
        if (isset($this->all_meta['p_intro_p_intro_item_caption'][0])) {
            $intro['title'] = $this->all_meta['p_intro_p_intro_item_caption'][0];
        }
        if ($intro) {
            file_put_contents($this->folder_path.'/intro.json', json_encode($intro));
        }
    }

    public function wdmSaveFeatureJsonOld()
    {
        $features = array();
        if (isset($this->all_meta['p_features_section_pitch_statement'][0])) {
            $features['title'] = $this->all_meta['p_features_section_pitch_statement'][0];
        }
        // if (isset($this->all_meta['product_feature_id_prefix'][0])) {
        //     $features['p_f_id_prefix'] = $this->all_meta['product_feature_id_prefix'][0];
        // }
        if (isset($this->all_meta['product_features'][0]) && $this->all_meta['product_features'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_features'][0]; $i++) {
                $features['items'][$i]['src'] = wp_get_attachment_url($this->all_meta['product_features_'.$i.'_p_f_image'][0]);
                $features['items'][$i]['img_title'] = $this->all_meta['product_features_'.$i.'_p_f_image_title'][0];
                $features['items'][$i]['id'] = $this->all_meta['product_features_'.$i.'_p_f_id'][0];
                $features['items'][$i]['img_alt'] = $this->all_meta['product_features_'.$i.'_p_f_image_alt'][0];
                $features['items'][$i]['title'] = $this->all_meta['product_features_'.$i.'_p_f_title'][0];
                $features['items'][$i]['desc'] = $this->all_meta['product_features_'.$i.'_p_f_description'][0];
            }
        }
        if ($features) {
            file_put_contents($this->folder_path.'/features.json', json_encode($features));
        } else {
            file_put_contents($this->folder_path.'/features.json', '');
        }
        unset($features);
    }

    public function wdmSaveScreenshotsJsonOld()
    {
        $screenshots = array();
        if (isset($this->all_meta['p_screenshots_section_pitch_statement'][0])) {
            $screenshots['title'] = $this->all_meta['p_screenshots_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['product_screenshots'][0]) && $this->all_meta['product_screenshots'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_screenshots'][0]; $i++) {
                $screenshots['items'][$i]['caption'] = $this->all_meta['product_screenshots_'.$i.'_p_ss_item_title'][0];
                $screenshots['items'][$i]['src'] = wp_get_attachment_url($this->all_meta['product_screenshots_'.$i.'_p_ss_item_image'][0]);
            }
        }
        if ($screenshots) {
            file_put_contents($this->folder_path.'/screenshots.json', json_encode($screenshots));
        } else {
            file_put_contents($this->folder_path.'/screenshots.json', '');
        }
    }

    public function wdmSaveTestimonialJsonOld()
    {
        $testimonials = array();
        if (isset($this->all_meta['p_testimonials'][0]) && $this->all_meta['p_testimonials'][0] > 0) {
            for ($i=0; $i<$this->all_meta['p_testimonials'][0]; $i++) {
                $testimonials[$i]['author_thumbnail'] = wp_get_attachment_url($this->all_meta['p_testimonials_'.$i.'_p_testimonials_author_thumbnail'][0]);
                $testimonials[$i]['author_name'] = $this->all_meta['p_testimonials_'.$i.'_p_testimonials_author_name'][0];
                $testimonials[$i]['author_info'] = $this->all_meta['p_testimonials_'.$i.'_p_testimonials_author_info'][0];
                $testimonials[$i]['author_testimonial'] = $this->all_meta['p_testimonials_'.$i.'_p_testimonials_content'][0];
            }
        }
        if ($testimonials) {
            file_put_contents($this->folder_path.'/testimonials.json', json_encode($testimonials));
        }
    }

    public function wdmSavePricingTablePlanJsonOld()
    {
        $pricing_table_plans = array();
        if (isset($this->all_meta['p_pricing_section_title'][0])) {
            $pricing_table_plans['title'] = $this->all_meta['p_pricing_section_title'][0];
        }
        if (isset($this->all_meta['p_pricing_section_pitch_statement'][0])) {
            $pricing_table_plans['sub_title'] = $this->all_meta['p_pricing_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['p_pricing_plans'][0]) && $this->all_meta['p_pricing_plans'][0] > 0) {
            for ($i=0; $i<$this->all_meta['p_pricing_plans'][0]; $i++) {
                $pricing_table_plans['items'][$i]['applicable_for'] = $this->all_meta['p_pricing_plans_'.$i.'_p_plans_applicable_for'][0];
                $pricing_table_plans['items'][$i]['regular_price'] = $this->all_meta['p_pricing_plans_'.$i.'_p_plans_regular_price'][0];
                $pricing_table_plans['items'][$i]['sale_price'] = $this->all_meta['p_pricing_plans_'.$i.'_p_plans_sale_price'][0];
                $pricing_table_plans['items'][$i]['duration'] = $this->all_meta['p_pricing_plans_'.$i.'_p_plans_duration'][0];
                $pricing_table_plans['items'][$i]['btn_item_id'] = $this->all_meta['p_pricing_plans_'.$i.'_p_plans_button_item_id'][0];
                $pricing_table_plans['items'][$i]['btn_price_id'] = $this->all_meta['p_pricing_plans_'.$i.'_p_plans_button_price_id'][0];
                $pricing_table_plans['items'][$i]['is_highlighted'] = $this->all_meta['p_pricing_plans_'.$i.'_p_plans_is_highlighted'][0];
                if (isset($this->all_meta['p_pricing_plans_'.$i.'_p_plans_features'][0]) && $this->all_meta['p_pricing_plans_'.$i.'_p_plans_features'][0] > 0) {
                    for ($j=0; $j<$this->all_meta['p_pricing_plans_'.$i.'_p_plans_features'][0]; $j++) {
                        $pricing_table_plans['items'][$i]['features'][$j] = $this->all_meta['p_pricing_plans_'.$i.'_p_plans_features_'.$j.'_p_plans_feature'][0];
                    }
                }
            }
        }
        if ($pricing_table_plans) {
            file_put_contents($this->folder_path.'/pricing.json', json_encode($pricing_table_plans));
        }
    }

    public function wdmSavePartnerJsonOld()
    {
        $partners = array();
        if (isset($this->all_meta['p_partners_section_pitch_statement'][0])) {
            $partners['title'] = $this->all_meta['p_partners_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['p_partners'][0]) && $this->all_meta['p_partners'][0] > 0) {
            for ($i=0; $i<$this->all_meta['p_partners'][0]; $i++) {
                $partners['items'][$i]['src'] = wp_get_attachment_url($this->all_meta['p_partners_'.$i.'_p_partners_image'][0]);
                $partners['items'][$i]['alt_text'] = $this->all_meta['p_partners_'.$i.'_p_partners_image_alt'][0];
            }
        }
        if ($partners) {
            file_put_contents($this->folder_path.'/partners.json', json_encode($partners));
        }
    }

    public function wdmSaveDemoJsonOld()
    {
        $demo = array();
        if (isset($this->all_meta['product_demo_p_demo_link'][0])) {
            $demo['link'] = $this->all_meta['product_demo_p_demo_link'][0];
        }
        if (isset($this->all_meta['product_demo_p_demo_video_link'][0])) {
            $demo['video_link'] = $this->all_meta['product_demo_p_demo_video_link'][0];
        }
        if (isset($this->all_meta['product_demo_p_demo_alt_title'][0])) {
            $demo['demo_alt_title'] = $this->all_meta['product_demo_p_demo_alt_title'][0];
        }
        if (isset($this->all_meta['product_demo_p_demo_alt_desc'][0])) {
            $demo['demo_alt_desc'] = $this->all_meta['product_demo_p_demo_alt_desc'][0];
        }
        if (isset($this->all_meta['product_demo_p_demo_alt_btn_txt'][0])) {
            $demo['demo_alt_btn_txt'] = $this->all_meta['product_demo_p_demo_alt_btn_txt'][0];
        }
        if ($demo) {
            file_put_contents($this->folder_path.'/demo.json', json_encode($demo));
        }
        unset($demo);
    }

    public function wdmSaveDeveloperInsightJsonOld()
    {
        $dev_insight = array();
        if (isset($this->all_meta['product_developer_insight_p_dev_insight_title'][0])) {
            $dev_insight['title'] = $this->all_meta['product_developer_insight_p_dev_insight_title'][0];
        }
        if (isset($this->all_meta['product_developer_insight_p_dev_insight_title'][0])) {
            $dev_insight['desc'] = $this->all_meta['product_developer_insight_p_dev_insight_desc'][0];
        }
        if (isset($this->all_meta['product_developer_insight_p_dev_insight_title'][0])) {
            $dev_insight['sizes'] = wp_get_attachment_image_sizes($this->all_meta['product_developer_insight_p_dev_image'][0]);
            $dev_insight['srcset'] = wp_get_attachment_image_srcset($this->all_meta['product_developer_insight_p_dev_image'][0]);
            $dev_insight['src'] = wp_get_attachment_url($this->all_meta['product_developer_insight_p_dev_image'][0]);
        }
        if (isset($this->all_meta['product_developer_insight_p_dev_insight_title'][0])) {
            $dev_insight['name'] = $this->all_meta['product_developer_insight_p_dev_name'][0];
        }
        if (isset($this->all_meta['product_developer_insight_p_dev_insight_title'][0])) {
            $dev_insight['role'] = $this->all_meta['product_developer_insight_p_dev_role'][0];
        }
        if (isset($this->all_meta['product_developer_insight_p_dev_insight_title'][0])) {
            $dev_insight['text'] = $this->all_meta['product_developer_insight_p_dev_content'][0];
        }
        if ($dev_insight) {
            file_put_contents($this->folder_path.'/developer_insight.json', json_encode($dev_insight));
        }
        unset($dev_insight);
    }

    public function wdmSaveFaqJsonOld()
    {
        $faqs = array();
        if (isset($this->all_meta['product_faqs'][0]) && $this->all_meta['product_faqs'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_faqs'][0]; $i++) {
                $faqs[$i]['title'] = $this->all_meta['product_faqs_'.$i.'_p_f_title'][0];
                $faqs[$i]['info'] = $this->all_meta['product_faqs_'.$i.'_p_f_info'][0];
            }
        }
        if ($faqs) {
            file_put_contents($this->folder_path.'/faqs.json', json_encode($faqs));
        }
        unset($faqs);
    }

    public function wdmSaveFooterCTAJsonOld()
    {
        $footer_cta = array();
        if (isset($this->all_meta['footer_cta_footer_cta_title'][0])) {
            $footer_cta['title'] = $this->all_meta['footer_cta_footer_cta_title'][0];
        }
        if (isset($this->all_meta['footer_cta_footer_cta_link'][0])) {
            $footer_cta['link'] = $this->all_meta['footer_cta_footer_cta_link'][0];
        }
        if (isset($this->all_meta['footer_cta_footer_cta_image'][0])) {
            $footer_cta['src'] = wp_get_attachment_url($this->all_meta['footer_cta_footer_cta_image'][0]);
        }
        if ($footer_cta) {
            file_put_contents($this->folder_path.'/footer_cta.json', json_encode($footer_cta));
        }
        unset($footer_cta);
    }

    // To get object of the current class
    public static function getInstance($folder_path, &$all_meta)
    {
        if (!isset(self::$instance)) {
            self::$instance = new ProLandingAcfFieldsOld($folder_path, $all_meta);
        }
        return self::$instance;
    }
}
