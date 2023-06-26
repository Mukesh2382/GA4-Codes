document.addEventListener("DOMContentLoaded", function() {

    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ ecommerce: null });
    window.dataLayer.push({
        event: "view_item_list",
        ecommerce: {
        currency: "USD",
        item_list_id: wdm_ga4_view_item_list_downloads_data['item_list_id'],
        item_list_name: wdm_ga4_view_item_list_downloads_data['item_list_name'],
        items: wdm_ga4_view_item_list_downloads_data['items']
}
});
});