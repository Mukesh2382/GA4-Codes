jQuery(document).ready(function() {
    jQuery('#edd-purchase-button').on('click',function(){
        if(jQuery('#edd_checkout_user_login').css('display')=='block' && typeof jQuery('#wdm_login_tab')!=='undefined'){
            jQuery('#wdm_login_tab').trigger('click');
        }
    });

    jQuery('#edd_checkout_form_country').on('change',"#edd_cust_country", function(){
        var country = jQuery(this).val();
        if(country=='US'){
            jQuery('#edd-state-wrap').show();
            jQuery("#edd_cust_state").prop('required',true);
        }
        else{
            jQuery('#edd-state-wrap').hide();
            jQuery("#edd_cust_state").prop('required',false);
        }
    });
    
    minimal_checkout_processing = 0;
    jQuery("#edd_checkout_form_wrap").on('focusout', '#edd-email-wrap input', function() {
        if( jQuery(this).val().length > 3 ){
            jQuery("#edd_user_login").val(jQuery(this).val());
            jQuery("#edd-email-wrap").find('.edd-email-error').remove();
            if(wdm_is_email(jQuery(this).val())==false){
                jQuery("#edd-email-wrap").append('<span class="edd-email-error">Please enter a valid email!</span>');
            }else{
                wdm_customer_registered_check(jQuery(this).val(), jQuery(this).attr('data-nonce'));
            }
        }
    });
    jQuery('#edd_checkout_form_wrap').on('click','.show-login',function(){
        jQuery('#edd_checkout_user_info').hide();
        jQuery('#edd_checkout_user_login').show();
        jQuery('#edd_checkout_form_country').hide();
    });
    jQuery('#edd_checkout_form_wrap').on('click','#wdm_login_tab',function(){
        jQuery('#edd_checkout_user_login').hide();
        jQuery('#edd_checkout_user_info').show();
        jQuery('#edd_checkout_form_country').show();
    });
    jQuery("#edd_checkout_form_wrap").on('input', '#edd_user_pass_confirm', function() {
        jQuery("#edd_user_pass").val(jQuery(this).val());
    });
    jQuery("#edd_checkout_form_wrap").on('change', '#edd_user_pass_confirm', function() {
        jQuery("#edd_user_login").val(jQuery('#edd-email-wrap input').val());
    });

    jQuery("#edd_checkout_form_wrap").on('input', '#edd-email', function() {
        jQuery("#edd_purchase_form").attr('data-email', jQuery(this).val());
    });

    jQuery("#edd_checkout_form_wrap").on('input', '#edd-first', function() {
        jQuery("#edd_purchase_form").attr('data-first-name', jQuery(this).val());
    });

    jQuery("#edd_checkout_form_wrap").on('input', '#edd-last', function() {
        jQuery("#edd_purchase_form").attr('data-last-name', jQuery(this).val());
    });
    
    
   
    jQuery('.wdm-cart-register a').on('click',function(){
        jQuery('.wdm-block-login-register-container').hide();
        jQuery('.log-reg-heading').hide();
        jQuery('.wdm-block-checkout-container').show();
    });
    jQuery('#edd_checkout_form_wrap').on('click', '.edd_checkout_register_login', function(e) {
        e.preventDefault();
        jQuery('.wdm-block-checkout-container').hide();
        jQuery('.wdm-block-login-register-container').show();
        jQuery('.log-reg-heading').show();
    });

    jQuery('select#edd-gateway, input.edd-gateway').on('change', function(e) {
        if(e.target.nodeName.includes('INPUT')){
            jQuery('input.edd-gateway').each(function(){
                jQuery(this).prev('.radio-span').removeClass('checked-radio-span');
            });
            jQuery(this).prev('.radio-span').addClass('checked-radio-span');
        }
        jQuery('#edd_payment_mode_select_wrap').prependTo('#edd_purchase_form');
        return false;
    });

    // On selecting/deselecting apply discount or apply renewal checkbox show/hide related fields
    jQuery('#apply_discount_check, #apply_renewal_check').on('change', function(e) {
        if(e.target.nodeName.includes('apply_renewal_check')){
            jQuery('#edd-license-key-container-wrap,#edd_sl_show_renewal_form,.edd-sl-renewal-actions').toggle();
        }
        if(this.checked){
            jQuery(this).closest('tr').next('tr').show();
        }else{
            jQuery(this).closest('tr').next('tr').hide();
        }
        return false;
    });

    // Cancel renewal event
    jQuery('#edd-cancel-license-renewal').on('click', function(e) {
        jQuery('#edd-license-key-container-wrap,#edd_sl_show_renewal_form,.edd-sl-renewal-actions').toggle();
        jQuery('#apply_renewal_check').click();
        return false;
    });
    
    // To move select payment field from the personal details form to the original position
    jQuery(document).ajaxSend(function(event, xhr, settings) {
        jQuery('.paypal-selected-msg').remove();
        if (typeof settings.data !== 'undefined' && settings.data.includes('action=edd_load_gateway')) {
            jQuery('#edd_payment_mode_select_wrap').prependTo('#edd_purchase_form');
        }
    });

    // After payment selection move the select payment field below the personal/address details fields
    jQuery(document).ajaxComplete(function(event, xhr, settings) {
        if (typeof settings.data !== 'undefined' && settings.data.includes('action=edd_load_gateway')) {
            if (typeof xhr.responseText !== 'undefined' && xhr.responseText.includes('fieldset id="edd_purchase_submit"')) {
                jQuery('#edd_payment_mode_select_wrap').insertBefore('#edd_purchase_submit');
                chkout_acceptance = wdm_read_cookie('chkout_acceptance');
                if(chkout_acceptance){
                    jQuery('.edd-privacy-policy-agreement label').trigger('click');
                }
                if(jQuery('#edd-email') && typeof jQuery("#edd_purchase_form").attr('data-email')!=='undefined'){
                    jQuery('#edd-email').val(jQuery("#edd_purchase_form").attr('data-email'));
                    jQuery('#edd_user_login').val(jQuery("#edd_purchase_form").attr('data-email'));
                }
                if(jQuery('#edd-first') && typeof jQuery("#edd_purchase_form").attr('data-first-name')!=='undefined'){
                    jQuery('#edd-first').val(jQuery("#edd_purchase_form").attr('data-first-name'));
                }
                if(jQuery('#edd-last') && typeof jQuery("#edd_purchase_form").attr('data-last-name')!=='undefined'){
                    jQuery('#edd-last').val(jQuery("#edd_purchase_form").attr('data-last-name'));
                }
            }
            if(jQuery('input[name="payment-mode"]:checked').val()=='paypalexpress'){
                jQuery('#edd-payment-mode-wrap').after('<span class="paypal-selected-msg" style="font-size:12px;margin-left:10px">You will pay via <img style="margin-bottom:5px;vertical-align:middle" src="'+edd_scripts.edd_paypal_img+'"></span>');
            }
            if (typeof xhr.responseText !== 'undefined' && xhr.responseText.includes('fieldset id="edd_cc_fields"')) {
                jQuery('#edd_cc_fields').insertBefore('#edd_purchase_submit');
            }
        }
    });

    // Remove discount click
    jQuery("#edd_checkout_form_wrap").on('click','.wdm_remove_coupon_field',wdm_edd_minimal_checkout_remove_discount);

    jQuery('#edd_checkout_form_wrap').on('click','#wdm_discount_coupon .wdm_edd_discount_link, #wdm_discount_coupon .wdm_try_again_coupon',function(){
        // console.log('wdm_edd_discount_link');
        jQuery('#wdm_discount_coupon .wdm_edd_discount_link').hide();
        jQuery('#wdm_coupon_error_wrap').hide();
        jQuery('#wdm_coupon_field').show();
    });

    jQuery('#edd_checkout_form_wrap').on('click','#wdm-edd-discount-cancel-button',function(){
        jQuery('#wdm_coupon_field').hide();
        // wdm_edd_discount_link
        if(jQuery('.wdm_remove_coupon_field').attr('data-code')){
            jQuery('#wdm_edd_discount_wrap').hide();
        }else{
            jQuery('#wdm_discount_coupon .wdm_edd_discount_link').show();
        }
    });

    jQuery('#edd_purchase_form').on('click','.tml-submit-wrap [name="wp-submit"]',function(e){
        clicked_button = jQuery(this);
        login_processing = 0;
        e.preventDefault();
        var $this = jQuery(this), postData = {
			action: 'wdm_minimal_checkout_login',
			email: jQuery('.tml-user-login-wrap [name="log"]').val(),
            pass: jQuery('.tml-user-pass-wrap [name="pwd"]').val(),
            remember: jQuery('.tml-rememberme-wrap [name="rememberme"]').is(':checked')?1:0,
            "wdm-security": jQuery('#wdm-security').val()
		};
        if(login_processing==0 && jQuery('.tml-user-login-wrap [name="log"]').val() && jQuery('.tml-user-pass-wrap [name="pwd"]').val()){
            /* console.log(login_processing); */
            jQuery.ajax({
                type: "POST",
                data: postData,
                dataType: "json",
                url: wdmCheckoutAjax.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                beforeSend: function( xhr ) {
                    jQuery('.login-message').remove();
                    /* console.log(login_processing); */
                    login_processing = 1;
                    /* console.log(login_processing); */
                    clicked_button.after('<span class="edd-loading-ajax edd-loading"></span>');
                },
                success: function (login_response) {
                    if(typeof login_response.loggedin !== 'undefined'){
                        if(login_response.loggedin==true){
                            location.reload(true);
                        }else if(login_response.loggedin==false){
                        }
                        if(typeof login_response.message !== 'undefined'){
                            jQuery('.tml-submit-wrap input[name="wp-submit"]').after('<span class="login-message">'+login_response.message+'</span>');
                        }    
                    }

                }
            }).complete(function (data) {
                /* console.log(login_processing); */
                login_processing = 0;
                jQuery('.edd-loading-ajax').remove();
                /* console.log(login_processing); */
                if ( window.console && window.console.log ) {
                    console.log( data );
                }
            });
        }

		return false;
    });

    // Change license event handling
    jQuery('body').on('click','span.license_options:not(.license_options_checked)',function(){
        var clicked_license = jQuery(this);
        var license_selected = clicked_license.find('input[type="radio"]').val();
        var nonce = clicked_license.find('input[type="radio"]').data('nonce');
        if(clicked_license.closest('div.wdm_edd_cart_item_licenses').length){
            var cart_key = clicked_license.closest('div.wdm_edd_cart_item_licenses').attr('data-cart-key');
        }else{
            var cart_key = clicked_license.closest('td').attr('data-cart-key');
        }
        // console.log(clicked_license);
        // console.log(cart_key);
        // console.log(license_selected);
        // console.log(nonce);
        if(typeof cart_key !== 'undefined' && license_selected!=='' && nonce!=='' && minimal_checkout_processing==0){
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : wdmCheckoutAjax.ajaxurl,
                data : {
                    action: "wdm_elem_cart_change_license", 
                    license : license_selected, 
                    nonce: nonce, 
                    cart_key: cart_key,
                    email: jQuery( '#edd-user-email' ).val()
                },
                beforeSend: function( xhr ) {
                    minimal_checkout_processing = 1;
                    clicked_license.closest('div.wdm_edd_cart_item_licenses').after('<span class="edd-loading-ajax edd-loading"></span>');
                },
                success: function(response) {
                    if (response.msg == 'valid') {
                        // After selecting another license for a cart product

                        // Show applied license message
                        clicked_license.closest('.wdm_edd_cart_item_licenses').find('.radio_button_img').each(function(){
                            jQuery(this).attr('src',wdmCheckoutAjax.asset_path+'/images/radio.svg');
                        });

                        // Select the new selected license option's radio button and deselect the old one
                        clicked_license.closest('.wdm_edd_cart_item_licenses').find('.license_options').each(function(){
                            if(jQuery(this).hasClass('license_options_checked')){
                                jQuery(this).removeClass('license_options_checked');
                            }
                        });
                       
                        // Apply the css class to the new option selected
                        // clicked_license.addClass('license_option_label_checked');
                        clicked_license.addClass('license_options_checked');
                        // find('.license_options').each(function(){
                        //     jQuery(this).addClass('license_options_checked');
                        // });
                        
                        clicked_license.find('input[type="radio"]').each(function(){
                            jQuery(this).attr("checked", true);
                        });

                        // Update row's product value
                        clicked_license.closest('tr').find('.wdm_edd_cart_item_price_value').html(response.item_value);

                        // Change the selected option indicating svg image
                        clicked_license.find('.radio_button_img').each(function(){
                            jQuery(this).attr('src',wdmCheckoutAjax.asset_path+'/images/selected_radio.svg');
                        });

                        // If any renewal was applied in the cart
                        if( typeof response.is_renewal!=='undefined' && jQuery('.edd-sl-renewal-details').length ){
                            if(response.is_renewal=='1'){
                                clicked_license.closest('tr').find('.edd-sl-renewal-details').remove();
                            }else if(response.is_renewal==false){
                                clicked_license.closest('tr').find('.edd-sl-renewal-details').remove();
                                jQuery('#edd-cancel-license-renewal').click();
                                jQuery('#edd_sl_cancel_renewal_form').remove();
                                jQuery('.edd-sl-renewal-actions').next('.edd-cart-adjustment').remove();
                                jQuery('.wdm-block-cart-price-details-renewal-check .apply_check_label').remove();
                            }
                        }
                        
                        updateMinimalCheckoutTotalSubtotalDetails(response);
                        updateCheckoutCartKeys(parseInt(cart_key),'#wdm_edd_checkout_cart div.wdm_edd_cart_item_licenses');
                    }
                }
            }).complete(function (data) {
                minimal_checkout_processing = 0;
                jQuery('.edd-loading-ajax').remove();
            });
        }   
    });

    function updateCheckoutCartKeys(cart_key,html_element){
        $current_cart_element = '';
        jQuery(html_element).each(function(i){
            if(typeof jQuery(this).attr('data-cart-key')!=='undefined'){
                if(parseInt(jQuery(this).attr('data-cart-key'))>cart_key){
                    jQuery(this).attr('data-cart-key',parseInt(jQuery(this).attr('data-cart-key'))-1);
                }else if(parseInt(jQuery(this).attr('data-cart-key'))==cart_key){
                    jQuery(this).attr('data-cart-key',jQuery(html_element).length-1);
                }
            }
        });
    }

    function updateMinimalCheckoutTotalSubtotalDetails(response){
        // Change Total and Sub total
        jQuery('.sub-total-row span.edd_cart_amount').each(function() {
            jQuery(this).data('total',response.total);
            jQuery(this).data('subtotal',response.subtotal);
			jQuery(this).html(response.subtotal);
        });
        jQuery('.sub-subtotal-row span.edd_cart_amount').each(function() {
            jQuery(this).data('total',response.total);
            jQuery(this).data('subtotal',response.subtotal);
			jQuery(this).find('.regular-price').html(response.subtotal);
			jQuery(this).find('.discounted-price').html(response.total);
        });

        // Update Sub total
        jQuery( '.cart-price-details-subtotal-value' ).each(function(){
            jQuery( this ).html(response.subtotal);
        });

        // After changing the option the total, subtotal, coupon value and percentage (amount) may change
        if(typeof response.coupon_value!=='undefined' ){
            // Check if the exisitng discount code does not support newly added license option
            if(response.coupon_value==''){
                $had_applied = 0;
                jQuery('.wdm_remove_coupon_field').each(function(){
                    if(typeof jQuery( this ).attr('data-code')!=='undefined' && jQuery( this ).attr('data-code')!=''){
                        $had_applied = 1;
                    }
                    jQuery( this ).attr('data-code','');
                });
                if($had_applied){
                    jQuery('#wdm_coupon_field').show();
                }
                // jQuery('#edd-discount'/*, $checkout_form_wrap */).val('');
                
                jQuery('.edd_cart_amount .regular-price').each(function(){
                    jQuery( this ).html('');
                });
                jQuery('.sub-total-row').hide();
                jQuery('.sub-subtotal-row').show();
                jQuery('#wdm_coupon_amount').html('');
        
                jQuery('#wdm_edd_discount_wrap').hide();
            }else{
                // jQuery('#edd-discount'/*, $checkout_form_wrap */).val(response.code);
                jQuery('.wdm_remove_coupon_field').each(function(){
                    jQuery( this ).data('code',response.code);
                });
                jQuery('#wdm_coupon_amount').html(' '+ response.amount + ' ');
                // jQuery('#applied-message,#wdm-edd-remove-discount').show();
                // jQuery('#wdm-block-discount-apply').hide();
            }
        }
    }

    function wdm_edd_minimal_checkout_remove_discount(event){
        event.preventDefault();
        $body = jQuery(document.body);

		var $this = jQuery(this), postData = {
			action: 'wdm_edd_remove_discount',
			code: $this.attr('data-code')
		};
        // console.log(postData);
        // console.log(minimal_checkout_processing);
        if(minimal_checkout_processing==0){
            jQuery.ajax({
                type: "POST",
                data: postData,
                dataType: "json",
                url: wdmCheckoutAjax.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                beforeSend: function( xhr ) {
                    minimal_checkout_processing = 1;
                },
                success: function (discount_response) {
                    process_after_removing_discount(discount_response, jQuery(this));
                    $body.trigger('edd_discount_removed', [ discount_response ]);
                }
            }).fail(function (data) {
                if ( window.console && window.console.log ) {
                    console.log( data );
                }
            }).complete(function (data) {
                minimal_checkout_processing = 0;
            });
        }

		return false;
    }

    function process_after_removing_discount(discount_response,remove_coupon_element){
        $had_dis_applied = $hundred_percent_applied = 0;
        jQuery('.edd_cart_amount span.discounted-price').each(function() {
            jQuery(this).attr('data-total',discount_response.total_plain);
            jQuery(this).attr('data-subtotal',discount_response.subtotal_plain);
            jQuery(this).html(discount_response.total);
        });
        jQuery('.sub-subtotal-row .edd_cart_amount').each(function() {
            if(typeof jQuery(this).attr('data-total')!='undefined' && jQuery(this).attr('data-total')=='0'){
                $hundred_percent_applied = 1;
            }
        });
    
        jQuery('.edd_cart_amount .regular-price').each(function(){
            jQuery( this ).html('');
        });
        jQuery('.sub-total-row').hide();
        jQuery('#wdm_coupon_amount').html('');

        jQuery('.wdm_remove_coupon_field').each(function(){
            if(typeof jQuery( this ).attr('data-code')!=='undefined' && jQuery( this ).attr('data-code')!=''){
                $had_dis_applied = 1;
            }
            jQuery( this ).attr('data-code','');
        });
        jQuery('#wdm_edd_discount_wrap').hide();
        jQuery('#wdm_coupon_field').show();
        if($had_dis_applied && $hundred_percent_applied){
            location.reload();
        }
    }

    // Apply discount click
    jQuery('#edd_checkout_form_wrap').on('click', '#wdm-edd-discount-button', wdm_edd_minimal_checkout_apply_discount);
    
    // Remove product from the cart
    jQuery('#wdm_edd_checkout_cart').on('click', '.wdm_remove_product', wdm_edd_minimal_checkout_remove_product);
    

    function wdm_edd_minimal_checkout_apply_discount(event) {

		event.preventDefault();
        $body = jQuery(document.body);

		var $this = jQuery(this),
			discount_code = jQuery('#wdm-edd-discount').val();
		if (discount_code == '' || discount_code == wdmCheckoutAjax.enter_discount ) {
            console.log();
			return false;
		}

		var postData = {
			action: 'wdm_edd_apply_discount',
			code: discount_code,
			email: jQuery( '#edd-user-email' ).val()
		};

		jQuery('.wdm_coupon_error').html('').hide();
        jQuery('#wdm_coupon_error_wrap').hide();
        if(minimal_checkout_processing==0){
            jQuery.ajax({
                type: "POST",
                data: postData,
                dataType: "json",
                url: wdmCheckoutAjax.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                beforeSend: function( xhr ){
                    minimal_checkout_processing = 1;
                },
                success: function (discount_response) {
                    if( discount_response ) {
                        process_after_apply_discount(discount_response);
                    } else {
                        if ( window.console && window.console.log ) {
                            console.log( discount_response );
                        }
                        $body.trigger('edd_discount_failed', [ discount_response ]);
                    }
                }
            }).fail(function (data) {
                if ( window.console && window.console.log ) {
                    console.log( data );
                }
            }).complete(function (data) {
                minimal_checkout_processing = 0;
            });
        }
		return false;
	};

    function process_after_apply_discount(discount_response){
        if (discount_response.msg == 'valid') {
    
            jQuery('.edd_cart_amount span.discounted-price').each(function() {
                jQuery(this).attr('data-total',discount_response.total_plain);
                jQuery(this).attr('data-subtotal',discount_response.subtotal_plain);
                jQuery(this).html(discount_response.total);
            });
            
            jQuery('.edd_cart_amount .regular-price').each(function(){
                jQuery( this ).html(discount_response.subtotal);
            });
            jQuery('.sub-total-row').show();
            
            jQuery('#wdm_coupon_amount').html(' '+ discount_response.amount + ' ');
            
            jQuery('.wdm_remove_coupon_field').each(function(){
                jQuery( this ).attr('data-code',discount_response.code);
            });
            if(discount_response.code!=='' && discount_response.total_plain==0){
                location.reload(true);
            }
            
            jQuery('#wdm_edd_discount_wrap').show();
            jQuery('#wdm_coupon_field').hide();
            
            jQuery('#wdm-edd-discount').val('');
            jQuery('.sub-subtotal-row').show();

            jQuery('.wdm_edd_discount_applied').show();

            jQuery('.sub-total-row .edd_cart_total').html('Sub Total');
        
            $body.trigger('edd_discount_applied', [ discount_response ]);
        } else {
            jQuery('#wdm_edd_discount_wrap').show();
            // console.log('Testing');
            jQuery('#wdm_coupon_field').hide();
            jQuery('#wdm-edd-discount').val('');
            jQuery('.wdm_coupon_error').html( '<span class="edd_error">' + discount_response.msg + '</span>' );
            jQuery('#wdm_coupon_error_wrap').show();
            jQuery('.wdm_coupon_error').show();
            jQuery('.wdm_edd_discount_applied').hide();
            
            $body.trigger('edd_discount_invalid', [ discount_response ]);
        }
    }

    function wdm_edd_minimal_checkout_remove_product(e){
        e.preventDefault();
        var remove_clicked = jQuery(this);
        var cart_key = remove_clicked.closest('td').find('.wdm_edd_cart_item_licenses').attr('data-cart-key');
        var nonce = remove_clicked.attr('data-nonce');
        if(cart_key!==''){
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : wdmCheckoutAjax.ajaxurl,
                data : {
                    action: "wdm_elem_cart_remove_item", 
                    nonce: nonce, 
                    cart_key: cart_key
                },
                success: function(response) {
                    if (response.msg == 'valid') {
                        remove_clicked.closest('tr').remove();
                        if(typeof jQuery('#wdm_edd_checkout_cart .wdm_edd_cart_item_name_value').length=='undefined' || jQuery('#wdm_edd_checkout_cart .wdm_edd_cart_item_name_value').length==0){
                            location.reload(true);
                        }
                        jQuery('.edd-cart-quantity').html(parseInt(jQuery('.edd-cart-quantity').html())-1);
                        updateMinimalCheckoutTotalSubtotalDetails(response);
                        updateCheckoutCartKeys(parseInt(cart_key),'#wdm_edd_checkout_cart div.wdm_edd_cart_item_licenses');
                    }
                }
            });
        }

        // Remove row
        // Update total and sub total values
        // Check if discount is still there if not run discount removal process
    }

    function wdm_read_cookie(name) {
        var nameEQ = encodeURIComponent(name) + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ')
                c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0)
                return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    }

    function wdm_is_email(email) {
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if(!regex.test(email)) {
            return false;
        }else{
            return true;
        }
    }

    function wdm_customer_registered_check(email,nonce){
        if(!email){
            return false;
        }
        var postData = {
			action: 'wdm_is_customer_registered',
			email: email,
            nonce: nonce
		};
        jQuery.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: wdmCheckoutAjax.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {
                if(typeof response.message !=='undefined'){
                    jQuery("#edd-email-wrap").append(response.message);
                }
                // console.log(response);
                // jQuery("#edd-email-wrap").append('<span class="edd-email-error">Email already exists, please <a class="show-login" href="#">login</a></span>');
            }
		}).fail(function (data) {
			// if ( window.console && window.console.log ) {
				// console.log( data );
			// }
		});
    }
});