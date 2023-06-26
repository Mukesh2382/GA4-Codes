jQuery(document).ready(function () {
  jQuery('select[name="wdm-downloads"],#edd-notice-period,#auto-manual').on('change', function () {
  		jQuery('#wdm-renewal-message-html').click();
    	var selected_download = jQuery('select[name="wdm-downloads"').val();
    	var selected_automanual = jQuery('select[name="auto-manual"').val();
    	var selected_period = jQuery('#edd-notice-period').val();
		if( selected_download > 0 ){
			formData = {action:'wdm_admin_renewal_email_data',download:selected_download+'_'+selected_period,automanual:selected_automanual};
			jQuery.ajax({
			    type: "post",
			    dataType: "json",
			    url: wdm_ajax_object.ajax_url,
			    data: formData,
			    success: function(msg){
			        if(msg!='0'){
			        	if(msg.subject!=''){
			        		jQuery('#edd-notice-subject').val(msg.subject);
			        	}
			        	if(msg.body!=''){
			        		console.log(msg.body);
			        		jQuery('#wdm-renewal-message').val(msg.body);
			        	}
			        	if(msg.enabled=='1'){
			        		jQuery('#edd-notice-status').prop('checked',true);
			        	}
			        	if(msg.auto=='1'){
			        		jQuery('#auto-manual').prop('checked',true);
			        	}
			        }else{
		        		jQuery('#edd-notice-subject').val('');
		        		jQuery('#wdm-renewal-message').val('');
		        		jQuery('#edd-notice-status').prop('checked',false);
		        		jQuery('#auto-manual').prop('checked',false);
			        }
			    }
			});
		}else{
    		jQuery('#edd-notice-subject').val('');
    		jQuery('#wdm-renewal-message').val('');
    		jQuery('#edd-notice-status').prop('checked',false);
    		jQuery('#auto-manual').prop('checked',false);
		}
    });
   jQuery('#wdm-test-purchase-email').on('click', function(e){
		var hasError = false;
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
 
        var emailaddressVal = jQuery('[name="wdm-test-email"]').val();
        jQuery('.wdm-test-email-error').remove();
        if(emailaddressVal == '') {
            jQuery(this).after(' <span class="wdm-test-email-error">Please enter your email address.</span>');
            hasError = true;
        }else if(!emailReg.test(emailaddressVal)) {
            jQuery(this).after(' <span class="wdm-test-email-error">Enter a valid email address.</span>');
			hasError = true;
        }else if( jQuery('#edd-notice-subject').val() == '' ){
        	jQuery(this).after(' <span class="wdm-test-email-error">Email Subject cannot be empty.</span>');
			hasError = true;
        }else if( jQuery('#wdm-renewal-message').val() == '' ){
        	jQuery(this).after(' <span class="wdm-test-email-error">Email body cannot be empty.</span>');
			hasError = true;
        }

        if(hasError == true) {
        	return false;
        }else{
        	event.preventDefault();
        	formData = {action:'wdm_admin_renewal_email_test_send',subject:jQuery('#edd-notice-subject').val(),to:emailaddressVal,body:jQuery('#wdm-renewal-message').val()};
			jQuery.ajax({
			    type: "post",
			    dataType: "json",
			    url: wdm_ajax_object.ajax_url,
			    data: formData,
			    success: function(msg){
			        if(msg!='0'){
			        	jQuery('#wdm-test-purchase-email').after(' <span class="wdm-test-email-error">Done.</span>');
			        	jQuery('[name="wdm-test-email"]').val('');
			        }else{
			        	jQuery('#wdm-test-purchase-email').after(' <span class="wdm-test-email-error">Error in sending the email, contact your developer.</span>');
			        }
			    }
			});
        }
   });
   jQuery('#copy-email-content').on('click', function(e){
   		e.preventDefault();
   		var selected_download = jQuery('select[name="copy-wdm-downloads"').val();
    	// var selected_automanual = jQuery('select[name="copy-auto-manual"').val();
    	// var selected_period = jQuery('select[name="copy-period"]').val();
		if( selected_download != '' ){
			formData = {action:'wdm_admin_renewal_email_copy_data',"copy-wdm-downloads":selected_download};
			jQuery.ajax({
			    type: "post",
			    dataType: "json",
			    url: wdm_ajax_object.ajax_url,
			    data: formData,
			    success: function(msg){
			        if(msg!='0'){
			        	if(msg.subject!=''){
			        		jQuery('#edd-notice-subject').val(msg.subject);
			        	}
			        	if(msg.body!=''){
			        		console.log(msg.body);
			        		jQuery('#wdm-renewal-message').val(msg.body);
			        	}
			        	// if(msg.enabled=='1'){
			        	// 	jQuery('#edd-notice-status').prop('checked',true);
			        	// }
			        	// if(msg.auto=='1'){
			        	// 	jQuery('#auto-manual').prop('checked',true);
			        	// }
			        }else{
		        		jQuery('#edd-notice-subject').val('');
		        		jQuery('#wdm-renewal-message').val('');
		        		// jQuery('#edd-notice-status').prop('checked',false);
		        		// jQuery('#auto-manual').prop('checked',false);
			        }
			    }
			});
		}else{
    		jQuery('#edd-notice-subject').val('');
    		jQuery('#wdm-renewal-message').val('');
    		// jQuery('#edd-notice-status').prop('checked',false);
    		// jQuery('#auto-manual').prop('checked',false);
		}
   });
});