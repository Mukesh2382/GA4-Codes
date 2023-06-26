var edd_scripts;
function edd_load_gateway(payment_mode) {
  jQuery(".edd-cart-ajax").show(),
    jQuery("#edd_purchase_form_wrap").html(
      '<span class="edd-loading-ajax edd-loading"></span>'
    );
  var nonce = jQuery("#edd-gateway-" + payment_mode).data(
      payment_mode + "-nonce"
    ),
    url = edd_scripts.ajaxurl;
  url.indexOf("?") > 0 ? (url += "&") : (url += "?"),
    (url = url + "payment-mode=" + payment_mode),
    jQuery.post(
      url,
      {
        action: "edd_load_gateway",
        edd_payment_mode: payment_mode,
        nonce: nonce,
      },
      function (response) {
        jQuery("#edd_purchase_form_wrap").html(response),
          jQuery(".edd-no-js").hide(),
          jQuery("body").trigger("edd_gateway_loaded", [payment_mode]);
      }
    );
}
jQuery(document).ready(function ($) {
  if (
    ($(".edd-no-js").hide(),
    $("a.edd-add-to-cart").addClass("edd-has-js"),
    $(document.body).on(
      "click.eddRemoveFromCart",
      ".edd-remove-from-cart",
      function (event) {
        var $this = $(this),
          item = $this.data("cart-item"),
          action = $this.data("action"),
          id = $this.data("download-id"),
          nonce = $this.data("nonce"),
          data = { action: action, cart_item: item, nonce: nonce };
        return (
          $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: edd_scripts.ajaxurl,
            xhrFields: { withCredentials: !0 },
            success: function (response) {
              if (response.removed) {
                if (
                  parseInt(edd_scripts.position_in_cart, 10) ===
                    parseInt(item, 10) ||
                  edd_scripts.has_purchase_links
                )
                  return (window.location = window.location), !1;
                $(".edd-cart").each(function () {
                  $(this)
                    .find("[data-cart-item='" + item + "']")
                    .parent()
                    .remove();
                }),
                  $(".edd-cart").each(function () {
                    var cart_item_counter = 0;
                    $(this)
                      .find("[data-cart-item]")
                      .each(function () {
                        $(this).attr("data-cart-item", cart_item_counter),
                          (cart_item_counter += 1);
                      });
                  }),
                  $("[id^=edd_purchase_" + id + "]").length &&
                    ($(
                      "[id^=edd_purchase_" + id + "] .edd_go_to_checkout"
                    ).hide(),
                    $("[id^=edd_purchase_" + id + "] a.edd-add-to-cart")
                      .show()
                      .removeAttr("data-edd-loading"),
                    "1" == edd_scripts.quantities_enabled &&
                      $(
                        "[id^=edd_purchase_" +
                          id +
                          "] .edd_download_quantity_wrapper"
                      ).show()),
                  $("span.edd-cart-quantity").text(response.cart_quantity),
                  $(document.body).trigger("edd_quantity_updated", [
                    response.cart_quantity,
                  ]),
                  edd_scripts.taxes_enabled &&
                    ($(".cart_item.edd_subtotal span").html(response.subtotal),
                    $(".cart_item.edd_cart_tax span").html(response.tax)),
                  $(".cart_item.edd_total span").html(response.total),
                  0 == response.cart_quantity &&
                    ($(
                      ".cart_item.edd_subtotal,.edd-cart-number-of-items,.cart_item.edd_checkout,.cart_item.edd_cart_tax,.cart_item.edd_total"
                    ).hide(),
                    $(".edd-cart").each(function () {
                      var cart_wrapper = $(this).parent();
                      cart_wrapper &&
                        (cart_wrapper.addClass("cart-empty"),
                        cart_wrapper.removeClass("cart-not-empty")),
                        $(this).append(
                          '<li class="cart_item empty">' +
                            edd_scripts.empty_cart_message +
                            "</li>"
                        );
                    })),
                  $(document.body).trigger("edd_cart_item_removed", [response]);
              }
            },
          })
            .fail(function (response) {
              window.console && window.console.log && console.log(response);
            })
            .done(function (response) {}),
          !1
        );
      }
    ),
    $(document.body).on("click.eddAddToCart", ".edd-add-to-cart", function (e) {
      e.preventDefault();
      var $this = $(this),
        form = $this.closest("form");
      $this.prop("disabled", !0);
      var $spinner = $this.find(".edd-loading"),
        container = $this.closest("div");
      $this.attr("data-edd-loading", "");
      var form = $this.parents("form").last(),
        download = $this.data("download-id"),
        variable_price = $this.data("variable-price"),
        price_mode = $this.data("price-mode"),
        nonce = $this.data("nonce"),
        item_price_ids = [],
        free_items = !0;
      if ("yes" == variable_price)
        if (
          form.find(".edd_price_option_" + download + '[type="hidden"]')
            .length > 0
        )
          (item_price_ids[0] = $(".edd_price_option_" + download, form).val()),
            form.find(".edd-submit").data("price") &&
              form.find(".edd-submit").data("price") > 0 &&
              (free_items = !1);
        else {
          if (
            !form.find(".edd_price_option_" + download + ":checked", form)
              .length
          )
            return (
              $this.removeAttr("data-edd-loading"),
              alert(edd_scripts.select_option),
              e.stopPropagation(),
              $this.prop("disabled", !1),
              !1
            );
          form
            .find(".edd_price_option_" + download + ":checked", form)
            .each(function (index) {
              if (
                ((item_price_ids[index] = $(this).val()), !0 === free_items)
              ) {
                var item_price = $(this).data("price");
                item_price && item_price > 0 && (free_items = !1);
              }
            });
        }
      else
        (item_price_ids[0] = download),
          $this.data("price") && $this.data("price") > 0 && (free_items = !1);
      if (
        (free_items && form.find(".edd_action_input").val("add_to_cart"),
        "straight_to_gateway" == form.find(".edd_action_input").val())
      )
        return form.submit(), !0;
      var action,
        data = {
          action: $this.data("action"),
          download_id: download,
          price_ids: item_price_ids,
          post_data: $(form).serialize(),
          nonce: nonce,
        };
      return (
        $.ajax({
          type: "POST",
          data: data,
          dataType: "json",
          url: edd_scripts.ajaxurl,
          xhrFields: { withCredentials: !0 },
          success: function (response) {
            var store_redirect = "1" == edd_scripts.redirect_to_checkout,
              item_redirect =
                "1" == form.find("#edd_redirect_to_checkout").val();
            if (
              (store_redirect && item_redirect) ||
              (!store_redirect && item_redirect)
            )
              window.location = edd_scripts.checkout_page;
            else {
              "1" === edd_scripts.taxes_enabled &&
                ($(".cart_item.edd_subtotal").show(),
                $(".cart_item.edd_cart_tax").show()),
                $(".cart_item.edd_total").show(),
                $(".cart_item.edd_checkout").show(),
                $(".cart_item.empty").length && $(".cart_item.empty").hide(),
                $(".widget_edd_cart_widget .edd-cart").each(function (cart) {
                  var target = $(this).find(".edd-cart-meta:first");
                  $(response.cart_item).insertBefore(target);
                  var cart_wrapper = $(this).parent();
                  cart_wrapper &&
                    (cart_wrapper.addClass("cart-not-empty"),
                    cart_wrapper.removeClass("cart-empty"));
                }),
                "1" === edd_scripts.taxes_enabled &&
                  ($(".edd-cart-meta.edd_subtotal span").html(
                    response.subtotal
                  ),
                  $(".edd-cart-meta.edd_cart_tax span").html(response.tax)),
                $(".edd-cart-meta.edd_total span").html(response.total);
              var items_added = $(
                ".edd-cart-item-title",
                response.cart_item
              ).length;
              if (
                ($("span.edd-cart-quantity").each(function () {
                  $(this).text(response.cart_quantity),
                    $(document.body).trigger("edd_quantity_updated", [
                      response.cart_quantity,
                    ]);
                }),
                "none" == $(".edd-cart-number-of-items").css("display") &&
                  $(".edd-cart-number-of-items").show("slow"),
                ("no" != variable_price && "multi" == price_mode) ||
                  ($("a.edd-add-to-cart", container).toggle(),
                  $(".edd_go_to_checkout", container).css(
                    "display",
                    "inline-block"
                  )),
                "multi" == price_mode && $this.removeAttr("data-edd-loading"),
                $(".edd_download_purchase_form").length &&
                  ("no" == variable_price ||
                    !form
                      .find(".edd_price_option_" + download)
                      .is("input:hidden")))
              ) {
                var parent_form = $(
                  '.edd_download_purchase_form *[data-download-id="' +
                    download +
                    '"]'
                ).parents("form");
                $("a.edd-add-to-cart", parent_form).hide(),
                  "multi" != price_mode &&
                    parent_form
                      .find(".edd_download_quantity_wrapper")
                      .slideUp(),
                  $(".edd_go_to_checkout", parent_form)
                    .show()
                    .removeAttr("data-edd-loading");
              }
              "incart" != response &&
                ($(".edd-cart-added-alert", container).fadeIn(),
                setTimeout(function () {
                  $(".edd-cart-added-alert", container).fadeOut();
                }, 3e3)),
                $this.prop("disabled", !1),
                $(document.body).trigger("edd_cart_item_added", [response]);
            }
          },
        })
          .fail(function (response) {
            window.console && window.console.log && console.log(response);
          })
          .done(function (response) {}),
        !1
      );
    }),
    $("#edd_checkout_form_wrap").on(
      "click",
      ".edd_checkout_register_login",
      function () {
        return !1;
        var $this, data;
      }
    ),
    $(document).on(
      "click",
      "#edd_purchase_form #edd_login_fields input[type=submit]",
      function (e) {
        e.preventDefault();
        var complete_purchase_val = $(this).val();
        $(this).val(edd_global_vars.purchase_loading),
          $(this).after('<span class="edd-loading-ajax edd-loading"></span>');
        var data = {
          action: "edd_process_checkout_login",
          edd_ajax: 1,
          edd_user_login: $("#edd_login_fields #edd_user_login").val(),
          edd_user_pass: $("#edd_login_fields #edd_user_pass").val(),
          edd_login_nonce: $("#edd_login_nonce").val(),
        };
        $.post(edd_global_vars.ajaxurl, data, function (data) {
          "success" == $.trim(data)
            ? ($(".edd_errors").remove(),
              (window.location = edd_scripts.checkout_page))
            : ($("#edd_login_fields input[type=submit]").val(
                complete_purchase_val
              ),
              $(".edd-loading-ajax").remove(),
              $(".edd_errors").remove(),
              $("#edd-user-login-submit").before(data));
        });
      }
    ),
    $("select#edd-gateway, input.edd-gateway").change(function (e) {
      var payment_mode = $(
        "#edd-gateway option:selected, input.edd-gateway:checked"
      ).val();
      return "0" != payment_mode && (edd_load_gateway(payment_mode), !1);
    }),
    "1" == edd_scripts.is_checkout)
  ) {
    var chosen_gateway = !1,
      ajax_needed = !1;
    $("select#edd-gateway, input.edd-gateway").length &&
      ((chosen_gateway = $("meta[name='edd-chosen-gateway']").attr("content")),
      (ajax_needed = !0)),
      chosen_gateway || (chosen_gateway = edd_scripts.default_gateway),
      ajax_needed
        ? setTimeout(function () {
            edd_load_gateway(chosen_gateway);
          }, 200)
        : $("body").trigger("edd_gateway_loaded", [chosen_gateway]);
  }
  function update_state_field() {
    var $this = $(this),
      $form,
      is_checkout = "undefined" != typeof edd_global_vars,
      field_name = "card_state";
    "edd_address_country" == $(this).attr("id") &&
      (field_name = "edd_address_state");
    var state_inputs = document.getElementById(field_name);
    if ("card_state" != $this.attr("id") && null != state_inputs) {
      var nonce = $(this).data("nonce"),
        postData = {
          action: "edd_get_shop_states",
          country: $this.val(),
          field_name: field_name,
          nonce: nonce,
        };
      $.ajax({
        type: "POST",
        data: postData,
        url: edd_scripts.ajaxurl,
        xhrFields: { withCredentials: !0 },
        success: function (response) {
          $form = is_checkout ? $("#edd_purchase_form") : $this.closest("form");
          var state_inputs =
            'input[name="card_state"], select[name="card_state"], input[name="edd_address_state"], select[name="edd_address_state"]';
          if ("nostates" == $.trim(response)) {
            var text_field =
              '<input type="text" id=' +
              field_name +
              ' name="card_state" class="card-state edd-input required" value=""/>';
            $form.find(state_inputs).replaceWith(text_field);
          } else $form.find(state_inputs).replaceWith(response);
          is_checkout &&
            $(document.body).trigger("edd_cart_billing_address_updated", [
              response,
            ]);
        },
      })
        .fail(function (data) {
          window.console && window.console.log && console.log(data);
        })
        .done(function (data) {
          is_checkout && recalculate_taxes();
        });
    } else is_checkout && recalculate_taxes();
    return !1;
  }
  $(document).on(
    "click",
    "#edd_purchase_form #edd_purchase_submit [type=submit]",
    function (e) {
      var eddPurchaseform = document.getElementById("edd_purchase_form");
      if (
        "function" != typeof eddPurchaseform.checkValidity ||
        !1 !== eddPurchaseform.checkValidity()
      ) {
        e.preventDefault();
        var complete_purchase_val = $(this).val();
        $(this).val(edd_global_vars.purchase_loading),
          $(this).prop("disabled", !0),
          $(this).after('<span class="edd-loading-ajax edd-loading"></span>'),
          $.post(
            edd_global_vars.ajaxurl,
            $("#edd_purchase_form").serialize() +
              "&action=edd_process_checkout&edd_ajax=true",
            function (data) {
              if ($.trim(data) == "success") {
                $.post(edd_global_vars.ajaxurl, 'action=wdm_ga4_add_payment_info', function( ga4_data ){
                    try {
                        //check if payment mode is credit card or paypal
                        var payment_element = document.querySelector('#pp_pay');
                        if(payment_element.checked){
                            var pay_mode = 'Paypal';
                        }
                        else{
                            var pay_mode = 'Razorpay';
                        }
                        var coupon_c = document.querySelector('.wdm_remove_coupon_field').getAttribute('data-code');
                        if(!coupon_c){
                            var coupon_code = 'Not Applied';
                        }
                        else{
                            var coupon_code = coupon_c;
                        }
                        window.dataLayer.push({
                            event: 'add_payment_info',
                            ecommerce: {
                                currency: "USD",
                                value: ga4_data.value,
                                payment_type: pay_mode,
                                coupon: coupon_code,
                                items: ga4_data.items,
                            }
                        });
                        console.log( "GA4 add_payment_info successful." );
                    } catch( error ) {
                        console.log( "Some issue related to GA4." );
                    }
                })
                .always(function(){
                    $('.edd_errors').remove();
                    $('.edd-error').hide();
                    $(eddPurchaseform).submit();
                });
              } else {
                $("#edd-purchase-button").val(complete_purchase_val);
                $(".edd-loading-ajax").remove();
                $(".edd_errors").remove();
                $(".edd-error").hide();
                $(edd_global_vars.checkout_error_anchor).before(data);
                $("#edd-purchase-button").prop("disabled", !1);
                $(document.body).trigger("edd_checkout_error", [data]);
              }
            }
          );
      }
    }
  ),
    $(document.body).on(
      "change",
      "#edd_cc_address input.card_state, #edd_cc_address select, #edd_address_country",
      update_state_field
    ),
    $(document.body).on(
      "change",
      "#edd_cc_address input[name=card_zip]",
      function () {
        "undefined" != typeof edd_global_vars && recalculate_taxes();
      }
    );
});
