<?php
namespace WDMCommonFunctions;

class ProLandingAcfFieldsNew
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
        $this->wdmSaveBannerJson();

    // Save Compatibility Data
        $this->wdmSaveCompatJson();

    // Save Comign Soon Section Data
        $this->wdmSaveComingSoonJson();

    // Save Intro Data
        $this->wdmSaveIntroJson();

    // Save Demos Data
        $this->wdmSaveDemosJson();

    // Save Features Data
        $this->wdmSaveFeatureJson();

    // Save Features Data
        $this->wdmSaveFeatureScreenshotJson();

    // Save Products in a bundle for bundle products
        $this->wdmSaveBunProductsJson();

    // Save Screenshots Data
        $this->wdmSaveScreenshotsJson();
    
    // Save Demo Data
        $this->wdmSaveDemoJson();
    
    // Save Demo Data
        $this->wdmSaveIntegrationJson();

    // Save Documentation Data
        $this->wdmSaveDocumentationJson();

    // Save Pricing Table Plan Data
        $this->wdmSavePricingTablePlanJson();
    
    // Save Pricing Table Rows Data
        $this->wdmSavePricingTableRowsJson();

    // Save Save Money Details
        $this->wdmSaveMoneyBackDetailsJson();

    // Save Testimonial Data
        $this->wdmSaveTestimonialJson();

    // Save Faq Data
        $this->wdmSaveFaqJson();

    // Save Our Team Details
        // $this->wdmSaveOurTeamJson();

    // Unset all meta property
        unset($this->all_meta);
    }

    public function wdmSaveBannerJson()
    {
        $banner = array();
        // For Banner
        $banner = $this->saveBannerMetaData($banner);
        if (isset($this->all_meta['product_banner_p_recogn_by'][0]) && $this->all_meta['product_banner_p_recogn_by'][0] > 0) {
            $banner = $this->saveBannerRecognizedByData($banner);
        }
        if ($banner) {
            file_put_contents($this->folder_path.'/banner.json', json_encode($banner));
        }
        unset($banner);
    }

    public function saveBannerMetaData($banner)
    {
        if (isset($this->all_meta['product_banner_p_b_logo'][0])) {
            $banner['logo'] = wp_get_attachment_url($this->all_meta['product_banner_p_b_logo'][0]);
        }
        if (isset($this->all_meta['product_banner_p_b_alt_text'][0])) {
            $banner['logo_alt'] = $this->all_meta['product_banner_p_b_alt_text'][0];
        }
        if (isset($this->all_meta['product_banner_p_b_image'][0])) {
            $banner['banner_img'] = wp_get_attachment_url($this->all_meta['product_banner_p_b_image'][0]);
        }
        if (isset($this->all_meta['product_banner_p_b_title'][0])) {
            $banner['title'] = $this->all_meta['product_banner_p_b_title'][0];
        }
        if (isset($this->all_meta['product_banner_p_b_subtitle'][0])) {
            $banner['subtitle'] = $this->all_meta['product_banner_p_b_subtitle'][0];
        }
        if (isset($this->all_meta['product_banner_p_review_id'][0])) {
            $banner['review_id'] = $this->all_meta['product_banner_p_review_id'][0];
        }
        
        return $banner;
    }

    public function saveBannerRecognizedByData($banner)
    {
        for ($i=0; $i<$this->all_meta['product_banner_p_recogn_by'][0]; $i++) {
            $banner['recognized_by'][$i]['title'] = $this->all_meta['product_banner_p_recogn_by_'.$i.'_p_recogn_by_title'][0];
            $banner['recognized_by'][$i]['src'] = wp_get_attachment_url($this->all_meta['product_banner_p_recogn_by_'.$i.'_p_recogn_by_image'][0]);
        }
        return $banner;
    }

    public function wdmSaveCompatJson()
    {
        $metadata = array();
        if (isset($this->all_meta['product_compatibility_section_pitch_statement_m'][0])) {
            $metadata['mobile_title'] = $this->all_meta['product_compatibility_section_pitch_statement_m'][0];
        }
        if (isset($this->all_meta['product_compatibility_p_version'][0])) {
            $metadata['version'] = $this->all_meta['product_compatibility_p_version'][0];
        }
        if (isset($this->all_meta['product_compatibility_p_last_update'][0])) {
            $metadata['last_update'] = date("d-m-Y", strtotime($this->all_meta['product_compatibility_p_last_update'][0]));
        }
        // if (isset($this->all_meta['product_compatibility_p_changelog_link'][0])) {
        //     $metadata['changelog_link'] = $this->all_meta['product_compatibility_p_changelog_link'][0];
        // }
        $metadata = $this->saveCompatibilityList($metadata);

        if ($metadata) {
            file_put_contents($this->folder_path.'/metadata.json', json_encode($metadata));
        }
        unset($metadata);
    }

    public function wdmSaveComingSoonJson()
    {
        $metadata = array();
        if (isset($this->all_meta['coming_soon_section_coming_soon_section_show'][0])) {
            $metadata['show'] = $this->all_meta['coming_soon_section_coming_soon_section_show'][0];
        }
        if (isset($this->all_meta['coming_soon_section_coming_soon_section_show_grid'][0])) {
            $metadata['show_grid'] = $this->all_meta['coming_soon_section_coming_soon_section_show_grid'][0];
        }
        if (isset($this->all_meta['coming_soon_section_coming_soon_section_title'][0])) {
            $metadata['desc'] = $this->all_meta['coming_soon_section_coming_soon_section_title'][0];
        }
        if (isset($this->all_meta['coming_soon_section_coming_soon_section_image'][0])) {
            $metadata['image'] = wp_get_attachment_url($this->all_meta['coming_soon_section_coming_soon_section_image'][0]);
        }

        if (isset($this->all_meta['coming_soon_section_coming_soon_features'][0])) {
            for ($i=0; $i<$this->all_meta['coming_soon_section_coming_soon_features'][0]; $i++) {
                $metadata['features'][$i]['image'] = wp_get_attachment_url($this->all_meta['coming_soon_section_coming_soon_features_'.$i.'_coming_soon_features_details_coming_soon_feature_image'][0]);
                $metadata['features'][$i]['notice'] = $this->all_meta['coming_soon_section_coming_soon_features_'.$i.'_coming_soon_features_details_coming_soon_feature_notice_title'][0];
                $metadata['features'][$i]['title'] = $this->all_meta['coming_soon_section_coming_soon_features_'.$i.'_coming_soon_features_details_coming_soon_feature_title'][0];
                $metadata['features'][$i]['desc'] = $this->all_meta['coming_soon_section_coming_soon_features_'.$i.'_coming_soon_features_details_coming_soon_feature_desc'][0];
                $metadata['features'][$i]['interested_users'] = $this->all_meta['coming_soon_section_coming_soon_features_coming_soon_features_details_'.$i.'_coming_soon_features_details_coming_soon_feature_interested_users'][0];
            }
        }
        if ($metadata) {
            file_put_contents($this->folder_path.'/comingsoon.json', json_encode($metadata));
        }
        unset($metadata);
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

    public function wdmSaveIntroJson()
    {
        $intro = array();
        if (isset($this->all_meta['product_banner_p_b_video'][0])) {
            $intro['video_link'] = $this->all_meta['product_banner_p_b_video'][0];
        }
        if (isset($this->all_meta['product_banner_p_b_video_btn_text'][0])) {
            $intro['button_text'] = $this->all_meta['product_banner_p_b_video_btn_text'][0];
        }
        if (isset($this->all_meta['p_intro_p_intro_descr'][0])) {
            $intro['desc'] = $this->all_meta['p_intro_p_intro_descr'][0];
        }
        if (isset($this->all_meta['p_intro_p_intro_image'][0])) {
            $intro['image'] = wp_get_attachment_url($this->all_meta['p_intro_p_intro_image'][0]);
        }
        if (isset($this->all_meta['p_intro_p_intro_image_alt'][0])) {
            $intro['image_alt'] = $this->all_meta['p_intro_p_intro_image_alt'][0];
        }
        if (isset($this->all_meta['p_intro_p_intro_image_caption'][0])) {
            $intro['image_caption'] = $this->all_meta['p_intro_p_intro_image_caption'][0];
        }
        if (isset($this->all_meta['p_intro_p_intro_items'][0]) && $this->all_meta['p_intro_p_intro_items'][0] > 0) {
            for ($i=0; $i<$this->all_meta['p_intro_p_intro_items'][0]; $i++) {
                $intro['items'][$i]['src'] = wp_get_attachment_url($this->all_meta['p_intro_p_intro_items_'.$i.'_p_intro_item_image'][0]);
                $intro['items'][$i]['alt_txt'] = $this->all_meta['p_intro_p_intro_items_'.$i.'_p_intro_item_image_alt_text'][0];
                $intro['items'][$i]['caption'] = $this->all_meta['p_intro_p_intro_items_'.$i.'_p_intro_item_caption'][0];
            }
        }
        if ($intro) {
            file_put_contents($this->folder_path.'/intro.json', json_encode($intro));
        }
        unset($intro);
    }

    public function wdmSaveDemosJson()
    {
        $demos = array();
        if (isset($this->all_meta['p_demos_section_title'][0])) {
            $demos['title'] = $this->all_meta['p_demos_section_title'][0];
        }
        if (isset($this->all_meta['p_demos_section_items'][0]) && $this->all_meta['p_demos_section_items'][0] > 0) {
            for ($i=0; $i<$this->all_meta['p_demos_section_items'][0]; $i++) {
                $demos['items'][$i]['title'] = $this->all_meta['p_demos_section_items_'.$i.'_title'][0];
                $demos['items'][$i]['image'] = wp_get_attachment_url($this->all_meta['p_demos_section_items_'.$i.'_image'][0]);
                $demos['items'][$i]['link'] = $this->all_meta['p_demos_section_items_'.$i.'_link'][0];
                $demos['items'][$i]['image_alt'] = $this->all_meta['p_demos_section_items_'.$i.'_image_alt'][0];
            }
        }
        if ($demos) {
            file_put_contents($this->folder_path.'/demos.json', json_encode($demos));
        }
        unset($demos);
    }

    public function wdmSaveFeatureJson()
    {
        $features = $prod_feature_model_data = array();
        $features['p_f_id_prefix'] = '';
        if (isset($this->all_meta['product_feature_id_prefix'][0])) {
            $features['p_f_id_prefix'] = $this->all_meta['product_feature_id_prefix'][0];
        }
        if (defined('WDM_PRO_FEATURE')) {
            require_once('class-prod-fav-feature-model.php');
            $prod_feature_model = ProdFavFeatureModel::getInstance();
        }
        $features = $this->wdmSaveFeatureSectionMeta($features);
        if (isset($this->all_meta['product_features'][0]) && $this->all_meta['product_features'][0] > 0) {
            list($features, $prod_feature_model_data) = $this->wdmSaveFeatureJsonItems($features, $prod_feature_model_data);
        }
        if ($features) {
            if (defined('WDM_PRO_FEATURE')) {
                $prod_feature_model->addFeatures($prod_feature_model_data);
            }
            file_put_contents($this->folder_path.'/features.json', json_encode($features));
        } else {
            file_put_contents($this->folder_path.'/features.json', '');
        }
        unset($features);
    }

    public function wdmSaveFeatureScreenshotJson(){
        if (isset($this->all_meta['feature_screenshots_section'][0]) && $this->all_meta['feature_screenshots_section'][0] > 0) {
            for ($i=0; $i<$this->all_meta['feature_screenshots_section'][0]; $i++) {
                $features['items'][$i]['title'] = $this->all_meta['feature_screenshots_section_'.$i.'_title'][0];
                $features['items'][$i]['desc'] = $this->all_meta['feature_screenshots_section_'.$i.'_description'][0];

                if(isset($this->all_meta['feature_screenshots_section_'.$i.'_screenshots'][0]) && $this->all_meta['feature_screenshots_section_'.$i.'_screenshots'][0] > 0){
                    for($j=0;$j<$this->all_meta['feature_screenshots_section_'.$i.'_screenshots'][0];$j++){
                        $features['items'][$i]['src'][$j] = wp_get_attachment_url($this->all_meta['feature_screenshots_section_'.$i.'_screenshots_'.$j.'_screenshot'][0]);
                    }
                }
            }
        }
        if ($features) {
            file_put_contents($this->folder_path.'/features_screenshots.json', json_encode($features));
        } else {
            file_put_contents($this->folder_path.'/features_screenshots.json', '');
        }
        unset($features);
    }

    public function wdmSaveFeatureSectionMeta($features)
    {
        if (isset($this->all_meta['p_features_section_pitch_statement'][0])) {
            $features['desktop_title'] = $this->all_meta['p_features_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['p_features_section_pitch_statement_m'][0])) {
            $features['mobile_title'] = $this->all_meta['p_features_section_pitch_statement_m'][0];
        }
        if (isset($this->all_meta['product_features_buttons_more_feature_button_label'][0])) {
            $features['more_features_btn'] = $this->all_meta['product_features_buttons_more_feature_button_label'][0];
        }
        if (isset($this->all_meta['product_features_buttons_less_feature_button_label'][0])) {
            $features['less_features_btn'] = $this->all_meta['product_features_buttons_less_feature_button_label'][0];
        }
        return $features;
    }

    public function wdmSaveFeatureJsonItems($features, $prod_feature_model_data)
    {
        for ($i=0; $i<$this->all_meta['product_features'][0]; $i++) {
            $features['items'][$i]['src'] = wp_get_attachment_url($this->all_meta['product_features_'.$i.'_p_f_image'][0]);
            $features['items'][$i]['large_src'] = wp_get_attachment_url($this->all_meta['product_features_'.$i.'_p_f_l_image'][0]);
            $features['items'][$i]['id'] = $this->all_meta['product_features_'.$i.'_p_f_id'][0];
            $prod_feature_model_data[$i]['id'] = $features['p_f_id_prefix'] . '_' . $this->all_meta['product_features_'.$i.'_p_f_id'][0];
            $features['items'][$i]['title'] = $this->all_meta['product_features_'.$i.'_p_f_title'][0];
            $features['items'][$i]['sub_title'] = $this->all_meta['product_features_'.$i.'_p_f_subtitle'][0];
            $features['items'][$i]['tags'] = !empty($this->all_meta['product_features_'.$i.'_p_f_tags'][0])?unserialize($this->all_meta['product_features_'.$i.'_p_f_tags'][0]):array();
            $features['items'][$i]['desc'] = $this->all_meta['product_features_'.$i.'_p_f_description'][0];
            if(!empty($this->all_meta['product_features_'.$i.'_p_f_is_new'])){
                $features['items'][$i]['is_new'] = $this->all_meta['product_features_'.$i.'_p_f_is_new'][0];
            }
            if (isset($this->all_meta['product_features_'.$i.'_p_f_screenshots'][0]) && $this->all_meta['product_features_'.$i.'_p_f_screenshots'][0] > 0) {
                for ($j=0; $j<$this->all_meta['product_features_'.$i.'_p_f_screenshots'][0]; $j++) {
                    $features['items'][$i]['popups'][$j]['src'] = wp_get_attachment_url($this->all_meta['product_features_'.$i.'_p_f_screenshots_'.$j.'_p_f_screenshot_image'][0]);
                    $features['items'][$i]['popups'][$j]['desc'] = $this->all_meta['product_features_'.$i.'_p_f_screenshots_'.$j.'_p_f_screenshot_desc'][0];
                    $features['items'][$i]['popups'][$j]['youtube'] = $this->all_meta['product_features_'.$i.'_p_f_screenshots_'.$j.'_p_f_screenshot_youtube_video_id'][0];
                }
            }
        }
        return array($features,$prod_feature_model_data);
    }

    public function wdmSaveBunProductsJson(){
        if(!empty($this->all_meta['products_included'])){
            for ($i=0; $i<$this->all_meta['products_included'][0]; $i++) {
                $products['items'][$i]['title'] = $this->all_meta['products_included_'.$i.'_title'][0];
                $products['items'][$i]['details'] = $this->all_meta['products_included_'.$i.'_details'][0];
                $products['items'][$i]['image'] = wp_get_attachment_url($this->all_meta['products_included_'.$i.'_image'][0]);
            }
            file_put_contents($this->folder_path.'/products.json', json_encode($products));
            unset($products);
        }
    }

    public function wdmSaveScreenshotsJson()
    {
        $screenshots = array();
        if (isset($this->all_meta['p_screenshots_section_pitch_statement'][0])) {
            $screenshots['title'] = $this->all_meta['p_screenshots_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['product_screenshots'][0]) && $this->all_meta['product_screenshots'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_screenshots'][0]; $i++) {
                $screenshots['items'][$i]['title'] = $this->all_meta['product_screenshots_'.$i.'_p_screenshot_title'][0];
                $screenshots['items'][$i]['tagline'] = $this->all_meta['product_screenshots_'.$i.'_p_screenshot_tagline'][0];
                if (isset($this->all_meta['product_screenshots_'.$i.'_p_ss_item'][0]) && $this->all_meta['product_screenshots_'.$i.'_p_ss_item'][0] > 0) {
                    for ($j=0; $j<$this->all_meta['product_screenshots_'.$i.'_p_ss_item'][0]; $j++) {
                        $screenshots['items'][$i]['screenshots'][$j]['title'] = $this->all_meta['product_screenshots_'.$i.'_p_ss_item_'.$j.'_p_ss_item_title'][0];
                        $screenshots['items'][$i]['screenshots'][$j]['src'] = wp_get_attachment_url($this->all_meta['product_screenshots_'.$i.'_p_ss_item_'.$j.'_p_ss_item_image'][0]);
                        $screenshots['items'][$i]['screenshots'][$j]['fullscreen_src'] = wp_get_attachment_url($this->all_meta['product_screenshots_'.$i.'_p_ss_item_'.$j.'_p_ss_item_f_image'][0]);
                    }
                }
            }
        }
        if ($screenshots) {
            file_put_contents($this->folder_path.'/screenshots.json', json_encode($screenshots));
        } else {
            file_put_contents($this->folder_path.'/screenshots.json', '');
        }
        unset($screenshots);
    }

    public function wdmSaveDemoJson()
    {
        $demo = array();
        if (isset($this->all_meta['product_demo_product_demo_title'][0])) {
            $demo['title'] = $this->all_meta['product_demo_product_demo_title'][0];
        }
        if (isset($this->all_meta['product_demo_product_demo_link'][0])) {
            $demo['link'] = $this->all_meta['product_demo_product_demo_link'][0];
        }
        if (isset($this->all_meta['product_demo_product_demo_link_text'][0])) {
            $demo['link_text'] = $this->all_meta['product_demo_product_demo_link_text'][0];
        }
        
        if ($demo) {
            file_put_contents($this->folder_path.'/demo.json', json_encode($demo));
        }
        unset($demo);
    }

    public function wdmSaveIntegrationJson()
    {
        $integrations = array();
        if (isset($this->all_meta['p_integrations_section_pitch_statement'][0])) {
            $integrations['title'] = $this->all_meta['p_integrations_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['product_integrations'][0]) && $this->all_meta['product_integrations'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_integrations'][0]; $i++) {
                $integrations['items'][$i]['title'] = $this->all_meta['product_integrations_'.$i.'_p_integrations_title'][0];
                $integrations['items'][$i]['src'] = wp_get_attachment_url($this->all_meta['product_integrations_'.$i.'_p_integrations_image'][0]);
                $integrations['items'][$i]['hover_src'] = wp_get_attachment_url($this->all_meta['product_integrations_'.$i.'_p_integrations_h_image'][0]);
                $integrations['items'][$i]['src_mobile'] = wp_get_attachment_url($this->all_meta['product_integrations_'.$i.'_p_integrations_m_image'][0]);
            }
        }
        if ($integrations) {
            file_put_contents($this->folder_path.'/integrations.json', json_encode($integrations));
        } else {
            file_put_contents($this->folder_path.'/integrations.json', '');
        }
        unset($integrations);
    }

    public function wdmSaveDocumentationJson()
    {
        $documentations = array();
        if (isset($this->all_meta['p_documentations_section_pitch_statement'][0])) {
            $documentations['title'] = $this->all_meta['p_documentations_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['product_documentations'][0]) && $this->all_meta['product_documentations'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_documentations'][0]; $i++) {
                $documentations['items'][$i]['src'] = wp_get_attachment_url($this->all_meta['product_documentations_'.$i.'_p_doc_image'][0]);
                $documentations['items'][$i]['src_mobile'] = wp_get_attachment_url($this->all_meta['product_documentations_'.$i.'_p_doc_m_image'][0]);
                $documentations['items'][$i]['title'] = $this->all_meta['product_documentations_'.$i.'_p_doc_title'][0];
                $documentations['items'][$i]['desc'] = $this->all_meta['product_documentations_'.$i.'_p_doc_description'][0];
                $documentations['items'][$i]['link'] = $this->all_meta['product_documentations_'.$i.'_p_doc_link'][0];
            }
        }
        if ($documentations) {
            file_put_contents($this->folder_path.'/documentations.json', json_encode($documentations));
        }
        unset($documentations);
    }

    public function wdmSavePricingTablePlanJson()
    {
        $pricing_table_plans = array();
        if (isset($this->all_meta['p_pricing_section_pitch_statement'][0])) {
            $pricing_table_plans['title'] = $this->all_meta['p_pricing_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['p_pricing_section_pitch_substatement'][0])) {
            $pricing_table_plans['sub_title'] = $this->all_meta['p_pricing_section_pitch_substatement'][0];
        }
        if (isset($this->all_meta['below_pricing_note'][0])) {
            $pricing_table_plans['note_text'] = $this->all_meta['below_pricing_note'][0];
        }
        if (isset($this->all_meta['p_pricing_table_plans'][0]) && $this->all_meta['p_pricing_table_plans'][0] > 0) {
            for ($i=0; $i<$this->all_meta['p_pricing_table_plans'][0]; $i++) {
                $pricing_table_plans['items'][$i]['id'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_id'][0];
                $pricing_table_plans['items'][$i]['color'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_color'][0];
                $pricing_table_plans['items'][$i]['order'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_order'][0];
                $pricing_table_plans['items'][$i]['title'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_title'][0];
                $pricing_table_plans['items'][$i]['regular_price_yr'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_regular_price_yearly'][0];
                $pricing_table_plans['items'][$i]['sale_price_yr'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_sale_price_yearly'][0];
                $pricing_table_plans['items'][$i]['sale_discount_yr'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_sale_discount_yearly'][0];
                $pricing_table_plans['items'][$i]['regular_price_lyf'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_regular_price_lifetime'][0];
                $pricing_table_plans['items'][$i]['sale_price_lyf'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_sale_price_lifetime'][0];
                $pricing_table_plans['items'][$i]['sale_discount_lyf'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_sale_discount_lifetime'][0];
                $pricing_table_plans['items'][$i]['licence_type'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_licence_type'][0];
                $pricing_table_plans['items'][$i]['licence_applicable_for'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_licence_applicable_for'][0];
                $pricing_table_plans['items'][$i]['highlighted'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_is_highlighted'][0];
                $pricing_table_plans['items'][$i]['popular'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_is_popular'][0];
                if(!empty($this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_is_upgrade'])){
                    $pricing_table_plans['items'][$i]['upgrade'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_is_upgrade'][0];
                }
                $pricing_table_plans['items'][$i]['btn_item_id'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_edd_download_item_id'][0];
                $pricing_table_plans['items'][$i]['btn_price_id_yr'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_edd_download_price_id_yr'][0];
                $pricing_table_plans['items'][$i]['btn_price_id_lyf'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_edd_download_price_id_lyf'][0];
                $pricing_table_plans['items'][$i]['buy_now'] = $this->all_meta['p_pricing_table_plans_'.$i.'_p_pricing_table_plan_buy_now_label'][0];
            }
            $features_content=json_decode(file_get_contents($this->folder_path.'/features.json'), true);
            if ($features_content) {
                foreach ($features_content['items'] as $key => $feature) {
                    $i = 0;
                    foreach ($pricing_table_plans['items'] as $plan) {
                        if (in_array($plan['title'], $feature['tags'])) {
                            $features_content['items'][$key]['tags'][$i] = array('color'=>$plan['color'],'value'=>$features_content['items'][$key]['tags'][0]);
                            $i++;
                        }
                    }
                }
                file_put_contents($this->folder_path.'/features.json', json_encode($features_content));
            }
        }
        if ($pricing_table_plans) {
            file_put_contents($this->folder_path.'/pricing_table_plans.json', json_encode($pricing_table_plans));
        }
        unset($pricing_table_plans);
    }

    public function wdmSavePricingTableRowsJson()
    {
        $pricing_table_rows = $plan_feature_counts = array();
        if (isset($this->all_meta['p_pricing_table_rows'][0]) && $this->all_meta['p_pricing_table_rows'][0] > 0) {
            $counter_j = $counter_k = 0;
            $plan_id = array();
            for ($l=0; $l<$this->all_meta['p_pricing_table_plans'][0]; $l++) {
                $plan_id[] = $this->all_meta['p_pricing_table_plans_'.$l.'_p_pricing_table_plan_id'][0];
                $plan_feature_counts[$this->all_meta['p_pricing_table_plans_'.$l.'_p_pricing_table_plan_id'][0]] = 0;
            }
            for ($i=0; $i<$this->all_meta['p_pricing_table_rows'][0]; $i++) {
                if ($this->all_meta['p_pricing_table_rows_'.$i.'_p_pricing_table_plans_feature_is_highlighted'][0] == 1) {
                    $this->wdmSaveTableHighlights($pricing_table_rows, $plan_feature_counts, $plan_id, $counter_j, $i);
                    if ($this->all_meta['p_pricing_table_rows_'.$i.'_p_pricing_table_plans_feature_is_last_child'][0] == 1) {
                        $counter_j++;
                    }
                } else {
                    $this->wdmSaveTableFeatures($pricing_table_rows, $plan_feature_counts, $plan_id, $counter_k, $i);
                }
            }
        }
        $pricing_table_plans_content=json_decode(file_get_contents($this->folder_path.'/pricing_table_plans.json'), true);
        if ($plan_feature_counts && $pricing_table_plans_content) {
            foreach ($pricing_table_plans_content as $key => $pricing_table_plans) {
                if (isset($plan_feature_counts[$pricing_table_plans['id']])) {
                    $pricing_table_plans_content[$key]['total_features'] = $plan_feature_counts[$pricing_table_plans['id']];
                }
            }
            file_put_contents($this->folder_path.'/pricing_table_plans.json', json_encode($pricing_table_plans_content));
        }
        if ($pricing_table_rows) {
            file_put_contents($this->folder_path.'/pricing_table_rows.json', json_encode($pricing_table_rows, JSON_FORCE_OBJECT));
        }
        unset($pricing_table_rows);
    }

    public function wdmSaveTableHighlights(&$pricing_table_rows, &$plan_feature_counts, $plan_id, &$counter_j, $counter_i)
    {
        $pricing_table_single_rows = array();
        // $pricing_table_rows['highlights'][$counter_j]['title'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_title'][0];
        $pricing_table_single_rows['title'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_title'][0];

        $pricing_table_single_rows['sub_title'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_subtitle'][0];
        $pricing_table_single_rows['short_desc'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_sdesc'][0];
        if ($this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_is_group_parent'][0] == 1) {
            $pricing_table_single_rows['is_parent'] = 1;
            if($this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_price_yearly'][0]){
                $pricing_table_single_rows['p_yearly'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_price_yearly'][0];
            }
            if($this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_price_lifetime'][0]){
                $pricing_table_single_rows['p_lifetime'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_price_lifetime'][0];
            }
        } else {
            $pricing_table_single_rows['is_parent'] = 0;
        }

        if ($this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_is_last_child'][0] == 1) {
            $pricing_table_single_rows['is_last_child'] = 1;
        } else {
            $pricing_table_single_rows['is_last_child'] = 0;
        }
        
        if ($this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_appfor'][0]) {
            if (isset($this->all_meta['p_pricing_table_plans'][0]) && $this->all_meta['p_pricing_table_plans'][0] > 0) {
                $set_plan_id = explode(',', $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_appfor'][0]);
                foreach ($plan_id as $id) {
                    if (in_array($id, $set_plan_id)) {
                        $pricing_table_single_rows['applicable_for'][] = 1;
                        $plan_feature_counts[$id] += 1;
                    } else {
                        $pricing_table_single_rows['applicable_for'][] = 0;
                    }
                }
            }
        }

        $pricing_table_rows['highlights'][$counter_j][] = $pricing_table_single_rows;
        // $counter_j++;
    }

    public function wdmSaveTableFeatures(&$pricing_table_rows, &$plan_feature_counts, $plan_id, &$counter_k, $counter_i)
    {
        $pricing_table_rows['features'][$counter_k]['title'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_title'][0];
        $pricing_table_rows['features'][$counter_k]['sub_title'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_subtitle'][0];
        $pricing_table_rows['features'][$counter_k]['short_desc'] = $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_sdesc'][0];

        if ($this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_is_group_parent'][0] == 1) {
            $pricing_table_rows['features'][$counter_k]['is_parent'] = 1;
        } else {
            $pricing_table_rows['features'][$counter_k]['is_parent'] = 0;
        }
        
        if ($this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_is_last_child'][0] == 1) {
            $pricing_table_rows['features'][$counter_k]['is_last_child'] = 1;
        } else {
            $pricing_table_rows['features'][$counter_k]['is_last_child'] = 0;
        }

        if ($this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_appfor'][0]) {
            if (isset($this->all_meta['p_pricing_table_plans'][0]) && $this->all_meta['p_pricing_table_plans'][0] > 0) {
                $set_plan_id = explode(',', $this->all_meta['p_pricing_table_rows_'.$counter_i.'_p_pricing_table_plans_feature_appfor'][0]);
                foreach ($plan_id as $id) {
                    if (in_array($id, $set_plan_id)) {
                        $pricing_table_rows['features'][$counter_k]['applicable_for'][] = 1;
                        $plan_feature_counts[$id] += 1;
                    } else {
                        $pricing_table_rows['features'][$counter_k]['applicable_for'][] = 0;
                    }
                }
            }
        }
        $counter_k++;
    }

    public function wdmSaveMoneyBackDetailsJson()
    {
        $savemoney = array();
        if (isset($this->all_meta['p_save_money_details_title'][0])) {
            $savemoney['title'] = $this->all_meta['p_save_money_details_title'][0];
        }
        if (isset($this->all_meta['p_save_money_details_sub_title'][0])) {
            $savemoney['sub_title'] = $this->all_meta['p_save_money_details_sub_title'][0];
        }
        if (isset($this->all_meta['p_save_money_details_image'][0])) {
            $savemoney['image'] = wp_get_attachment_url($this->all_meta['p_save_money_details_image'][0]);
        }
        if (isset($this->all_meta['p_save_money_details_image_alt'][0])) {
            $savemoney['image_alt'] = $this->all_meta['p_save_money_details_image_alt'][0];
        }
        if ($savemoney) {
            file_put_contents($this->folder_path.'/money_back.json', json_encode($savemoney));
        }
        unset($savemoney);
    }

    public function wdmSaveTestimonialJson()
    {
        $testimonials = array();
        if (isset($this->all_meta['p_testimonials_section_pitch_statement'][0])) {
            $testimonials['title'] = $this->all_meta['p_testimonials_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['product_testimonials'][0]) && $this->all_meta['product_testimonials'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_testimonials'][0]; $i++) {
                $testimonials['items'][$i]['author_thumbnail'] = wp_get_attachment_url($this->all_meta['product_testimonials_'.$i.'_p_t_thumbnail'][0]);
                $testimonials['items'][$i]['author_name'] = $this->all_meta['product_testimonials_'.$i.'_p_t_authorname'][0];
                $testimonials['items'][$i]['author_info'] = $this->all_meta['product_testimonials_'.$i.'_p_t_authorinfo'][0];
                $testimonials['items'][$i]['author_testimonial'] = $this->all_meta['product_testimonials_'.$i.'_p_t_desc'][0];
                if (!empty($this->all_meta['product_testimonials_'.$i.'_p_t_video'][0])) {
                    $testimonials['items'][$i]['author_video'] = 'https://www.youtube.com/embed/'.$this->all_meta['product_testimonials_'.$i.'_p_t_video'][0];
                }
            }
        }
        if ($testimonials) {
            file_put_contents($this->folder_path.'/testimonials.json', json_encode($testimonials));
        }
        unset($testimonials);
    }

    public function wdmSaveFaqJson()
    {
        $faqs = array();
        if (isset($this->all_meta['product_faqs_section_pitch_statement'][0])) {
            $faqs['title'] = $this->all_meta['product_faqs_section_pitch_statement'][0];
        }
        if (isset($this->all_meta['product_faqs'][0]) && $this->all_meta['product_faqs'][0] > 0) {
            for ($i=0; $i<$this->all_meta['product_faqs'][0]; $i++) {
                $faqs['items'][$i]['title'] = $this->all_meta['product_faqs_'.$i.'_p_f_title'][0];
                $faqs['items'][$i]['info'] = $this->all_meta['product_faqs_'.$i.'_p_f_info'][0];
            }
        }
        if ($faqs) {
            file_put_contents($this->folder_path.'/faqs.json', json_encode($faqs));
        }
        unset($faqs);
    }

    public function wdmSaveOurTeamJson()
    {
        $ourteam = array();
        if (isset($this->all_meta['p_our_team_details_title'][0])) {
            $ourteam['title'] = $this->all_meta['p_our_team_details_title'][0];
        }
        if (isset($this->all_meta['p_our_team_details_sub_title'][0])) {
            $ourteam['sub_title'] = $this->all_meta['p_our_team_details_sub_title'][0];
        }
        if (isset($this->all_meta['p_our_team_details_image'][0])) {
            $ourteam['image'] = wp_get_attachment_url($this->all_meta['p_our_team_details_image'][0]);
        }
        if ($ourteam) {
            file_put_contents($this->folder_path.'/our_team.json', json_encode($ourteam));
        }
        unset($ourteam);
    }

    // To get object of the current class
    public static function getInstance($folder_path, &$all_meta)
    {
        if (!isset(self::$instance)) {
            self::$instance = new ProLandingAcfFieldsNew($folder_path, $all_meta);
        }
        return self::$instance;
    }
}
