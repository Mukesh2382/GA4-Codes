(function($) {
    $(document).ready(function() {
      // $('select').on('change', function (e) {
      //     var optionSelected = $("option:selected", this);
      //     var valueSelected = this.value;
      //     if(valueSelected!==''){
      //       nonce = optionSelected.data('nonce');
      //       option = optionSelected.data('option-id');
      //       download = optionSelected.data('download-id');
      //       remove_cart_btn = $(optionSelected.closest('td').next('td').next('td').find('.edd_cart_remove_item_btn'));
      //       custom_remove_cart = remove_cart_btn.attr('href')+'&wdm_nonce='+nonce+'&wdm_add_cart_dnld='+download+'&wdm_add_cart_optn='+option;
      //       remove_cart_btn.attr('href',custom_remove_cart);
      //       remove_cart_btn[0].click();
      //     }
      // });
        if($('.edd_discount_rate').html()!==undefined){
            $('.edd-label[for="edd-discount"]').html('<span style="color:#155724">Discount applied!</span>');
        }
        $( document ).ajaxComplete(function( event, xhr, settings ) {
            if(typeof settings.data !== 'undefined' && settings.data.includes('action=edd_remove_discount')){
                if(typeof xhr.responseText !=='undefined' && xhr.responseText.includes('"discounts":[],"html":null')){
                    $('.edd-label[for="edd-discount"]').html('Discount');
                }
            }
            if(typeof settings.data !== 'undefined' && settings.data.includes('action=edd_apply_discount')){
                if(typeof xhr.responseText !=='undefined' && xhr.responseText.includes('"msg":"valid"') && xhr.responseText.includes('edd_discount_rate')){
                    $('.edd-label[for="edd-discount"]').html('<span style="color:#155724">Discount applied!</span>');
                }
            }
        });

      $('input.selected_more_options').on('change', function (e) {
          var optionSelected = $('input[name="'+$(this).prop('name')+'"]:checked');
          var valueSelected = this.value;
          if(valueSelected!==''){
            nonce = optionSelected.data('nonce');
            option = optionSelected.data('option-id');
            download = optionSelected.data('download-id');
            remove_cart_btn = $($(this).closest('td').next('td').next('td').find('.edd_cart_remove_item_btn'));
            custom_remove_cart = remove_cart_btn.attr('href')+'&wdm_nonce='+nonce+'&wdm_add_cart_dnld='+download+'&wdm_add_cart_optn='+option;
            remove_cart_btn.attr('href',custom_remove_cart);
            remove_cart_btn[0].click();
          }
      });
      // $(document).on("click", ".wdm_popup_close", function(){
      //   $('.popup_close').click();
      // });
      //close button
      jQuery(document).on('click', '.wisdm-close, .wisdm-complete', function(){
        jQuery('.wisdm-popup-container').css('display', 'none');
      })
    });
    // Exit intent popup on checkout
    'use strict';
      var opened;
      $.fn.setCookie = function (cname, cvalue, exdays) {
          var d = new Date();
          d.setTime(d.getTime() + (exdays*24*60*60*1000));
          console.log(d.getTime() + (exdays*24*60*60*1000));
          var expires = "expires=" + d.toGMTString();
          document.cookie = cname+" = "+cvalue+"; "+ expires +"; path=/"
      }
      $.fn.getCookie = function (cname) {
          if (document.cookie.indexOf(cname) >= 0) {
              return true;
          } else {
              return false;
          }
      }
      $.fn.checkCookie = function () {
          var user= $.fn.getCookie("e_i_opened");
          if (user) {
              opened = true;
          } else {
              opened = false;
          }
      }
     
      $.fn.checkout_pop = function () {
          $.fn.checkCookie();
          $('body').mouseleave(function (event) {
              var top = jQuery(document).scrollTop();
              if (event.pageY < top && !opened && $('.wisdm-popup-container').length ) {
                  opened = true;
                  $.fn.setCookie("e_i_opened", "true", 1);
                  jQuery('.wisdm-popup-container').css('display', 'flex');
              }
          });
      }
      $(document).checkout_pop();
  })(jQuery);
  