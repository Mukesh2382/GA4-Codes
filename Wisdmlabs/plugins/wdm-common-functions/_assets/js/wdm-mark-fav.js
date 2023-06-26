(function($) {
  $(document).ready(function() {
    function createCookie(cookieName, cookieValue, daysToExpire) {
      var date = new Date();
      date.setTime(date.getTime() + daysToExpire * 24 * 60 * 60 * 1000);
      document.cookie =
        cookieName +
        "=" +
        cookieValue +
        "; expires=" +
        date.toGMTString() +
        "; path=/";
    }
    function accessCookie(cookieName) {
      var name = cookieName + "=";
      var allCookieArray = document.cookie.split(";");
      for (var i = 0; i < allCookieArray.length; i++) {
        var temp = allCookieArray[i].trim();
        if (temp.indexOf(name) > 0) return temp;
      }
      return "";
    }
    function deleteCookie(name) {
      document.cookie =
        name + "=;expires=Thu, 01 Jan 1970 00:00:01 GMT;" + " path=/";
    }
    var mark_fav_prod_click_processing = false;
    if (window.wdm_js_obj_for_fav_prod.wdmuid == 0) {
      $(".mark-fav-prod").each(function(index) {
        var feature_id = $(this).data("feature-id");
        var wdm_fav_cookie = accessCookie("wdm-p-f-" + feature_id).replace(
          "=1",
          ""
        );
        if (!$(this).hasClass("h-filled") && wdm_fav_cookie != "") {
          $(this).addClass("h-filled");
        }
      });
    }
    $("body").on("click", ".mark-fav-prod", function() {
      // mark_fav_prod_click_processing = true;
      var clicked_element = $(this);
      var feature_id = $(this).data("feature-id");
      var verify = $(this).data("verify");
      var rm = 0;

      var wdm_fav_cookie = accessCookie("wdm-p-f-" + feature_id).replace(
        "=1",
        ""
      );
      if ($(this).hasClass("h-filled") && wdm_fav_cookie != "") {
        rm = 1;
      }
      if (!$(this).hasClass("h-filled") && wdm_fav_cookie=='' && feature_id!='') {
        $('span[data-feature-id="'+feature_id+'"]').addClass('h-filled');
      }
      if (
        !mark_fav_prod_click_processing &&
        (wdm_fav_cookie == "" || rm == 1)
      ) {
        mark_fav_prod_click_processing = true;
        $.post(
          window.wdm_js_obj_for_fav_prod.ajaxurl,
          {
            action: "wdm_prod_fav",
            feature_id: feature_id,
            wdm_prod_fav: verify,
            rm: rm,
            user: wdm_fav_cookie
          },
          function(data) {
            mark_fav_prod_click_processing = false;
            var response = $.parseJSON(data);
            // Set cookie and change heart button css class or if required make it disabled
            if (data == "0") {
              console.log(wdm_fav_cookie);
              deleteCookie(wdm_fav_cookie);
              $('span[data-feature-id="'+feature_id+'"]').removeClass('h-filled');
            } else if (data == "1") {
              console.log("Invalid Access 1");
            } else if (
              typeof response == "object" &&
              typeof response.cookie != "undefined"
            ) {
              createCookie(response.cookie, 1, 365);
            } else {
              console.log("Invalid Access 2");
            }
          }
        );
      }
    });
  });
})(jQuery);
