<?php
namespace WDMCommonFunctions;

/**
* Class to handle Custom CTA Blogs
*/

class CustomBlogCta
{
    private static $instance;

    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
    }

    function found_in_cart($download){
        $download_id = $download['id'];
        $price_id = $download['price_id'];
        $download_price_id = $price_id ? $download_id . "_" . $price_id : $download_id;
        $cart_contents = edd_get_cart_contents();
        $products = [];
        foreach ($cart_contents as $key => $downloads) {
            $download_id = $downloads['id'];
            $price_id = isset($downloads['options']['price_id']) && !empty($downloads['options']['price_id']) ? $downloads['options']['price_id'] : false;
            $current_download_price_id = $price_id ? $download_id . "_" . $price_id : $download_id;
            $products[] = $current_download_price_id;
        }
        return in_array($download_price_id , $products);
    }

    function remove_existing_before_upsell($add_download_id, $options){
        if(isset($_GET['remove_download_id']) && isset($_GET['remove_price_id'])){
            $remove_download_id = trim($_GET['remove_download_id']);
            $remove_price_id = trim($_GET['remove_price_id']);
            $cart_contents = edd_get_cart_contents();
            foreach ($cart_contents as $key => $downloads) {
                $download_id = $downloads['id'];
                $price_id = isset($downloads['options']['price_id']) && !empty($downloads['options']['price_id']) ? $downloads['options']['price_id'] : false;
                if($remove_download_id == $download_id && $price_id == $remove_price_id){
                    edd_remove_from_cart($key);
                    break;
                }
            }
        }
    }

    function remove_query_args(){
        if(edd_straight_to_checkout() ){
            if(!isset($_GET['edd_action']) && isset($_GET['remove_download_id']) && isset($_GET['remove_price_id'])){
                $query_args 	= remove_query_arg( array( 'remove_download_id', 'remove_price_id') );
                $query_part 	= strpos( $query_args, "?" );
                $url_parameters = '';
                if ( false !== $query_part ) {
                    $url_parameters = substr( $query_args, $query_part );
                }
                wp_redirect( edd_get_checkout_uri() . $url_parameters, 303 );
            }
        }
    }

    public function upSellLeapCtsShortcodeCallback($atts){
        ob_start();
        $leap_annual = ["id"=>"368714" , "price_id"=> "1"];
        $leap_lifetime = ["id"=>"368714" , "price_id"=> "2"];
        $leap_plus_elumine_annual = ["id"=>"366221" , "price_id"=> "1"];
        $leap_plus_elumine_lifetime = ["id"=>"366221" , "price_id"=> "2"];

        $annual_leap_found_in_cart = $this->found_in_cart($leap_annual);
        $lifetime_leap_found_in_cart = $this->found_in_cart($leap_lifetime);
        $upsell_found_in_cart = $this->found_in_cart($leap_plus_elumine_annual) || $this->found_in_cart($leap_plus_elumine_lifetime) ;

        if(!$upsell_found_in_cart && ($annual_leap_found_in_cart || $lifetime_leap_found_in_cart)){

            $leap_variable_prices = edd_get_variable_prices($leap_annual['id']);
            $leap_plus_elumine_variable_prices = edd_get_variable_prices($leap_plus_elumine_annual['id']);
            if($annual_leap_found_in_cart ){
                $download_id = $leap_plus_elumine_annual['id'];
                $price_id = $leap_plus_elumine_annual['price_id'];
                $remove_download_id = $leap_annual['id'];
                $remove_price_id = $leap_annual['price_id'];
                $leap_price = $leap_variable_prices[$price_id]['amount'];
                $leap_plus_elumine_price = $leap_plus_elumine_variable_prices[$price_id]['amount'];
            }
            else if($lifetime_leap_found_in_cart){
                $download_id = $leap_plus_elumine_lifetime['id'];
                $price_id = $leap_plus_elumine_lifetime['price_id'];
                $remove_download_id = $leap_lifetime['id'];
                $remove_price_id = $leap_lifetime['price_id'];
                $leap_price = $leap_variable_prices[$price_id]['amount'];
                $leap_plus_elumine_price = $leap_plus_elumine_variable_prices[$price_id]['amount'];
            }

            $diff =  $leap_plus_elumine_price - $leap_price;
            $link = home_url() . "/checkout?edd_action=add_to_cart&download_id=$download_id&edd_options[price_id]=$price_id&remove_download_id=$remove_download_id&remove_price_id=$remove_price_id";
            $link = urlencode($link);
            $popupcontent ='Get our feature-rich, visually captivating, best-selling theme designed exclusively for LearnDash for just $'.$diff.' more! <div style="display:flex;justify-content: center;"><ul style="text-align:left;"> <li>Tailored layouts for LearnDash pages</li> <li>Powerful features to enhance your website</li> <li>Seamless integration with all LearnDash add-ons</li> <li>One-click Demo Import</li> </ul></div>';
            $popuptitle = "Add butter-smooth user experience to your palette of powerful LEAP features with eLumine.";
            echo do_shortcode( "[wdm_custom_cta 
                    link='$link' 
                    popuptitle='$popuptitle'
                    popupcontent='$popupcontent'
                    popupid='popup_999999'
                    exitintent='1'
                    buttonlabel='Get eLumine + LearnDash Essential Addon Pack(LEAP)'
                /]" 
            );
        }
        return ob_get_clean();
    }

    public function shortcodeCallback($atts)
    {
        ob_start();
        $atts = shortcode_atts(
            array(
                'appendcta'                 => '0',
                'onlycta'                   => '0',
                'nobutton'                  => '0',
                'showinsidebar'             => '0',
                'popup'                     => '0',
                'link'                      => '',
                'buttoncssclass'            => '',
                'buttonlabel'               => '',
                'popuptitle'                => '',
                'ctatitle'                  => '',
                'popupcontent'              => '',
                'ctacontent'                => '',
                'exitintent'                => '0',
                'halfscroll'                => '0',
                'woomigratetype'            => '',
                'popupid'                   => '0',
                'autoshow'                  => '0',
                'footer'                    => '',
                'width_in_percent'          => '',
                'popupcontentfontsize'        => '',
                'template_shortcode' => null,
            ),
            $atts,
            'wdm_custom_cta'
        );
        if (!$atts['popupid']) {
            $atts['popupid'] = 'popup_'.rand();
        }
        // if($atts['woomigratetype']){
            $fixed_div = $this->generateFixedDiv($atts);
        // }
        $atts['link'] = urldecode($atts['link']);
        if ($atts['popup'] || $atts['link'] || $atts['template_shortcode']) {
            $this->generatePopup($atts);
        }
        $this->jQueryProcessing($atts, $fixed_div);
        return ob_get_clean();
    }

    public function generateFixedDiv($atts)
    {
        $fixed_div = '';
        if ($atts['woomigratetype']=='Migration Checklist') {
            $buttonlabel = $atts['buttonlabel']?$atts['buttonlabel']:'Download Now';
            if ($atts['showinsidebar']) {
                $cssclass = "migration-cta-form-open";
                if ($atts['buttoncssclass']) {
                    $cssclass = $atts['buttoncssclass'];
                }
                $fixed_div = '<div class="migration-checklist sidebar-cta banner light banner-3 quote-banner" style="text-align: center;"><h3 style="text-align: center;">Get Your Free Subscription Migration Checklist</h3><a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right '.$cssclass.'" style="margin: 10px;display:block;" href="#" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">'.$buttonlabel.'</a></div>';
            }
            if ($atts['appendcta']=="1") {
                if($atts['nobutton']=="1"){
                    // Add nothing
                }elseif($atts['onlycta']=="1"){
                    echo '<a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right only-cta-btn-banner '.$cssclass.'" style="margin: 10px;" href="#" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">Download Now</a>';
                }else{
                    echo '<div class="appended-sidebar-cta banner light banner-3 quote-banner" style="text-align:center;display:none;margin:1rem 0 1rem 0"><h3 style="text-align: center;">Get Your Free Subscription Migration Checklist</h3><a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right '.$cssclass.'" style="margin: 10px;" href="#" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">Download Now</a></div>';
                }
            }
        } elseif ($atts['woomigratetype']=='Migration Plan') {
            $buttonlabel = $atts['buttonlabel']?$atts['buttonlabel']:'Get a Custom Migration Plan';
            if ($atts['showinsidebar']) {
                $link = $atts['link']?$atts['link']:'https://wisdmlabs.com/woocommerce-subscriptions-migration/';
                $fixed_div = '<div class="sidebar-cta migration-plan banner light banner-3 quote-banner" style="text-align: center;"><h3 style="text-align: center;">Planning to move to WooCommerce Subscriptions?</h3><a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right" style="margin: 10px;display:block;" href="'.$link.'" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">'.$buttonlabel.'</a></div>';
            }
            if ($atts['appendcta']=="1") {
                if($atts['nobutton']=="1"){
                    // Add nothing
                }elseif($atts['onlycta']=="1"){
                    echo '<a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right only-cta-btn-banner" style="margin: 10px;" href="https://wisdmlabs.com/woocommerce-subscriptions-migration/" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">'.$buttonlabel.'</a>';
                }else{
                    echo '<div class="appended-sidebar-cta banner light banner-3 quote-banner" style="text-align:center;display:none;margin:1rem 0 1rem 0"><h3 style="text-align: center;">Planning to move to WooCommerce Subscriptions?</h3><a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right" style="margin: 10px;" href="https://wisdmlabs.com/woocommerce-subscriptions-migration/" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">'.$buttonlabel.'</a></div>';
                }
            }
        } else {
            $buttonlabel = $atts['buttonlabel']?$atts['buttonlabel']:'Download Now';
            if ($atts['showinsidebar']) {
                $cssclass = $atts['buttoncssclass']?$atts['buttoncssclass']:'migration-cta-form-open';
                $ctatitle = $atts['ctatitle']?$atts['ctatitle']:'Get Your Free Subscription Migration Checklist';
                $ctacontent = $atts['ctacontent']?'<p>'.$atts['ctacontent'].'</p>':'';
                $buttonlabel = $atts['buttonlabel']?$atts['buttonlabel']:'Download Now';
                $fixed_div = '<div class="migration-checklist sidebar-cta banner light banner-3 quote-banner" style="text-align: center;"><h3 style="text-align: center;">'.$ctatitle.'</h3>'.$ctacontent.'<a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right '.$cssclass.'" style="margin: 10px;display:block;" href="#" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">'.$buttonlabel.'</a></div>';
            }
            if ($atts['appendcta']=="1") {
                $cssclass = $atts['buttoncssclass']?$atts['buttoncssclass']:'migration-cta-form-open';
                $ctatitle = $atts['ctatitle']?$atts['ctatitle']:'Get Your Free Subscription Migration Checklist';
                $ctacontent = $atts['ctacontent']?'<p>'.$atts['ctacontent'].'</p>':'';
                $buttonlabel = $atts['buttonlabel']?$atts['buttonlabel']:'Download Now';
                if($atts['nobutton']=="1"){
                    // Add nothing
                }elseif($atts['onlycta']=="1"){
                    echo '<a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right only-cta-btn-banner '.$cssclass.'" style="margin: 10px;" href="#" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">'.$buttonlabel.'</a></div>';
                }else{
                    echo '<div class="appended-sidebar-cta banner light banner-3 quote-banner" style="text-align:center;display:none;margin:1rem 0 1rem 0"><h3 style="text-align: center;">'.$ctatitle.'</h3>'.$ctacontent.'<a class="btn btn-normal btn-icon-left btn-primary radius-3px ui--animation pull-right '.$cssclass.'" style="margin: 10px;" href="#" target="_blank" rel="noopener noreferrer" data-popup="'.$atts['popupid'].'">'.$buttonlabel.'</a></div>';
                }
            }
        }
        return $fixed_div;
    }

    public function generatePopup($atts)
    {
        // To show migration checklist form in a popup
        if ($atts['popup'] || $atts['link'] || $atts['template_shortcode']) {
            ?>
            <div id="<?php echo $atts['popupid'];?>" class="migration-popup-container" style="display: none;">
                <div class="migration-content-wrap <?php echo ($atts['link'])?'migration-content-wrap-with-linkcta':''?>" <?php echo $atts['width_in_percent']?'style="width:'.$atts['width_in_percent'].'%"':''?>>
                    <span class="migration-close">
                        <img draggable="false" role="img" class="emoji" alt="✖" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2716.svg">
                    </span>
                    <div class="popup_content">
                        <?php 
                        if(isset($atts['template_shortcode'])){
                            echo do_shortcode("[elementor-template id='".$atts['template_shortcode']."']");
                        }
                        else{
                            if ($atts['popuptitle']) {?>
                                <h1 style="color:#a73232;font-weight:700<?php echo ($atts['link'])?';margin:0 0 2.4rem':''?>"><?php echo $atts['popuptitle'];?></h1>
                            <?php }
                            if ($atts['popupcontent']) {?>
                                <p <?php echo ($atts['link'])?'style="font-size:'.($atts['popupcontentfontsize']?$atts['popupcontentfontsize']:'1.8rem').'"':''?>><?php echo $atts['popupcontent'];?></p>
                                <?php
                            }
                            if($atts['popup']){
                                echo do_shortcode('[contact-form-7 id="'.$atts['popup'].'"]');
                            }
                            if($atts['link']){
                                if(!$atts['buttonlabel']){
                                    $atts['buttonlabel'] = 'Get';
                                }
                                echo '<a style="float:none;display:inline-block" href="'.$atts['link'].'" target="_blank" rel="noopener noreferrer"><button style="border-radius: 3px!important">'.$atts['buttonlabel'].'</button></a>';
                            }
                            if($atts['footer']){
                                echo $atts['footer'];
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php if ($atts['showinsidebar'] || $atts['appendcta'] || $atts['exitintent'] || $atts['halfscroll']) {?>
                <script>
                    jQuery(document).ready(function(){
                        if(!(jQuery('#cta-popup-style').length>0)){
                            jQuery('.migration-popup-container').append(
                                '<style id="cta-popup-style">.migration-popup-container {position: fixed;top: 0;left: 0;width: 100%;height: 100%;z-index: 9999;background-color: #00000085;display: flex;justify-content: center;align-items: center;display: none;overflow: auto;}.migration-content-wrap {width: 70%;background-color: #fff;padding: 60px;text-align: center;position:relative;max-width:100%;overflow-y:auto;overflow-x:hidden;max-height: 500px;}.migration-content-wrap-with-linkcta{width:45%;padding:30px}.migration-close {background-color: #fff;position: absolute;top: 0px;right: 0px;    cursor: pointer;width: 35px;height: 35px;display: flex;align-items: center;justify-content: center;border-radius: 50%;}div.wpcf7-response-output{margin:.5em .5em 1em}.migration-content-wrap .cf7-submit-btn{padding: 2%;margin: 1%;}.migration-content-wrap label.your-name-label,.migration-content-wrap label.your-email-label{width: 47%;display: inline-block;margin: 1%;}.migration-content-wrap span.your-name input,.migration-content-wrap span.your-email input{padding: 4%;}.migration-content-wrap .subscribe-label{display: block;margin: 3%;}img.emoji {display: inline !important;border: none !important;box-shadow: none !important;height: 1em !important;width: 1em !important;margin: 0 .07em !important;vertical-align: -0.1em !important;background: none !important;padding: 0 !important;}p.submit-para{margin-bottom: 0.5rem}@media only screen and (max-width: 767px){.migration-content-wrap .migration-content-wrap{padding: 20px;}.migration-content-wrap label.your-name-label,.migration-content-wrap label.your-email-label{width: 94%;display: block;margin: 3%;}.migration-content-wrap .subscribe-label{margin: 1%;}}</style>'
                                );
                        }
                    });
                </script>
                <?php
            }
            // Local 377978
            // Live 388232
            if ($atts['popup']=='376169' || $atts['popup']=='388232') { ?>
                <script>
                        if(!(jQuery('#cta-popup-style-376169').length>0)){
                            jQuery('.migration-popup-container').append(
                                '<style id="cta-popup-style-376169">div.wpcf7 .screen-reader-response{position:absolute;overflow:hidden;clip:rect(1px,1px,1px,1px);height:1px;width:1px;margin:0;padding:0;border:0}div.wpcf7-response-output{margin:.5em .5em 1em;padding:.2em 1em;border:2px solid red}div.wpcf7-mail-sent-ok{border:2px solid #398f14}div.wpcf7-aborted,div.wpcf7-mail-sent-ng{border:2px solid red}div.wpcf7-spam-blocked{border:2px solid orange}div.wpcf7-acceptance-missing,div.wpcf7-validation-errors{border:2px solid #f7e700}.wpcf7-form-control-wrap{position:relative}.use-floating-validation-tip span.wpcf7-not-valid-tip{position:absolute;top:20%;left:20%;z-index:100;border:1px solid red;background:#fff;padding:.2em .8em}span.wpcf7-list-item{display:inline-block;margin:0 0 0 1em}span.wpcf7-list-item-label:after,span.wpcf7-list-item-label:before{content:" "}.wpcf7-display-none{display:none}div.wpcf7 .ajax-loader{visibility:hidden;display:inline-block;background-image:url(wp-content/plugins/contact-form-7/images/ajax-loader.gif);width:16px;height:16px;border:none;padding:0;margin:0 0 0 4px;vertical-align:middle}div.wpcf7 .ajax-loader.is-active{visibility:visible}div.wpcf7 div.ajax-error{display:none}div.wpcf7 .placeheld{color:#888}div.wpcf7 input[type=file]{cursor:pointer}div.wpcf7 input[type=file]:disabled{cursor:default}div.wpcf7 .wpcf7-submit:disabled{cursor:not-allowed}.migration-content-wrap label.your-name-label,.migration-content-wrap label.your-email-label{width: 47%;display: inline-block;margin: 1%;}.migration-content-wrap span.wpcf7-not-valid-tip{position:relative}.assistance-with-p textarea.wpcf7-form-control.wpcf7-textarea,.migration-content-wrap span.your-name input,.migration-content-wrap span.your-email input{padding: 4%;}.migration-content-wrap .subscribe-label{display: block;margin: 3%;}.assistance-with-p textarea.wpcf7-form-control.wpcf7-textarea,input.wpcf7-form-control {border-radius: 3px !important;}span.wpcf7-list-item{display:inline-block;margin: 0 0 0 1em;}.migration-content-wrap .one-half .cf-label,#contact br{display: none}.migration-content-wrap .cf-label{float:none;text-transform:inherit;top:inherit;z-index:inherit;left:inherit;font-size:inherit;padding:inherit;}.migration-content-wrap input{height:inherit !important}.migration-content-wrap p{margin-bottom:2.6rem}span.wpcf7-list-item.first,span.wpcf7-list-item.last {width: 46%;}p.radio-para{margin-bottom: 3% !important;}p.assistance-with-p{margin-bottom: 4% !important;display:block !important}p.submit-para{margin-bottom: 0.5rem}.assistance-with-p span.wpcf7-form-control-wrap{display: block !important}.assistance-with-p span.wpcf7-list-item{display: inherit !important;white-space: nowrap !important}.assistance-with-p span.wpcf7-list-item.first{width:inherit !important}.assistance-with-p input[type="checkbox"]{margin-left: 4px !important;margin-right: 2px !important}.assistance-with-p span.wpcf7-list-item span.wpcf7-list-item-label{white-space: initial}.assistance-question{font-weight:700}@media only screen and (max-width:768px){ .radio-para{margin-bottom:10% !important;} span.wpcf7-form-control-wrap.your-name, span.wpcf7-form-control-wrap.your-email{margin:1%}span.wpcf7-list-item{    margin: 0 0 0 0;}#contact p:not(:last-child){margin:0;display:inline-block} span.wpcf7-list-item.first,span.wpcf7-list-item.last {width: 100%;} .migration-content-wrap{padding:20px !important;width:80% !important} .migration-content-wrap label.your-name-label,.migration-content-wrap label.your-email-label{width: 94%;display: block;margin: 3%;}.migration-content-wrap .subscribe-label{margin: 1%;}p.assistance-with-p{margin-top: 6% !important;}}</style>'
                                );
                            jQuery('.migration-content-wrap input[name="your-name"]').attr('placeholder','Name');
                            jQuery('.migration-content-wrap input[name="your-email"]').attr('placeholder','Email');
                        }
                </script>
                <?php
            }elseif ($atts['popup']=='615833' || $atts['popup']=='615829' ) { // Service landing pages exit intent form?>
                <script>
                        if(!(jQuery('#cta-popup-style-615833').length>0)){
                            jQuery('.migration-popup-container').append(
                                '<style id="cta-popup-style-615833">.migration-content-wrap {padding: 30px!important}div.wpcf7 .screen-reader-response{position:absolute;overflow:hidden;clip:rect(1px,1px,1px,1px);height:1px;width:1px;margin:0;padding:0;border:0}div.wpcf7-response-output{margin:.5em .5em 1em;padding:.2em 1em;border:2px solid red}div.wpcf7-mail-sent-ok{border:2px solid #398f14}div.wpcf7-aborted,div.wpcf7-mail-sent-ng{border:2px solid red}div.wpcf7-spam-blocked{border:2px solid orange}div.wpcf7-acceptance-missing,div.wpcf7-validation-errors{border:2px solid #f7e700}.wpcf7-form-control-wrap{position:relative}.use-floating-validation-tip span.wpcf7-not-valid-tip{position:absolute;top:20%;left:20%;z-index:100;border:1px solid red;background:#fff;padding:.2em .8em}span.wpcf7-list-item{display:inline-block;margin:0 0 0 1em}span.wpcf7-list-item-label:after,span.wpcf7-list-item-label:before{content:" "}.wpcf7-display-none{display:none}div.wpcf7 .ajax-loader{visibility:hidden;display:inline-block;background-image:url(wp-content/plugins/contact-form-7/images/ajax-loader.gif);width:16px;height:16px;border:none;padding:0;margin:0 0 0 4px;vertical-align:middle}div.wpcf7 .ajax-loader.is-active{visibility:visible}div.wpcf7 div.ajax-error{display:none}div.wpcf7 .placeheld{color:#888}div.wpcf7 input[type=file]{cursor:pointer}div.wpcf7 input[type=file]:disabled{cursor:default}div.wpcf7 .wpcf7-submit:disabled{cursor:not-allowed}.migration-content-wrap label.your-email-label,.migration-content-wrap label.your-name-label{width:47%;display:inline-block;margin:1%}.migration-content-wrap span.wpcf7-not-valid-tip{position:relative}.assistance-with-p textarea.wpcf7-form-control.wpcf7-textarea,.migration-content-wrap span.your-email input,.migration-content-wrap span.your-name input{padding:4%}.migration-content-wrap .subscribe-label{display:block;margin:3%}.assistance-with-p textarea.wpcf7-form-control.wpcf7-textarea,input.wpcf7-form-control{border-radius:3px!important}span.wpcf7-list-item{display:inline-block;margin:0 0 0 1em}#contact br,.migration-content-wrap .one-half .cf-label{display:none}.migration-content-wrap .cf-label{float:none;text-transform:inherit;top:inherit;z-index:inherit;left:inherit;font-size:inherit;padding:inherit}.migration-content-wrap input{height:inherit!important}.migration-content-wrap p{margin-bottom:2.6rem}span.wpcf7-list-item{width:100%}p.radio-para{margin-bottom:3%!important}p.assistance-with-p{margin-bottom:4%!important;display:block!important}p.submit-para{margin-bottom:.5rem}.assistance-with-p span.wpcf7-form-control-wrap{display:block!important}.assistance-with-p span.wpcf7-list-item{display:inherit!important;white-space:nowrap!important}.assistance-with-p span.wpcf7-list-item.first{width:inherit!important}.assistance-with-p input[type=checkbox]{margin-left:4px!important;margin-right:2px!important}.assistance-with-p span.wpcf7-list-item span.wpcf7-list-item-label{white-space:initial}.assistance-question{font-weight:700}.migration-content-wrap{width:46%!important}#contact p{margin-bottom:1%;text-align:left}div[data-id=not-found]{text-align:left}@media only screen and (max-width:768px){.radio-para{margin-bottom:10%!important}span.wpcf7-form-control-wrap.your-email,span.wpcf7-form-control-wrap.your-name{margin:1%}span.wpcf7-list-item{margin:0}#contact p:not(:last-child){margin:0;display:inline-block}span.wpcf7-list-item.first,span.wpcf7-list-item.last{width:100%}.migration-content-wrap{padding:20px!important;width:80%!important}.migration-content-wrap label.your-email-label,.migration-content-wrap label.your-name-label{width:94%;display:block;margin:3%}.migration-content-wrap .subscribe-label{margin:1%}p.assistance-with-p{margin-top:6%!important}}</style>'
                            );
                        }
                </script>
                <?php
            }
        }
    }
    // When user loggs in check if the user has marked any feature as
    // fav if yes then relate the user with the fav record
    public function jQueryProcessing($atts, $fixed_div)
    {
        ?>
        <script>
        jQuery(document).ready(function(){
            <?php 
            if($atts['buttoncssclass']){?>
                if(typeof jQuery('.<?php echo $atts['buttoncssclass'];?>').data('popup')==="undefined"){
                    jQuery('.<?php echo $atts['buttoncssclass'];?>').attr('data-popup',"<?php echo $atts['popupid'];?>");
                }
            <?php
            }
            if ($atts['autoshow']) {
                ?>
                showPopupCTAAfterTime(<?php echo $atts['autoshow'];?>,<?php echo $atts['popupid'];?>);
            <?php }
            if ($atts['showinsidebar']) {
                ?>
                if(jQuery('.sidebar-primary').css('display')=='block'){
                    showFixedCTA();
                }
            <?php }
            if ($atts['appendcta']) {?>
                jQuery('.appended-sidebar-cta').css('display','block');
            <?php } ?>
            function showFixedCTA(){
                if(jQuery('.widget_recent_entries').length){
                    top_pixel = jQuery('header .wrap').height();
                    fixed_div = '<style>.fixed-cta{position: fixed;top: '+top_pixel+'px;}</style><?php echo $fixed_div;?>';
                    jQuery('.widget_recent_entries').append(fixed_div);
                    setTimeout(function(){
                        var wrap = jQuery(".sidebar-cta");
                        var wrap_top = wrap.offset().top;
                        jQuery(window).on("scroll", function(e) {
                            if (jQuery(this).scrollTop() > wrap_top) {
                                wrap.addClass("fixed-cta");
                            } else {
                                wrap.removeClass("fixed-cta");
                            }
                        });
                    }, 1000);
                }
            }
            <?php if ($atts['buttoncssclass']) {?>
                jQuery('.<?php echo $atts['buttoncssclass'];?>').on('click',function(e){
                    e.preventDefault();
                    jQuery('#'+jQuery(this).data('popup')).css('display','flex');
                });
            <?php }?>
            jQuery('.widget_recent_entries').on('click','.migration-cta-form-open',function(e){
                e.preventDefault();
                jQuery('#'+jQuery(this).data('popup')).css('display','flex');
            });
            jQuery('.appended-sidebar-cta').on('click','.migration-cta-form-open',function(e){
                e.preventDefault();
                jQuery('#'+jQuery(this).data('popup')).css('display','flex');
            });
            jQuery('.migration-close').on('click',function(){
                jQuery('.site-header-alt').show();
                jQuery('body').css('overflow','unset');
                jQuery('.migration-popup-container').css('display','none');
            });
            function showPopupCTAAfterTime(aftertime=5000,div_id=''){
                if(div_id!=''){
                    setTimeout(function() { 
                        jQuery('#'+div_id).css('display','flex');
                    }, aftertime);
                }
            }
            <?php if ($atts['exitintent']) { ?>
                var w_s_mig_i_opened_<?php echo $atts['popupid'];?>;
                jQuery.fn.setExitIntentCookie = function (cname, cvalue, exdays) {
                    var d = new Date();
                    d.setTime(d.getTime() + (exdays*24*60*60*1000));
                    var expires = "expires=" + d.toGMTString();
                    document.cookie = cname+" = "+cvalue+"; "+ expires +"; path=/"
                }
                jQuery.fn.getExitIntentCookie = function (cname) {
                    if (document.cookie.indexOf(cname) >= 0) {
                        return true;
                    } else {
                        return false;
                    }
                }
                jQuery.fn.checkExitIntentCookie = function (msg_var_opened_name) {
                    var w_s_mig_i_opened_user= jQuery.fn.getExitIntentCookie(msg_var_opened_name);
                    if (w_s_mig_i_opened_user) {
                        w_s_mig_i_opened_<?php echo $atts['popupid'];?> = true;
                    } else {
                        w_s_mig_i_opened_<?php echo $atts['popupid'];?> = false;
                    }
                }
               
                jQuery.fn.mig_cta_pop = function () {
                    jQuery.fn.checkExitIntentCookie("w_s_mig_i_opened_<?php echo $atts['popupid'];?>");
                    jQuery('body').mouseleave(function (event) {
                        var w_s_mig_i_top = jQuery(document).scrollTop();
                        if (event.pageY < w_s_mig_i_top && w_s_mig_i_opened_<?php echo $atts['popupid'];?>==false && jQuery('.migration-popup-container').length ) {
                            w_s_mig_i_opened_<?php echo $atts['popupid'];?> = true;
                            jQuery.fn.setExitIntentCookie("w_s_mig_i_opened_<?php echo $atts['popupid'];?>", "true", 1);
                            jQuery('#<?php echo $atts['popupid'];?>').css('display','flex');
                        }
                    });
                }
                if(!(jQuery('#exit_pop').length>0)){
                    jQuery(document).mig_cta_pop();
                }
            <?php } ?>
            <?php if ($atts['halfscroll']) { ?>
                var w_s_mig_i_opened_<?php echo $atts['popupid'];?>;
                jQuery.fn.setExitIntentCookie = function (cname, cvalue, exdays) {
                    var d = new Date();
                    d.setTime(d.getTime() + (exdays*24*60*60*1000));
                    var expires = "expires=" + d.toGMTString();
                    document.cookie = cname+" = "+cvalue+"; "+ expires +"; path=/"
                }
                jQuery.fn.getExitIntentCookie = function (cname) {
                    if (document.cookie.indexOf(cname) >= 0) {
                        return true;
                    } else {
                        return false;
                    }
                }
                jQuery.fn.checkExitIntentCookie = function (msg_var_opened_name) {
                    var w_s_mig_i_opened_user= jQuery.fn.getExitIntentCookie(msg_var_opened_name);
                    if (w_s_mig_i_opened_user) {
                        w_s_mig_i_opened_<?php echo $atts['popupid'];?> = true;
                    } else {
                        w_s_mig_i_opened_<?php echo $atts['popupid'];?> = false;
                    }
                }
               
                jQuery.fn.mig_cta_pop = function () {
                    jQuery.fn.checkExitIntentCookie("w_s_mig_i_opened_<?php echo $atts['popupid'];?>");
                    var w_s_mig_i_top = jQuery(document).scrollTop();
                        var reached_half_page = w_s_mig_i_top  > 220;
                        // var reached_half_page = w_s_mig_i_top  > $(document).height() / 2;
                        if (reached_half_page && w_s_mig_i_opened_<?php echo $atts['popupid'];?>==false && jQuery('.migration-popup-container').length ) {
                            jQuery('body').css('overflow','hidden');
                            w_s_mig_i_opened_<?php echo $atts['popupid'];?> = true;
                            jQuery.fn.setExitIntentCookie("w_s_mig_i_opened_<?php echo $atts['popupid'];?>", "true", 1);
                            jQuery('#<?php echo $atts['popupid'];?>').css('display','flex');
                            jQuery('.site-header-alt').hide();
                        }
               
                }
                if(!(jQuery('#exit_pop').length>0)){
                    jQuery(window).scroll(function() {
                        jQuery(document).mig_cta_pop();
                    });
                }
              
            <?php } ?>
        });
        </script>
        <?php
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CustomBlogCta;
        }
        return self::$instance;
    }
}
