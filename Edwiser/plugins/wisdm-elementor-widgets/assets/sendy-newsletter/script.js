!function($){"use strict";function IsEmail(email){var regex;return!!/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email)}function refresh_response(parent,status,message){$(".sendy-failed-response").html(""),$(".sendy-success-response").html(""),"success"==status?$(parent).find(".sendy-success-response").html(message):$(parent).find(".sendy-failed-response").html(message)}function subscribe_to_newsletter(sendy_list_id,email_id,nonce,parent){jQuery.ajax({type:"post",dataType:"json",url:sendyAjax.ajaxurl,data:{action:"subscribe_to_newsletter",sendy_list_id:sendy_list_id,email_id:email_id,nonce:nonce},success:function(response){"success"==response.status?refresh_response(parent,"success","Subscribed to newsletter successfully"):refresh_response(parent,"failed","Failed to subscribe")}})}$(document).ready((function(){$(".wdm-elementor-sendy-newsletter #subscribe_email_id").keyup((function(){var parent=$(this).closest(".wdm-elementor-sendy-newsletter");let email_id;IsEmail($(this).val())?refresh_response(parent,"success","Valid email id"):refresh_response(parent,"failed","")})),$(".wdm-elementor-sendy-newsletter .wdm-sn-button a").click((function(){var parent=$(this).closest(".wdm-elementor-sendy-newsletter");let sendy_list_id=$(parent).find("#sendy_list_id").val(),email_id=$(parent).find("#subscribe_email_id").val(),nonce;if(!IsEmail(email_id))return refresh_response(parent,"failed","Enter valid email"),!1;subscribe_to_newsletter(sendy_list_id,email_id,$(this).data("nonce"),parent)}))}))}(jQuery);