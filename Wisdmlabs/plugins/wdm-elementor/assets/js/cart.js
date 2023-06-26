jQuery(document).ready(function(){
    add_contact_us_menu_cart();    
    jQuery('body').on('click','.wdm_edd_cart_item_action a',function(e){
        e.preventDefault();
        var remove_clicked = jQuery(this);
        var cart_key = remove_clicked.attr('data-cart-key');
        var nonce = remove_clicked.attr('data-nonce');
        if(cart_key!==''){
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : wdmAjax.ajaxurl,
                data : {
                    action: "wdm_elem_cart_remove_item", 
                    nonce: nonce, 
                    cart_key: cart_key
                },
                success: function(response) {
                    if (response.msg == 'valid') {
                        remove_clicked.closest('tr').remove();
                        jQuery('.edd-cart-quantity').html(parseInt(jQuery('.edd-cart-quantity').html())-1);
                        updateTotalSubtotalDetails(response);
                        updateCartKeys(parseInt(cart_key),'.wdm_edd_cart_item_action a');
                    }
                }
            });
        }
    });
    
    jQuery('body').on('click','.license_options:not(.license_options_checked)',function(){
        var clicked_license = jQuery(this);
        var license_selected = clicked_license.find('input[type="radio"]').val();
        var nonce = clicked_license.find('input[type="radio"]').data('nonce');
        var cart_key = clicked_license.closest('tr').find('.wdm_edd_cart_item_action a').attr('data-cart-key');
        if(typeof cart_key !== 'undefined' && license_selected!=='' && nonce!==''){
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : wdmAjax.ajaxurl,
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

                        // Show applied license message
                        clicked_license.closest('.wdm_edd_cart_item_licenses').find('.radio_button_img').each(function(){
                            jQuery(this).attr('src',wdmAjax.asset_path+'/images/radio.svg');
                        });

                        // Select the new selected license option's radio button and deselect the old one
                        clicked_license.closest('.wdm_edd_cart_item_licenses').find('.license_options').each(function(){
                            if(jQuery(this).hasClass('license_options_checked')){
                                jQuery(this).removeClass('license_options_checked');
                                jQuery(this).find('input[type="radio"]').each(function(){
                                    jQuery(this).removeAttr("checked");
                                });
                            }
                        });

                        // Apply the css class to the new option selected
                        clicked_license.addClass('license_options_checked');
                        clicked_license.find('input[type="radio"]').each(function(){
                            jQuery(this).attr("checked", true);
                        });

                        clicked_license.closest('tr').find('.wdm_edd_cart_item_price_value').html(response.item_value);

                        // Change the selected option indicating svg image
                        clicked_license.find('.radio_button_img').each(function(){
                            jQuery(this).attr('src',wdmAjax.asset_path+'/images/selected_radio.svg');
                        });
                        
                        updateTotalSubtotalDetails(response);
                        updateCartKeys(parseInt(cart_key),'.wdm_edd_cart_item_action a');
                    }
                }
            });
        }   
    });

    function add_contact_us_menu_cart(){
        if(jQuery('#menu-item-349684').length==0){
            jQuery('#menu-footer-connect-with-us').prepend('<li id="menu-item-349684" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-349684"><a href="/contact-us/" itemprop="url">Contact Us</a></li>');
        }
    }

    jQuery('.wdm-block-cart-proceed-to-checkout a').on('click',function(){
        var date = new Date();
        date.setTime(date.getTime() + (60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
        document.cookie = encodeURIComponent('chkout_acceptance') + "=" + encodeURIComponent(1) + expires + "; path=/";
    });

    function updateCartKeys(cart_key,html_element){
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

    function updateTotalSubtotalDetails(response){
        // Change Total and Sub total
        jQuery('.edd_cart_amount span').each(function() {
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
                jQuery( this ).html(response.amount);
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
                    jQuery( this ).html(response.amount);
                });
                jQuery('.coupon-value').each(function(){
                    jQuery( this ).html(' - '+response.coupon_value);
                });
            }
        }
        jQuery('#cart-grandtotal').html(response.total);
    }
});