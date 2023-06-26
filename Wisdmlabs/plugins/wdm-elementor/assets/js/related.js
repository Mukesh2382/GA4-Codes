jQuery(document).ready(function(){
    
    jQuery('#wdm-block-related-product-cta a').on('click', wdm_edd_add_to_cart);

    function wdm_edd_add_to_cart(event){
        event.preventDefault();
        var related_product = jQuery(this);
        var nonce = related_product.data('nonce');
        var download = related_product.data('download');
        if(typeof download !== 'undefined' && download!=='' && nonce!==''){
            jQuery.ajax({
                type : "post",
                dataType : "json",
                url : wdmAjax.ajaxurl,
                data : {
                    action: "wdm_elem_related_product_add_to_cart", 
                    download : download, 
                    nonce: nonce
                },
                success: function(response) {
                    if (response.msg == 'valid') {
                        jQuery('.wdm-block-cart-table').find('tbody').append(response.tr);
                        jQuery('.edd-cart-quantity').html(parseInt(jQuery('.edd-cart-quantity').html())+1);
                        jQuery('.wdm-block-related-products-heading').remove();
                        jQuery('.wdm-block-related-products').remove();
                        updateTotalSubtotalDetailsInRelated(response);
                    }
                }
            });
        }   
    }

    function updateTotalSubtotalDetailsInRelated(response){
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
