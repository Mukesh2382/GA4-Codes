jQuery(document).ready(function() {
    add_contact_us_menu_checkout();
    // jQuery('input[type="radio"][name="payment-mode"]').on('change',function(){
    //     jQuery('#edd_cc_fields').remove();
    //     jQuery('#edd_purchase_submit').remove();
    // });

    // jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
    //     if(typeof settings.data !== 'undefined' && settings.data.includes('action=edd_load_gateway')){
    //         if(typeof xhr.responseText !=='undefined' && xhr.responseText.includes('fieldset id="edd_cc_fields"')){
    //             jQuery('#edd_cc_fields').appendTo('#edd_payment_mode_select_wrap');
    //         }
    //         if(typeof xhr.responseText !=='undefined' && xhr.responseText.includes('fieldset id="edd_purchase_submit"')){
    //             jQuery('#edd_purchase_submit').appendTo('#edd_payment_mode_select_wrap');
    //         }
    //     }
    // });
    // var edd_payment_mode_select_wrap = jQuery('#edd_payment_mode_select_wrap').clone();

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
    

    
    // // jQuery('input[type="radio"][name="payment-mode"]').on('change', function(){
    // //     // jQuery('#edd_cc_fields').remove();
    // //     console.log('Change event');
    // //     console.log(jQuery('#edd_payment_mode_select_wrap'));
    // //     // console.log(jQuery('#edd_payment_mode_select_wrap').length);
    // //     jQuery('#edd_payment_mode_select_wrap').prependTo('#edd_purchase_form');

    // // });
    // jQuery('input.edd-gateway').change(function(e) {
    //     console.log('jQuery');
    // });
    // To move select payment field from the personal details form to the original position
    jQuery(document).ajaxSend(function(event, xhr, settings) {
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
            }
            if (typeof xhr.responseText !== 'undefined' && xhr.responseText.includes('fieldset id="edd_cc_fields"')) {
                jQuery('#edd_cc_fields').insertBefore('#edd_purchase_submit');
            }
        }
    });

    // Change license event handling
    jQuery('body').on('click','.license_option_label:not(.license_option_label_checked)',function(){
        var clicked_license = jQuery(this);
        var license_selected = clicked_license.find('input[type="radio"]').val();
        var nonce = clicked_license.find('input[type="radio"]').data('nonce');
        if(clicked_license.closest('div.wdm_edd_cart_item_licenses').length){
            var cart_key = clicked_license.closest('div').attr('data-cart-key');
        }else{
            var cart_key = clicked_license.closest('td').attr('data-cart-key');
        }
        // console.log(clicked_license);
        // console.log(cart_key);
        // console.log(license_selected);
        // console.log(nonce);
        if(typeof cart_key !== 'undefined' && license_selected!=='' && nonce!==''){
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
                success: function(response) {
                    if (response.msg == 'valid') {
                        // After selecting another license for a cart product

                        // // Show applied license message
                        // clicked_license.closest('.wdm_edd_cart_item_licenses').find('.radio_button_img').each(function(){
                        //     jQuery(this).attr('src',wdmCheckoutAjax.asset_path+'/images/radio.svg');
                        // });

                        // Select the new selected license option's radio button and deselect the old one
                        clicked_license.closest('.wdm_edd_cart_item_licenses').find('.license_options').each(function(){
                            if(jQuery(this).hasClass('license_options_checked')){
                                jQuery(this).removeClass('license_options_checked');
                            }
                        });
                        clicked_license.closest('.wdm_edd_cart_item_licenses').find('.license_option_label').each(function(){
                            if(jQuery(this).hasClass('license_option_label_checked')){
                                jQuery(this).removeClass('license_option_label_checked');
                            }
                        });
                        

                        // Apply the css class to the new option selected
                        clicked_license.addClass('license_option_label_checked');
                        clicked_license.find('.license_options').each(function(){
                            jQuery(this).addClass('license_options_checked');
                        });
                        
                        // clicked_license.find('input[type="radio"]').each(function(){
                        //     jQuery(this).attr("checked", true);
                        // });

                        clicked_license.closest('tr').find('.wdm_edd_cart_item_price_value').html(response.item_value);

                        // Change the selected option indicating svg image
                        // clicked_license.find('.radio_button_img').each(function(){
                        //     jQuery(this).attr('src',wdmCheckoutAjax.asset_path+'/images/selected_radio.svg');
                        // });

                        // If any renewal was applied in the cart
                        if( typeof response.is_renewal!=='undefined' && jQuery('.edd-sl-renewal-details').length ){
                            if(response.is_renewal=='1'){
                                clicked_license.closest('tr').find('.edd-sl-renewal-details').remove();
                            }else if(response.is_renewal==false){
                                clicked_license.closest('tr').find('.edd-sl-renewal-details').remove();
                                jQuery('#edd-cancel-license-renewal').click();
                                jQuery('#edd_sl_cancel_renewal_form').remove();
                                jQuery('.edd-sl-renewal-actions').next('.edd-cart-adjustment').remove();
                            }
                        }
                        
                        updateCheckoutTotalSubtotalDetails(response);
                        // updateCheckoutCartKeys(parseInt(cart_key),'.wdm_edd_cart_item_action a');
                    }
                }
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

    function updateCheckoutTotalSubtotalDetails(response){
        // Change Total and Sub total
        jQuery('span.edd_cart_amount').each(function() {
            jQuery(this).data('total',response.total);
            jQuery(this).data('subtotal',response.subtotal);
			jQuery(this).html(response.subtotal);
        });

        // Update Sub total
        jQuery( '.cart-price-details-subtotal-value' ).each(function(){
            jQuery( this ).html(response.subtotal);
        });

        // After changing the option the total, subtotal, coupon value and percentage (amount) may change
        if(typeof response.coupon_value!=='undefined' ){
            jQuery( '.coupon-rate' ).each(function(){
                jQuery( this ).html('('+response.amount+')');
            });
            // Check if the exisitng discount code does not support newly added license option
            if(response.coupon_value==''){
                if(jQuery('#wdm-edd-remove-discount').length){
                    jQuery('#wdm-edd-remove-discount').data('code','');
                }
                jQuery('#edd-discount'/*, $checkout_form_wrap */).val('');
                jQuery( '.coupon-rate' ).each(function(){
                    jQuery( this ).html('');
                });
                jQuery('.coupon-value').each(function(){
                    jQuery( this ).html('');
                });
                jQuery('#applied-message,#wdm-edd-remove-discount').hide();
                jQuery('#wdm-block-discount-apply').show();
            }else{
                jQuery('#edd-discount'/*, $checkout_form_wrap */).val(response.code);
                jQuery('#wdm-edd-remove-discount').data('code',response.code);
                jQuery('#applied-message,#wdm-edd-remove-discount').show();
                jQuery('#wdm-block-discount-apply').hide();
                jQuery( '.coupon-rate' ).each(function(){
                    jQuery( this ).html('('+response.amount+')');
                });
                jQuery('.coupon-value').each(function(){
                    jQuery( this ).html(' - '+response.coupon_value);
                });
            }
        }
        jQuery('#cart-grandtotal').html(response.total);
        jQuery('#edd_final_total_wrap .edd_cart_amount').each(function(){
            jQuery(this).html(response.total);
            jQuery(this).attr('data-subtotal',response.subtotal_plain);
            jQuery(this).attr('data-total',response.total_plain);
        });
    }

    // Remove discount click
    jQuery('#wdm-edd-remove-discount').on('click', wdm_edd_checkout_remove_discount);

    function wdm_edd_checkout_remove_discount(event){
        event.preventDefault();
        $body = jQuery(document.body);

		var $this = jQuery(this), postData = {
			action: 'wdm_edd_remove_discount',
			code: $this.attr('data-code')
		};

		jQuery.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: wdmCheckoutAjax.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {
				jQuery('.edd_cart_amount span').each(function() {
					jQuery(this).attr('data-total',discount_response.total);
					jQuery(this).attr('data-subtotal',discount_response.subtotal);
					jQuery(this).html(discount_response.subtotal);
				});
				jQuery( '.coupon-rate' ).each(function(){
					jQuery( this ).html('');
				});
				jQuery('.coupon-value').each(function(){
					jQuery( this ).html('');
				});
				if(jQuery('#wdm-edd-remove-discount').length){
					jQuery('#wdm-edd-remove-discount').attr('data-code','');
				}
                jQuery('#cart-grandtotal').html(discount_response.total);
                jQuery('#edd_final_total_wrap .edd_cart_amount').each(function(){
                    jQuery(this).html(discount_response.total);
                    jQuery(this).attr('data-subtotal',discount_response.subtotal_plain);
                    jQuery(this).attr('data-total',discount_response.total_plain);
                });
				jQuery('#edd-discount'/*, $checkout_form_wrap */).val('');
				jQuery('#applied-message,#wdm-edd-remove-discount').hide();
				jQuery('#wdm-block-discount-apply').show();
				$body.trigger('edd_discount_removed', [ discount_response ]);
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
    }
    
    // Apply discount click
    jQuery('#wdm-block-discount-apply').on('click', wdm_edd_checkout_apply_discount);

    function wdm_edd_checkout_apply_discount(event) {

		event.preventDefault();
        $body = jQuery(document.body);

		var $this = jQuery(this),
			discount_code = jQuery('#edd-discount').val();
		if (discount_code == '' || discount_code == wdmCheckoutAjax.enter_discount ) {
            console.log();
			return false;
		}

		var postData = {
			action: 'wdm_edd_apply_discount',
			code: discount_code,
			email: jQuery( '#edd-user-email' ).val()
		};

		jQuery('#edd-discount-error-wrap').html('').hide();

		jQuery.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: wdmCheckoutAjax.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {
				if( discount_response ) {
					if (discount_response.msg == 'valid') {
						jQuery( '.edd_cart_amount span' ).each( function() {
							jQuery(this).attr('data-total',discount_response.total);
							jQuery(this).attr('data-subtotal',discount_response.subtotal);
							jQuery(this).html(discount_response.subtotal);
						} );
						jQuery('.coupon-value').each(function(){
							jQuery( this ).html(' - '+discount_response.coupon_value);
						});
						jQuery( '.coupon-rate' ).each(function(){
							jQuery( this ).html('('+discount_response.amount+')');
						});
						if(jQuery('#wdm-edd-remove-discount').length){
							jQuery('#wdm-edd-remove-discount').attr('data-code',discount_response.code);
						}
                        jQuery('#cart-grandtotal').html(discount_response.total);
                        jQuery('#edd_final_total_wrap .edd_cart_amount').each(function(){
                            jQuery(this).html(discount_response.total);
                            jQuery(this).attr('data-subtotal',discount_response.subtotal_plain);
                            jQuery(this).attr('data-total',discount_response.total_plain);
                        });
						jQuery('#edd-discount'/*, $checkout_form_wrap */).val(discount_response.code);
						jQuery('#wdm-block-discount-apply').hide();
						jQuery('#applied-message,#wdm-edd-remove-discount').show();
						$body.trigger('edd_discount_applied', [ discount_response ]);
					} else {
                        jQuery('#edd-discount').val('');
						jQuery('#edd-discount-error-wrap').html( '<span class="edd_error">' + discount_response.msg + '</span>' );
						jQuery('#edd-discount-error-wrap').show();
						$body.trigger('edd_discount_invalid', [ discount_response ]);
					}
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
		});
		return false;
	};

    function add_contact_us_menu_checkout(){
        if(jQuery('#menu-item-349684').length==0){
            jQuery('#menu-footer-connect-with-us').prepend('<li id="menu-item-349684" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-349684"><a href="/contact-us/" itemprop="url">Contact Us</a></li>');
        }
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
});