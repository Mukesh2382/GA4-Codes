jQuery(document).ready(function(){
    jQuery('#send_test_email').on('click',function(e){e.preventDefault()});
    jQuery('#send_test_email').on('click',function(e){
        e.preventDefault();
        if(""!=jQuery("#select_download").val() && ""!=jQuery("#select_upgrade_option").val() && ""!=jQuery("#test_email").val()){
            var e={
                action:"wdm_upgrade_send_test_email",
                email:jQuery("#test_email").val(),
                select_email: jQuery("#select_email").val()
            };
            jQuery.ajax(
                {
                    url:frontend_ajax_object.ajaxurl,
                    data:e,
                    type:"POST",
                    beforeSend:function(e){},
                    success:function(e){
                        if(e==1){
                            MessageManager.show('<div class="updated notice"><p>Email sent successfully!</p></div>');
                        }else{
                            MessageManager.show('<div class="error notice"><p>There is an issue in sending email functionality!</p></div>');
                        }
                    }
                }
            );
        }else{
            MessageManager.show('<div class="error notice"><p>Please select a download, its upgrade option and also enter email field correctly.</p></div>');
            // alert('Please select a download, its upgrade option and also enter email field correctly');
        }
    });
    jQuery('#download_cutomers').DataTable({});
    jQuery("#select_download").change(function(){
        if(""!=jQuery("#select_download").val()){
            var e={
                action:"loadmore_upgrades",
                download:jQuery("#select_download").val()
            };
            jQuery.ajax(
                {
                    url:frontend_ajax_object.ajaxurl,
                    data:e,
                    type:"POST",
                    beforeSend:function(e){},
                    success:function(e){
                        if(e){
                            e = JSON.parse(e);   
                            if(e.options){
                                jQuery("#select_variation").find("option").remove().end().append(e.options);
                            }
                            if(e.upgrades){
                                jQuery("#select_upgrade_option").find("option").remove().end().append(e.upgrades);
                            }
                        }
                    }
                }
                );
            }
        }
    );
    var MessageManager = {
        show: function(content) {
            jQuery('#message-container').html(content);
            setTimeout(function(){
                jQuery('#message-container').html('');
            }, 5000);
        }
    };
});