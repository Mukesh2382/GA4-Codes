if (document.readyState !== "loading") {
  wdm_ga4_ecommerce_select_item_init();
} else {
  document.addEventListener("DOMContentLoaded", function () {
    wdm_ga4_ecommerce_select_item_init();
  });
}
function wdm_ga4_ecommerce_select_item_init() {
  let view_details_button = document.querySelectorAll(".details-btn a");

  view_details_button.forEach(function (view_details_button) {
    view_details_button.addEventListener("click", function (e) {
      let urlString = this.href;
      let parts = urlString.split("/");
      let result = parts[parts.length - 2]; // get the second-to-last part

      let data = wdm_ga4_select_item[result];

      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        event: "select_item",
        ecommerce: {
          currency: "USD",
          value: data['price'],
          items: [
            {
              item_id: data['item_id'],
              item_name: data['item_name'],
              discount: 0.0,
              index: 0,
              item_brand: "WisdmLabs",
              item_category: data['item_category'],
              item_list_id: data['item_list_id'],
              item_list_name: data['item_list_name'],
              price: data['price'],
              quantity: 1,
            },
          ],
        },
      });
    });
  });
}
