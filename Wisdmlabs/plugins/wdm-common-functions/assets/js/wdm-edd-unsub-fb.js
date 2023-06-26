jQuery(document).ready(function(){
    jQuery('.edd_subscription_cancel').on('click',unsub_click);
    jQuery('.unsub-feedback-close').on('click',close_unsub_popup);
    jQuery('.form-cancel').on('click',close_unsub_popup);

    // To make custom made radio button icon checked
    jQuery('.popup_content input[type="radio"]:checked').next('.wpcf7-list-item-label').find('.radio-button__control').addClass('radio-button__control_selected');
    jQuery('.popup_content input[type="radio"]:checked').closest('.wpcf7-list-item').css({'background-color':'#f7f7f7','border':'none'});
    
    // On radion button select 
    jQuery('.popup_content input[type="radio"]').change(
        function(){
            jQuery(this).closest('.wpcf7-radio').find('.wpcf7-list-item').css({'background-color':'#fff','border':'1px solid #ddd'});
            jQuery(this).closest('.wpcf7-radio').find('.radio-button__control').removeClass('radio-button__control_selected');
            if (jQuery(this).is(':checked')) {
                jQuery(this).closest('.wpcf7-list-item').css({'background-color':'#f7f7f7','border':'none'});
                jQuery(this).next('.wpcf7-list-item-label').find('.radio-button__control').addClass('radio-button__control_selected');
            }
        }
    );

    function unsub_click(e){
        var subscription_id = get_sub_id(jQuery(this).attr('href'));
        // First time clicked
        if(subscription_id){
            if(jQuery(this).attr('unsub_clicked')!='true'){
                jQuery(this).attr('unsub_popup_opened','true');
                e.preventDefault();
                open_unsub_popup(subscription_id);
                set_subscription_id(subscription_id);
            }else{
                // console.log('Inside Else');
                location.href = jQuery(this).attr('href');
                jQuery(this).attr('unsub_clicked',true);
            }
        }
    }
    function close_unsub_popup(e){
        if(jQuery('#unsub-feedback-popup-container').length){
            jQuery('#unsub-feedback-popup-container').css('display','none');
            jQuery('input[name="cancelled-subscription"]').val('0');
            unset_subscription_id();
            reset_cf7();
        }
    }

    function reset_cf7(){
        jQuery( 'div.wpcf7 > form' ).each( function() {
            // console.log('Inside 1');
            var $form = jQuery( this );
            wpcf7.clearResponse( $form );
            if ( wpcf7.cached ) {
                // console.log('Inside 2');
                wpcf7.refill( $form );
            }
        } );
    }

    function open_unsub_popup(subscription_id){
        if(jQuery('#unsub-feedback-popup-container').length){
            jQuery('#unsub-feedback-popup-container').css('display','flex');
            jQuery('input[name="cancelled-subscription"]').val(wdm_ajax_object.wdm_sub_url+subscription_id);
            reset_cf7();
            jQuery('.popup_content input[type="radio"]:checked').closest('.wpcf7-radio').find('.wpcf7-list-item').css({'background-color':'#fff','border':'1px solid #ddd'});
            jQuery('.popup_content input[type="radio"]:checked').closest('.wpcf7-radio').find('.radio-button__control').removeClass('radio-button__control_selected');
            jQuery('.popup_content input[type="radio"]:checked').closest('.wpcf7-list-item').css({'background-color':'#f7f7f7','border':'none'});
            jQuery('.popup_content input[type="radio"]:checked').next('.wpcf7-list-item-label').find('.radio-button__control').addClass('radio-button__control_selected');
        }
    }
    function get_sub_id(href){
        if(href!=''){
            return get_url_parameter(href, 'sub_id');
        }
        return false;
    }
    function set_subscription_id(subscription_id){
        if(subscription_id!='' && jQuery('input[name="your-subscription"]').length){
            jQuery('input[name="your-subscription"]').val(subscription_id);
        }
    }
    function unset_subscription_id(){
        if(jQuery('input[name="your-subscription"]').length){
            jQuery('input[name="your-subscription"]').val(0);
        }
    }
    function get_url_parameter(url, name) {
        return (RegExp(name + '=' + '(.+?)(&|$)').exec(url)||[,null])[1];
    }
    document.addEventListener( 'wpcf7submit', function( event ) {
        // console.log(event.detail);
        if ( event.detail.status!='validation_failed' && typeof wdm_ajax_object.fb_form !== 'undefined' && wdm_ajax_object.fb_form == event.detail.contactFormId ) {
            jQuery('.edd_subscription_cancel[unsub_popup_opened="true"]').attr('unsub_clicked','true');
            jQuery('.unsub-feedback-close').trigger('click');
            jQuery('.edd_subscription_cancel[unsub_popup_opened="true"]').trigger('click');
        }
    }, false );
});