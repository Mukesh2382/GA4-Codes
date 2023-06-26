(function(){
    if ( document.readyState !== 'loading' ) {
        wdm_ga4_ecommerce_purchase_event_init();
    } else {
        document.addEventListener( 'DOMContentLoaded', function () {
            wdm_ga4_ecommerce_purchase_event_init();
        });
    }

    function wdm_ga4_ecommerce_purchase_event_init() {
        try {
            window.dataLayer.push({
                event: 'purchase',
                ecommerce: {
                    currency: 'USD',
                    value: parseFloat(wdm_ga4_ecommerce_purchase_event_data[ 'value' ]),
                    transaction_id: wdm_ga4_ecommerce_purchase_event_data[ 'transaction_id' ],
                    coupon: wdm_ga4_ecommerce_purchase_event_data[ 'coupon' ],
                    items: wdm_ga4_ecommerce_purchase_event_data[ 'items' ],
                    // items: [{
                    //     item_id: 'id',
                    //     item_name: 'item_name',
                    //     discount: 0,
                    //     index: 0,
                    //     item_brand: 'Wisdmlabs',
                    //     price: 'price',
                    //     quantity: 1,
                    // }]
                }
            });
        }  catch( error ) {
            console.log( "The download has not been configured for GA4." );
        }
    }
})();
