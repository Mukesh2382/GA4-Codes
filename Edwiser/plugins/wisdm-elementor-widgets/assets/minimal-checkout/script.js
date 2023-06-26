jQuery(document).ready(function () {
  function updateCheckoutCartKeys(cart_key, html_element) {
    ($current_cart_element = ""),
      jQuery(html_element).each(function (i) {
        void 0 !== jQuery(this).attr("data-cart-key") &&
          (parseInt(jQuery(this).attr("data-cart-key")) > cart_key
            ? jQuery(this).attr(
                "data-cart-key",
                parseInt(jQuery(this).attr("data-cart-key")) - 1
              )
            : parseInt(jQuery(this).attr("data-cart-key")) == cart_key &&
              jQuery(this).attr(
                "data-cart-key",
                jQuery(html_element).length - 1
              ));
      });
  }
  function updateMinimalCheckoutTotalSubtotalDetails(response) {
    jQuery(".sub-total-row span.edd_cart_amount").each(function () {
      jQuery(this).data("total", response.total),
        jQuery(this).data("subtotal", response.subtotal),
        jQuery(this).html(response.subtotal);
    }),
      jQuery(".sub-subtotal-row span.edd_cart_amount").each(function () {
        jQuery(this).data("total", response.total),
          jQuery(this).data("subtotal", response.subtotal),
          jQuery(this).find(".regular-price").html(response.subtotal),
          jQuery(this).find(".discounted-price").html(response.total);
      }),
      jQuery(".cart-price-details-subtotal-value").each(function () {
        jQuery(this).html(response.subtotal);
      }),
      void 0 !== response.coupon_value &&
        ("" == response.coupon_value
          ? (($had_applied = 0),
            jQuery(".wdm_remove_coupon_field").each(function () {
              void 0 !== jQuery(this).attr("data-code") &&
                "" != jQuery(this).attr("data-code") &&
                ($had_applied = 1),
                jQuery(this).attr("data-code", "");
            }),
            $had_applied && jQuery("#wdm_coupon_field").show(),
            jQuery(".edd_cart_amount .regular-price").each(function () {
              jQuery(this).html("");
            }),
            jQuery(".sub-total-row").hide(),
            jQuery(".sub-subtotal-row").show(),
            jQuery("#wdm_coupon_amount").html(""),
            jQuery("#wdm_edd_discount_wrap").hide())
          : (jQuery(".wdm_remove_coupon_field").each(function () {
              jQuery(this).data("code", response.code);
            }),
            jQuery("#wdm_coupon_amount").html(" " + response.amount + " ")));
  }
  function wdm_edd_minimal_checkout_remove_discount(event) {
    event.preventDefault(), ($body = jQuery(document.body));
    var $this,
      postData = {
        action: "wdm_edd_remove_discount",
        code: jQuery(this).attr("data-code"),
      };
    return (
      0 == minimal_checkout_processing &&
        jQuery
          .ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: wdmCheckoutAjax.ajaxurl,
            xhrFields: { withCredentials: !0 },
            beforeSend: function (xhr) {
              minimal_checkout_processing = 1;
            },
            success: function (discount_response) {
              process_after_removing_discount(discount_response, jQuery(this)),
                $body.trigger("edd_discount_removed", [discount_response]);
            },
          })
          .fail(function (data) {
            window.console && window.console.log && console.log(data);
          })
          .complete(function (data) {
            minimal_checkout_processing = 0;
          }),
      !1
    );
  }
  function process_after_removing_discount(
    discount_response,
    remove_coupon_element
  ) {
    ($had_dis_applied = $hundred_percent_applied = 0),
      jQuery(".edd_cart_amount span.discounted-price").each(function () {
        jQuery(this).attr("data-total", discount_response.total_plain),
          jQuery(this).attr("data-subtotal", discount_response.subtotal_plain),
          jQuery(this).html(discount_response.total);
      }),
      jQuery(".sub-subtotal-row .edd_cart_amount").each(function () {
        void 0 !== jQuery(this).attr("data-total") &&
          "0" == jQuery(this).attr("data-total") &&
          ($hundred_percent_applied = 1);
      }),
      jQuery(".edd_cart_amount .regular-price").each(function () {
        jQuery(this).html("");
      }),
      jQuery(".sub-total-row").hide(),
      jQuery("#wdm_coupon_amount").html(""),
      jQuery(".wdm_remove_coupon_field").each(function () {
        void 0 !== jQuery(this).attr("data-code") &&
          "" != jQuery(this).attr("data-code") &&
          ($had_dis_applied = 1),
          jQuery(this).attr("data-code", "");
      }),
      jQuery("#wdm_edd_discount_wrap").hide(),
      jQuery("#wdm_coupon_field").show(),
      $had_dis_applied && $hundred_percent_applied && location.reload();
  }
  function wdm_edd_minimal_checkout_apply_discount(event) {
    event.preventDefault(), ($body = jQuery(document.body));
    var $this = jQuery(this),
      discount_code = jQuery("#wdm-edd-discount").val();
    if ("" == discount_code || discount_code == wdmCheckoutAjax.enter_discount)
      return console.log(), !1;
    var postData = {
      action: "wdm_edd_apply_discount",
      code: discount_code,
      email: jQuery("#edd-user-email").val(),
    };
    return (
      jQuery(".wdm_coupon_error").html("").hide(),
      jQuery("#wdm_coupon_error_wrap").hide(),
      0 == minimal_checkout_processing &&
        jQuery
          .ajax({
            type: "POST",
            data: postData,
            dataType: "json",
            url: wdmCheckoutAjax.ajaxurl,
            xhrFields: { withCredentials: !0 },
            beforeSend: function (xhr) {
              minimal_checkout_processing = 1;
            },
            success: function (discount_response) {
              discount_response
                ? process_after_apply_discount(discount_response)
                : (window.console &&
                    window.console.log &&
                    console.log(discount_response),
                  $body.trigger("edd_discount_failed", [discount_response]));
            },
          })
          .fail(function (data) {
            window.console && window.console.log && console.log(data);
          })
          .complete(function (data) {
            minimal_checkout_processing = 0;
          }),
      !1
    );
  }
  function process_after_apply_discount(discount_response) {
    "valid" == discount_response.msg
      ? (jQuery(".edd_cart_amount span.discounted-price").each(function () {
          jQuery(this).attr("data-total", discount_response.total_plain),
            jQuery(this).attr(
              "data-subtotal",
              discount_response.subtotal_plain
            ),
            jQuery(this).html(discount_response.total);
        }),
        jQuery(".edd_cart_amount .regular-price").each(function () {
          jQuery(this).html(discount_response.subtotal);
        }),
        jQuery(".sub-total-row").show(),
        jQuery("#wdm_coupon_amount").html(" " + discount_response.amount + " "),
        jQuery(".wdm_remove_coupon_field").each(function () {
          jQuery(this).attr("data-code", discount_response.code);
        }),
        "" !== discount_response.code &&
          0 == discount_response.total_plain &&
          location.reload(!0),
        jQuery("#wdm_edd_discount_wrap").show(),
        jQuery("#wdm_coupon_field").hide(),
        jQuery("#wdm-edd-discount").val(""),
        jQuery(".sub-subtotal-row").show(),
        jQuery(".wdm_edd_discount_applied").show(),
        jQuery(".sub-total-row .edd_cart_total").html("Sub Total"),
        $body.trigger("edd_discount_applied", [discount_response]))
      : (jQuery("#wdm_edd_discount_wrap").show(),
        jQuery("#wdm_coupon_field").hide(),
        jQuery("#wdm-edd-discount").val(""),
        jQuery(".wdm_coupon_error").html(
          '<span class="edd_error">' + discount_response.msg + "</span>"
        ),
        jQuery("#wdm_coupon_error_wrap").show(),
        jQuery(".wdm_coupon_error").show(),
        jQuery(".wdm_edd_discount_applied").hide(),
        $body.trigger("edd_discount_invalid", [discount_response]));
  }

  //OG code if changed does not work properly.
  // function wdm_edd_minimal_checkout_remove_product(e) {
  //   e.preventDefault();
  //   var remove_clicked = jQuery(this),
  //     cart_key = remove_clicked
  //       .closest("td")
  //       .find(".wdm_edd_cart_item_licenses")
  //       .attr("data-cart-key"),
  //     nonce = remove_clicked.attr("data-nonce");
  //   "" !== cart_key &&
  //     jQuery.ajax({
  //       type: "post",
  //       dataType: "json",
  //       url: wdmCheckoutAjax.ajaxurl,
  //       data: {
  //         action: "wdm_elem_cart_remove_item",
  //         nonce: nonce,
  //         cart_key: cart_key,
  //       },
  //       success: function (response) {

  //         "valid" == response.msg && (remove_clicked.closest("tr").remove(),
  //           (void 0 !==jQuery("#wdm_edd_checkout_cart .wdm_edd_cart_item_name_value") .length && 0 !=jQuery("#wdm_edd_checkout_cart .wdm_edd_cart_item_name_value").length) || location.reload(!0),
  //           jQuery(".edd-cart-quantity").html(parseInt(jQuery(".edd-cart-quantity").html()) - 1),
  //           updateMinimalCheckoutTotalSubtotalDetails(response),
  //           updateCheckoutCartKeys(parseInt(cart_key),"#wdm_edd_checkout_cart div.wdm_edd_cart_item_licenses"
  //           ));

  //       },
  //     });
  // }

  function wdm_edd_minimal_checkout_remove_product(e) {
    e.preventDefault();
    var index = $( ".wdm_remove_product" ).index( this );

    
    var remove_clicked = jQuery(this),
    
      cart_key = remove_clicked
        .closest("td")
        .find(".wdm_edd_cart_item_licenses")
        .attr("data-cart-key"),
      nonce = remove_clicked.attr("data-nonce");
    if ("" !== cart_key) {
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: wdmCheckoutAjax.ajaxurl,
        data: {
          action: "wdm_elem_cart_remove_item",
          nonce: nonce,
          cart_key: cart_key,
        },
        success: function (response) {
          if ("valid" == response.msg) {
            window.dataLayer.push({
              event: 'remove_from_cart',
              ecommerce: {
                  currency: 'USD',
                  value: parseFloat( wdm_ga4_cart_data[index].item_price ),
                  items: [{
                      item_id: wdm_ga4_cart_data[index].id,
                      item_name: wdm_ga4_cart_data[index].name,
                      discount: parseFloat( wdm_ga4_cart_data[index].discount ),
                      index: 0,
                      item_brand: 'Edwiser',
                      price: parseFloat( wdm_ga4_cart_data[index].item_price ),
                      quantity: parseFloat( wdm_ga4_cart_data[index].quantity ),
                  }]
              }
          });
            
            if (
              void 0 !==
                jQuery("#wdm_edd_checkout_cart .wdm_edd_cart_item_name_value")
                  .length &&
              0 !=
                jQuery("#wdm_edd_checkout_cart .wdm_edd_cart_item_name_value")
                  .length
            ) {
              location.reload(!0);
            }
            jQuery(".edd-cart-quantity").html(
              parseInt(jQuery(".edd-cart-quantity").html()) - 1
            );
            updateMinimalCheckoutTotalSubtotalDetails(response);
            updateCheckoutCartKeys(
              parseInt(cart_key),
              "#wdm_edd_checkout_cart div.wdm_edd_cart_item_licenses"
            );
          }
        },
      });
    }
  }

  // function wdm_edd_minimal_checkout_remove_product(e){
  //       e.preventDefault();
  //       var remove_clicked = jQuery(this);
  //       var cart_key = remove_clicked.closest('').find('.wdm_edd_cart_item_licenses').attr('data-cart-key');
  //       var nonce = remove_clicked.attr('data-nonce');
  //       if(cart_key!==''){
  //           jQuery.ajax({
  //               type : "post",
  //               dataType : "json",
  //               url : wdmCheckoutAjax.ajaxurl,
  //               data : {
  //                   action: "wdm_elem_cart_remove_item",
  //                   nonce: nonce,
  //                   cart_key: cart_key
  //               },
  //               success: function(response) {
  //                   if (response.msg == 'valid') {
  //                       // GA4 code
  //                       if ( 'undefined' != typeof response.ga4_data ) {
  //                           try {
  //                               window.dataLayer.push({
  //                                   event: 'remove_from_cart',
  //                                   ecommerce: {
  //                                       currency: 'USD',
  //                                       value: parseFloat( response.ga4_data.value ),
  //                                       items: [{
  //                                           item_id: response.ga4_data.single_item_data.item_id,
  //                                           item_name: response.ga4_data.single_item_data.item_name,
  //                                           discount: parseFloat( response.ga4_data.single_item_data.discount ),
  //                                           index: 0,
  //                                           item_brand: 'Edwiser',
  //                                           item_category: response.ga4_data.single_item_data.item_category,
  //                                           price: parseFloat( response.ga4_data.single_item_data.price ),
  //                                           quantity: parseFloat( response.ga4_data.single_item_data.quantity ),
  //                                       }]
  //                                   }
  //                               });
  //                               console.log( "GA4 remove_from_checkout successful." );
  //                           } catch( error ) {
  //                               console.log( "The download has not been configured for GA4." );
  //                           }
  //                       }
  //                       remove_clicked.closest('tr').remove();
  //                       if(typeof jQuery('#wdm_edd_checkout_cart .wdm_edd_cart_item_name_value').length=='undefined' || jQuery('#wdm_edd_checkout_cart .wdm_edd_cart_item_name_value').length==0){
  //                           location.reload(true);
  //                       }
  //                       jQuery('.edd-cart-quantity').html(parseInt(jQuery('.edd-cart-quantity').html())-1);
  //                       updateMinimalCheckoutTotalSubtotalDetails(response);
  //                       updateCheckoutCartKeys(parseInt(cart_key),'#wdm_edd_checkout_cart div.wdm_edd_cart_item_licenses');
  //                   }
  //               }
  //           });
  //       }

  //       // Remove row
  //       // Update total and sub total values
  //       // Check if discount is still there if not run discount removal process
  //     }

  function wdm_read_cookie(name) {
    for (
      var nameEQ = encodeURIComponent(name) + "=",
        ca = document.cookie.split(";"),
        i = 0;
      i < ca.length;
      i++
    ) {
      for (var c = ca[i]; " " === c.charAt(0); ) c = c.substring(1, c.length);
      if (0 === c.indexOf(nameEQ))
        return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
  }
  function wdm_is_email(email) {
    var regex;
    return !!/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(
      email
    );
  }
  function wdm_customer_registered_check(email, nonce) {
    if (!email) return !1;
    var postData = {
      action: "wdm_is_customer_registered",
      email: email,
      nonce: nonce,
    };
    jQuery
      .ajax({
        type: "POST",
        data: postData,
        dataType: "json",
        url: wdmCheckoutAjax.ajaxurl,
        xhrFields: { withCredentials: !0 },
        success: function (response) {
          void 0 !== response.message &&
            jQuery("#edd-email-wrap").append(response.message);
        },
      })
      .fail(function (data) {});
  }
  jQuery("#edd-purchase-button").on("click", function () {
    console.log("edd-purchase-button clicked"),
      "block" == jQuery("#edd_checkout_user_login").css("display") &&
        void 0 !== jQuery("#wdm_login_tab") &&
        (console.log("trigger click() -> wdm_login_tab"),
        jQuery("#wdm_login_tab").trigger("click"));
  }),
    (minimal_checkout_processing = 0),
    jQuery("#edd_checkout_form_wrap").on(
      "focusout",
      "#edd-email-wrap input",
      function () {
        jQuery(this).val().length > 3 &&
          (jQuery("#edd_user_login").val(jQuery(this).val()),
          jQuery("#edd-email-wrap").find(".edd-email-error").remove(),
          0 == wdm_is_email(jQuery(this).val())
            ? jQuery("#edd-email-wrap").append(
                '<span class="edd-email-error">Please enter a valid email!</span>'
              )
            : wdm_customer_registered_check(
                jQuery(this).val(),
                jQuery(this).attr("data-nonce")
              ));
      }
    ),
    jQuery("#edd_checkout_form_wrap").on("click", ".show-login", function () {
      jQuery("#edd_checkout_user_info").hide(),
        jQuery("#edd_checkout_user_login").show();
    }),
    jQuery("#edd_checkout_form_wrap").on(
      "click",
      "#wdm_login_tab",
      function () {
        jQuery("#edd_checkout_user_login").hide(),
          jQuery("#edd_checkout_user_info").show();
      }
    ),
    jQuery("#edd_checkout_form_wrap").on(
      "input",
      "#edd_user_pass_confirm",
      function () {
        jQuery("#edd_user_pass").val(jQuery(this).val());
      }
    ),
    jQuery("#edd_checkout_form_wrap").on(
      "change",
      "#edd_user_pass_confirm",
      function () {
        jQuery("#edd_user_login").val(jQuery("#edd-email-wrap input").val());
      }
    ),
    jQuery("#edd_checkout_form_wrap").on("input", "#edd-email", function () {
      jQuery("#edd_purchase_form").attr("data-email", jQuery(this).val());
    }),
    jQuery("#edd_checkout_form_wrap").on("input", "#edd-first", function () {
      jQuery("#edd_purchase_form").attr("data-first-name", jQuery(this).val());
    }),
    jQuery("#edd_checkout_form_wrap").on("input", "#edd-last", function () {
      jQuery("#edd_purchase_form").attr("data-last-name", jQuery(this).val());
    }),
    jQuery(".wdm-cart-register a").on("click", function () {
      jQuery(".wdm-block-login-register-container").hide(),
        jQuery(".log-reg-heading").hide(),
        jQuery(".wdm-block-checkout-container").show();
    }),
    jQuery("#edd_checkout_form_wrap").on(
      "click",
      ".edd_checkout_register_login",
      function (e) {
        e.preventDefault(),
          jQuery(".wdm-block-checkout-container").hide(),
          jQuery(".wdm-block-login-register-container").show(),
          jQuery(".log-reg-heading").show();
      }
    ),
    jQuery("select#edd-gateway, input.edd-gateway").on("change", function (e) {
      return (
        e.target.nodeName.includes("INPUT") &&
          (jQuery("input.edd-gateway").each(function () {
            jQuery(this).prev(".radio-span").removeClass("checked-radio-span");
          }),
          jQuery(this).prev(".radio-span").addClass("checked-radio-span")),
        jQuery("#edd_payment_mode_select_wrap").prependTo("#edd_purchase_form"),
        !1
      );
    }),
    jQuery("#apply_discount_check, #apply_renewal_check").on(
      "change",
      function (e) {
        return (
          e.target.nodeName.includes("apply_renewal_check") &&
            jQuery(
              "#edd-license-key-container-wrap,#edd_sl_show_renewal_form,.edd-sl-renewal-actions"
            ).toggle(),
          this.checked
            ? jQuery(this).closest("tr").next("tr").show()
            : jQuery(this).closest("tr").next("tr").hide(),
          !1
        );
      }
    ),
    $("#edd_checkout_form_wrap .edd-privacy-policy-agreement input").prop(
      "checked",
      !1
    ),
    $("#edd_checkout_form_wrap fieldset#edd_sendy input").prop("checked", !1),
    jQuery("#edd_checkout_form_wrap").on(
      "click",
      ".edd-privacy-policy-agreement label",
      function (e) {
        jQuery(
          "#edd_purchase_submit #edd-privacy-policy-agreement label"
        ).toggleClass("checked");
      }
    ),
    jQuery("#edd_checkout_form_wrap").on(
      "click",
      "fieldset#edd_sendy label",
      function (e) {
        jQuery("#edd_purchase_submit #edd_sendy label").toggleClass("checked");
      }
    ),
    jQuery(document).ajaxSend(function (event, xhr, settings) {
      jQuery(".paypal-selected-msg").remove(),
        void 0 !== settings.data &&
          settings.data.includes("action=edd_load_gateway") &&
          jQuery("#edd_payment_mode_select_wrap").prependTo(
            "#edd_purchase_form"
          );
    }),
    jQuery(document).ajaxComplete(function (event, xhr, settings) {
      void 0 !== settings.data &&
        settings.data.includes("action=edd_load_gateway") &&
        (void 0 !== xhr.responseText &&
          xhr.responseText.includes('fieldset id="edd_purchase_submit"') &&
          (jQuery("#edd_payment_mode_select_wrap").insertBefore(
            "#edd_purchase_submit"
          ),
          (chkout_acceptance = wdm_read_cookie("chkout_acceptance")),
          chkout_acceptance &&
            jQuery(".edd-privacy-policy-agreement label").trigger("click"),
          jQuery("#edd-email") &&
            void 0 !== jQuery("#edd_purchase_form").attr("data-email") &&
            (jQuery("#edd-email").val(
              jQuery("#edd_purchase_form").attr("data-email")
            ),
            jQuery("#edd_user_login").val(
              jQuery("#edd_purchase_form").attr("data-email")
            )),
          jQuery("#edd-first") &&
            void 0 !== jQuery("#edd_purchase_form").attr("data-first-name") &&
            jQuery("#edd-first").val(
              jQuery("#edd_purchase_form").attr("data-first-name")
            ),
          jQuery("#edd-last") &&
            void 0 !== jQuery("#edd_purchase_form").attr("data-last-name") &&
            jQuery("#edd-last").val(
              jQuery("#edd_purchase_form").attr("data-last-name")
            )),
        "paypalexpress" == jQuery('input[name="payment-mode"]:checked').val() &&
          jQuery("#edd-payment-mode-wrap").after(
            '<span class="paypal-selected-msg" style="font-size:12px;margin-left:10px">You will pay via <img style="margin-bottom:5px;vertical-align:middle" src="' +
              edd_scripts.edd_paypal_img +
              '"></span>'
          ),
        void 0 !== xhr.responseText &&
          xhr.responseText.includes('fieldset id="edd_cc_fields"') &&
          jQuery("#edd_cc_fields").insertBefore("#edd_purchase_submit"));
    }),
    jQuery("#edd_checkout_form_wrap").on(
      "click",
      ".wdm_remove_coupon_field",
      wdm_edd_minimal_checkout_remove_discount
    ),
    jQuery("#edd_checkout_form_wrap").on(
      "click",
      "#wdm_discount_coupon .wdm_edd_discount_link, #wdm_discount_coupon .wdm_try_again_coupon",
      function () {
        jQuery("#wdm_discount_coupon .wdm_edd_discount_link").hide(),
          jQuery("#wdm_coupon_error_wrap").hide(),
          jQuery("#wdm_coupon_field").show();
      }
    ),
    jQuery("#edd_checkout_form_wrap").on(
      "click",
      "#wdm-edd-discount-cancel-button",
      function () {
        jQuery("#wdm_coupon_field").hide(),
          jQuery(".wdm_remove_coupon_field").attr("data-code")
            ? jQuery("#wdm_edd_discount_wrap").hide()
            : jQuery("#wdm_discount_coupon .wdm_edd_discount_link").show();
      }
    ),
    jQuery("#edd_purchase_form").on(
      "click",
      '.tml-submit-wrap [name="wp-submit"]',
      function (e) {
        (clicked_button = jQuery(this)),
          (login_processing = 0),
          e.preventDefault();
        var $this = jQuery(this),
          postData = {
            action: "wdm_minimal_checkout_login",
            email: jQuery('.tml-user-login-wrap [name="log"]').val(),
            pass: jQuery('.tml-user-pass-wrap [name="pwd"]').val(),
            remember: jQuery('.tml-rememberme-wrap [name="rememberme"]').is(
              ":checked"
            )
              ? 1
              : 0,
            "wdm-security": jQuery("#wdm-security").val(),
          };
        return (
          0 == login_processing &&
            jQuery('.tml-user-login-wrap [name="log"]').val() &&
            jQuery('.tml-user-pass-wrap [name="pwd"]').val() &&
            (console.log("ifddd"),
            jQuery
              .ajax({
                type: "POST",
                data: postData,
                dataType: "json",
                url: wdmCheckoutAjax.ajaxurl,
                xhrFields: { withCredentials: !0 },
                beforeSend: function (xhr) {
                  jQuery(".login-message").remove(),
                    (login_processing = 1),
                    clicked_button.after(
                      '<span class="edd-loading-ajax edd-loading"></span>'
                    );
                },
                success: function (login_response) {
                  void 0 !== login_response.loggedin &&
                    (1 == login_response.loggedin
                      ? location.reload(!0)
                      : login_response.loggedin,
                    void 0 !== login_response.message &&
                      jQuery('.tml-submit-wrap input[name="wp-submit"]').after(
                        '<span class="login-message">' +
                          login_response.message +
                          "</span>"
                      ));
                },
              })
              .complete(function (data) {
                (login_processing = 0),
                  jQuery(".edd-loading-ajax").remove(),
                  window.console && window.console.log && console.log(data);
              })),
          !1
        );
      }
    ),
    jQuery("body").on(
      "click",
      "span.license_options:not(.license_options_checked)",
      function () {
        var clicked_license = jQuery(this),
          license_selected = clicked_license.find('input[type="radio"]').val(),
          nonce = clicked_license.find('input[type="radio"]').data("nonce");
        if (clicked_license.closest("div.wdm_edd_cart_item_licenses").length)
          var cart_key = clicked_license
            .closest("div.wdm_edd_cart_item_licenses")
            .attr("data-cart-key");
        else var cart_key = clicked_license.closest("td").attr("data-cart-key");
        void 0 !== cart_key &&
          "" !== license_selected &&
          "" !== nonce &&
          0 == minimal_checkout_processing &&
          jQuery
            .ajax({
              type: "post",
              dataType: "json",
              url: wdmCheckoutAjax.ajaxurl,
              data: {
                action: "wdm_elem_cart_change_license",
                license: license_selected,
                nonce: nonce,
                cart_key: cart_key,
                email: jQuery("#edd-user-email").val(),
              },
              beforeSend: function (xhr) {
                (minimal_checkout_processing = 1),
                  clicked_license
                    .closest("div.wdm_edd_cart_item_licenses")
                    .after(
                      '<span class="edd-loading-ajax edd-loading"></span>'
                    );
              },
              success: function (response) {
                "valid" == response.msg &&
                  (clicked_license
                    .closest(".wdm_edd_cart_item_licenses")
                    .find(".radio_button_img")
                    .each(function () {
                      jQuery(this).attr(
                        "src",
                        wdmCheckoutAjax.asset_path + "/images/radio.svg"
                      );
                    }),
                  clicked_license
                    .closest(".wdm_edd_cart_item_licenses")
                    .find(".license_options")
                    .each(function () {
                      jQuery(this).hasClass("license_options_checked") &&
                        jQuery(this).removeClass("license_options_checked");
                    }),
                  clicked_license.addClass("license_options_checked"),
                  clicked_license.find('input[type="radio"]').each(function () {
                    jQuery(this).attr("checked", !0);
                  }),
                  clicked_license
                    .closest("tr")
                    .find(".wdm_edd_cart_item_price_value")
                    .html(response.item_value),
                  clicked_license.find(".radio_button_img").each(function () {
                    jQuery(this).attr(
                      "src",
                      wdmCheckoutAjax.asset_path + "/images/selected_radio.svg"
                    );
                  }),
                  void 0 !== response.is_renewal &&
                    jQuery(".edd-sl-renewal-details").length &&
                    ("1" == response.is_renewal
                      ? clicked_license
                          .closest("tr")
                          .find(".edd-sl-renewal-details")
                          .remove()
                      : 0 == response.is_renewal &&
                        (clicked_license
                          .closest("tr")
                          .find(".edd-sl-renewal-details")
                          .remove(),
                        jQuery("#edd-cancel-license-renewal").click(),
                        jQuery("#edd_sl_cancel_renewal_form").remove(),
                        jQuery(".edd-sl-renewal-actions")
                          .next(".edd-cart-adjustment")
                          .remove(),
                        jQuery(
                          ".wdm-block-cart-price-details-renewal-check .apply_check_label"
                        ).remove())),
                  updateMinimalCheckoutTotalSubtotalDetails(response),
                  updateCheckoutCartKeys(
                    parseInt(cart_key),
                    "#wdm_edd_checkout_cart div.wdm_edd_cart_item_licenses"
                  ));
              },
            })
            .complete(function (data) {
              (minimal_checkout_processing = 0),
                jQuery(".edd-loading-ajax").remove();
            });
      }
    ),
    jQuery("#edd_checkout_form_wrap").on(
      "click",
      "#wdm-edd-discount-button",
      wdm_edd_minimal_checkout_apply_discount
    ),
    jQuery("#wdm_edd_checkout_cart").on(
      "click",
      ".wdm_remove_product",
      wdm_edd_minimal_checkout_remove_product
    );
});
