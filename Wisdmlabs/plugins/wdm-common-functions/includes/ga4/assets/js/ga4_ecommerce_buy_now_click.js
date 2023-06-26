if (document.readyState !== 'loading') {
    wdm_ga4_ecommerce_init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        wdm_ga4_ecommerce_init();
    });
}

function wdm_ga4_ecommerce_init() {
    let wdm_is_ga4_captured  = false;
    let buy_now_forms        = document.querySelectorAll(".wdm-cta-buy-now-button");
    let buy_now_link_buttons = document.querySelectorAll(".wdm-buy-now-link-button a");

    buy_now_forms.forEach( function( buy_now_form ) {
        buy_now_form.addEventListener( "submit", function( e ){
            if ( true === wdm_is_ga4_captured ) {
                return;
            }

            e.preventDefault();

            let download_id = this.querySelector('input[name="download_id"]').value;
            let price_id = this.querySelector('input[name="edd_options[price_id][]"]').value;

            wdm_ga4_datalayer_push_buy_now_event( download_id, price_id );
            wdm_is_ga4_captured = true;
            this.submit();
        });
    });

    buy_now_link_buttons.forEach( function( buy_now_link_button ) {
        buy_now_link_button.addEventListener("click", function( e ){
            if ( true === wdm_is_ga4_captured ) {
                return;
            }

            e.preventDefault();

            let urlString   = this.href;
            let paramString = decodeURI( urlString.split("?")[1] );
            let params_arr  = paramString.split("&");
            let download_id = "";
            let price_id    = "";

            for (let i = 0; i < params_arr.length; i++) {
                let pair = params_arr[i].split("=");
                if ( "download_id" == pair[0] ) {
                    download_id = pair[1];
                }
                if ( "edd_options[price_id]" == pair[0] ) {
                    price_id = pair[1];
                }
            }

            wdm_ga4_datalayer_push_buy_now_event( download_id, price_id );
            wdm_is_ga4_captured = true;
            this.click();
        });
    });

    function wdm_ga4_datalayer_push_buy_now_event( download_id, price_id ) {
        try {
            let price = '-1';

            if ( "undefined" != typeof wdm_ga4_downloads_data[ download_id ][ "variable_prices" ]
            ) {
                price = wdm_ga4_downloads_data[ download_id ][ "variable_prices" ][ price_id ][ "amount" ];
            } else if ( "undefined" != typeof wdm_ga4_downloads_data[ download_id ][ "single_price_amount" ] ) {
                price = wdm_ga4_downloads_data[ download_id ][ "single_price_amount" ];
            }

            dataLayer.push({ ecommerce: null }); // Clear the previous ecommerce object.
            dataLayer.push({
                event: "begin_checkout",
                ecommerce: {
                    currency: "USD",
                    items: [
                    {
                        item_id: download_id,
                        item_name: wdm_ga4_downloads_data[ download_id ][ "item_name" ],
                        discount: 0.00,
                        index: 0,
                        item_brand: "WisdmLabs",
                        item_category: wdm_ga4_downloads_data[ download_id ][ "item_category" ],
                        price: parseFloat( price ),
                        quantity: 1
                    }
                    ]
                }
            });
            return 1;
        } catch( error ) {
            console.log( "The download has not been configured for GA4." );
            return -1;
        }
    }
}
