jQuery(document).ready(function($) {
    jQuery(document).on('click','.show-login',function(){
        jQuery('.edwiser-minimal-checkout-cc-address-wrapper').hide();
    });

    jQuery(document).on('click','#wdm_login_tab',function(){
        jQuery('.edwiser-minimal-checkout-cc-address-wrapper').show();
    });
});