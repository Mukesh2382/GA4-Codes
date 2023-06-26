jQuery(document).ready(function(){
    jQuery('input[name="cart_read_accept_policy_check"]').change(function(){
        if(jQuery(this).is(':checked')) {
            jQuery('.wdm-block-cart-proceed-to-checkout a.button.disabled').removeClass('disabled');
        }else{
            jQuery('.wdm-block-cart-proceed-to-checkout a.button').addClass('disabled');
        }
    });
    jQuery('.wdm-block-cart-proceed-to-checkout a.button').on('click',function(e){
        if(jQuery(this).hasClass('disabled')){
            return false;
        }else{
            // Do nothing
        }
    });
    
    jQuery('#wdm-block-discount-apply').on('click', wdm_edd_apply_discount);
    jQuery('#wdm-edd-remove-discount').on('click', wdm_edd_remove_discount);

    function wdm_edd_remove_discount(event){
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
			url: wdmDiscountAjax.ajaxurl,
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

    function wdm_edd_apply_discount(event) {

		event.preventDefault();
        $body = jQuery(document.body);

		var $this = jQuery(this),
			discount_code = jQuery('#edd-discount').val();
		if (discount_code == '' || discount_code == wdmDiscountAjax.enter_discount ) {
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
			url: wdmDiscountAjax.ajaxurl,
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
							jQuery( this ).html(discount_response.amount);
						});
						if(jQuery('#wdm-edd-remove-discount').length){
							jQuery('#wdm-edd-remove-discount').attr('data-code',discount_response.code);
						}
						jQuery('#cart-grandtotal').html(discount_response.total);
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
});
jQuery(window).load(function(){
    if(jQuery('input[name="cart_read_accept_policy_check"]').is(':checked')) {
        jQuery('.wdm-block-cart-proceed-to-checkout a.button.disabled').removeClass('disabled');
    }
});