<?php

/**
 * Edd Reports API 
 * 
 * Api implemented to be used by google Apps script
 * 
 * @package   EdwiserCustomisations
 * @author    Swapnil Mahadik <swapnil.mahadik@wisdmlabs.com>
 * 
*/

class EddReportsApi{
    private static $instance;
    public function __construct(){
        add_action('rest_api_init', [$this, 'register_edd_reports']);
    }

    public function get_edd_payments($payments){
        $data = [];

        foreach ( $payments as $payment ) {
            $payment = new EDD_Payment( $payment->Id );
            $payment_meta   = $payment->payment_meta;
            $user_info      = $payment->user_info;
            $downloads      = $payment->cart_details;
            $total          = $payment->total;
            $user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 && $user_info['id'] != 0 ? $user_info['id'] : $user_info['email'];
            $products       = '';
            $products_raw   = '';
            $skus           = '';

            if ( $downloads ) {
                foreach ( $downloads as $key => $download ) {

                    // Download ID
                    $id  = isset( $payment_meta['cart_details'] ) ? $download['id'] : $download;
                    $qty = isset( $download['quantity'] ) ? $download['quantity'] : 1;

                    if ( isset( $download['price'] ) ) {
                        $price = $download['price'];
                    } else {
                        // If the download has variable prices, override the default price
                        $price_override = isset( $payment_meta['cart_details'] ) ? $download['price'] : null;
                        $price = edd_get_download_final_price( $id, $user_info, $price_override );
                    }

                    $download_tax      = isset( $download['tax'] ) ? $download['tax'] : 0;
                    $download_price_id = isset( $download['item_number']['options']['price_id'] ) ? absint( $download['item_number']['options']['price_id'] ) : false;

                    /* Set up verbose product column */

                    $products .= html_entity_decode( get_the_title( $id ) );

                    if ( $qty > 1 ) {
                        $products .= html_entity_decode( ' (' . $qty . ')' );
                    }

                    $products .= ' - ';

                    if ( edd_use_skus() ) {
                        $sku = edd_get_download_sku( $id );

                        if ( ! empty( $sku ) ) {
                            $skus .= $sku;
                        }
                    }

                    if ( isset( $downloads[ $key ]['item_number'] ) && isset( $downloads[ $key ]['item_number']['options'] ) ) {
                        $price_options = $downloads[ $key ]['item_number']['options'];

                        if ( isset( $price_options['price_id'] ) && ! is_null( $price_options['price_id'] ) ) {
                            $products .= html_entity_decode( edd_get_price_option_name( $id, $price_options['price_id'], $payment->ID ) ) . ' - ';
                        }
                    }

                    $products .= html_entity_decode( edd_currency_filter( edd_format_amount( $price ) ) );

                    if ( $key != ( count( $downloads ) -1 ) ) {

                        $products .= ' / ';

                        if( edd_use_skus() ) {
                            $skus .= ' / ';
                        }
                    }

                    /* Set up raw products column - Nothing but product names */
                    $products_raw .= html_entity_decode( get_the_title( $id ) ) . '|' . $price . '{' . $download_tax . '}';

                    // if we have a Price ID, include it.
                    if ( false !== $download_price_id ) {
                        $products_raw .= '{' . $download_price_id . '}';
                    }

                    if ( $key != ( count( $downloads ) -1 ) ) {

                        $products_raw .= ' / ';

                    }
                }
            }

            if ( is_numeric( $user_id ) ) {
                $user = get_userdata( $user_id );
            } else {
                $user = get_user_by( 'email', $user_id );
                if($user && is_numeric($user->ID)) {
                    $user = get_userdata( $user->ID );
                } else {
                    $user = false;
                }
            }

            // Get the customer's address

            $data[] = array(
                'id'           => $payment->ID,
                'seq_id'       => $payment->number,
                'email'        => $payment_meta['email'],
                'customer_id'  => $payment->customer_id,
                'first'        => $user_info['first_name'],
                'last'         => $user_info['last_name'],
                'address1'     => isset( $payment->address['line1'] )   ? $payment->address['line1']   : '',
                'address2'     => isset( $payment->address['line2'] )   ? $payment->address['line2']   : '',
                'city'         => isset( $payment->address['city'] )    ? $payment->address['city']    : '',
                'state'        => isset( $payment->address['state'] )   ? $payment->address['state']   : '',
                'country'      => isset( $payment->address['country'] ) ? $payment->address['country'] : '',
                'zip'          => isset( $payment->address['zip'] )     ? $payment->address['zip']     : '',
                'products'     => $products,
                'products_raw' => $products_raw,
                'skus'         => $skus,
                'amount'       => html_entity_decode( edd_format_amount( $total ) ), // The non-discounted item price
                'tax'          => html_entity_decode( edd_format_amount( edd_get_payment_tax( $payment->ID, $payment_meta ) ) ),
                'discount'     => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'easy-digital-downloads' ),
                'gateway'      => edd_get_gateway_admin_label( edd_get_payment_meta( $payment->ID, '_edd_payment_gateway', true ) ),
                'trans_id'     => $payment->transaction_id,
                'key'          => $payment_meta['key'],
                'date'         => $payment->date,
                'user'         => $user ? $user->display_name : __( 'guest', 'easy-digital-downloads' ),
                'currency'     => $payment->currency,
                'ip'           => $payment->ip,
                'mode'         => $payment->get_meta( '_edd_payment_mode', true ),
                'status'       => ( 'publish' === $payment->status ) ? 'complete' : $payment->status,
                'country_name' => isset( $payment->address['country'] ) ? edd_get_country_name( $payment->address['country'] ) : '',
            );
        }
        $data = apply_filters( 'edd_export_get_data', $data );
        $data = apply_filters( 'edd_export_get_data_payments' , $data );

        return $data;
    }

    public function register_edd_reports() {
        register_rest_route( 'edd_report', '/payment',array(
            'methods'  => 'GET',
            'callback' => array($this, 'edd_payment_report'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route( 'edd_report', '/payment_updates',array(
            'methods'  => 'GET',
            'callback' => array($this, 'edd_payment_updates_report'),
            'permission_callback' => '__return_true'
        ));
    }

    public function edd_payment_report(\WP_REST_Request $request){
        $start  = isset( $request['start'] )  ? sanitize_text_field( $request['start'] )  : '';
        $end    = isset( $request['end']  )   ? sanitize_text_field( $request['end']  )   : '';
        $status = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'any';
        $last_payment_id = isset( $request['last_payment_id'] ) ? sanitize_text_field( $request['last_payment_id'] ) : 0;
        // $args = array(
  //           'posts_per_page' => 50,
        //  'status'   => $status,
  //           'post_type'=> ['edd_payment'],
        //  'order'    => 'ASC',
        //  'orderby'  => 'date',
        // );
        if($last_payment_id){
            $args['last_payment_id'] = $last_payment_id;
        }
        // if( ! empty( $start ) || ! empty( $end ) ) {

        //  $args['date_query'] = array(
        //      array(
        //          'after'     => date( 'Y-n-d 00:00:00', strtotime( $start ) ),
        //          'before'    => date( 'Y-n-d 23:59:59', strtotime( $end ) ),
        //          'inclusive' => true
        //      )
        //  );
        // }
  //       $filter_handler = function( $where ) use ( $last_payment_id ) {
  //           global $wpdb;
  //           return $where . $wpdb->prepare( " AND {$wpdb->posts}.ID > %d", $last_payment_id );
  //       };
  //       add_filter( 'posts_where', $filter_handler );
  //       $query = new WP_Query( $args );
  //       $payments = $query->posts;
        // remove_filter( 'posts_where', $filter_handler );
        global $wpdb;
        $sql = "SELECT Id, total, status, date_created FROM {$wpdb->prefix}edd_orders WHERE Id > ".$last_payment_id;
        $status = $status == 'publish' || $status == 'complete' ? 'complete' : $status;
        $status = $status == 'edd_subscription' ? 'edd_subscription' : 'any';
        if($status != 'any') {
            $sql .= " AND status = '".$status."'";
        }
        $sql .= " Order By date_created ASC LIMIT 50";
        $results = $wpdb->get_results($sql);
        if( $results ) {
            $data = $this->get_edd_payments($results);
            return new \WP_REST_Response($data, 200);
        }
        return new \WP_REST_Response([], 200);
    }

    // Api to sync order date and status in google sheet
    public function edd_payment_updates_report(\WP_REST_Request $request){
        $status = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'any';
        $date_after_query = isset( $request['date_after_query'] ) ? sanitize_text_field( $request['date_after_query'] ) : false;
        global $wpdb;
        $sql = "SELECT Id, total, status, date_created FROM {$wpdb->prefix}edd_orders WHERE date_created > '".date( 'Y-m-d 00:00:00', strtotime( $date_after_query ) )."'";
        $status = $status == 'publish' || $status == 'complete' ? 'complete' : $status;
        $status = $status == 'edd_subscription' ? 'edd_subscription' : 'any';
        if($status != 'any') {
            $sql .= " AND status = '".$status."'";
        }
        $sql .= " Order By date_created ASC";
        // echo $sql; 
        $results = $wpdb->get_results($sql);
        // $args = array(
  //           'posts_per_page' => -1,
        //  'status'   => $status,
  //           'post_type'=> ['edd_payment'],
        //  'order'    => 'ASC',
        //  'orderby'  => 'date',
        // );
  //       if($date_after_query){
  //           $args['date_query'] = array(
        //      array(
        //          'after'     => date( 'Y-n-d 00:00:00', strtotime( $date_after_query ) ),
        //          'inclusive' => true
        //      )
        //  );
  //       }
  //       $query = new WP_Query( $args );
        // $payments = $query->posts;
        $result = [];
        foreach($results as $payment){
            $result[$payment->Id] = [
                'ID' => $payment->Id,
                'post_status' => $payment->status,
                'post_date' => $payment->date_created
            ];
        }
        return new \WP_REST_Response($result, 200);
    }

    public function csv_cols() {
        $cols = array(
            'id'           => __( 'Payment ID',   'easy-digital-downloads' ), // unaltered payment ID (use for querying)
            'seq_id'       => __( 'Payment Number',   'easy-digital-downloads' ), // sequential payment ID
            'email'        => __( 'Email', 'easy-digital-downloads' ),
            'customer_id'  => __( 'Customer ID', 'easy-digital-downloads' ),
            'first'        => __( 'First Name', 'easy-digital-downloads' ),
            'last'         => __( 'Last Name', 'easy-digital-downloads' ),
            'address1'     => __( 'Address', 'easy-digital-downloads' ),
            'address2'     => __( 'Address (Line 2)', 'easy-digital-downloads' ),
            'city'         => __( 'City', 'easy-digital-downloads' ),
            'state'        => __( 'State', 'easy-digital-downloads' ),
            'country'      => __( 'Country', 'easy-digital-downloads' ),
            'zip'          => __( 'Zip / Postal Code', 'easy-digital-downloads' ),
            'products'     => __( 'Products (Verbose)', 'easy-digital-downloads' ),
            'products_raw' => __( 'Products (Raw)', 'easy-digital-downloads' ),
            'skus'         => __( 'SKUs', 'easy-digital-downloads' ),
            'amount'       => __( 'Amount', 'easy-digital-downloads' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
            'tax'          => __( 'Tax', 'easy-digital-downloads' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
            'discount'     => __( 'Discount Code', 'easy-digital-downloads' ),
            'gateway'      => __( 'Payment Method', 'easy-digital-downloads' ),
            'trans_id'     => __( 'Transaction ID', 'easy-digital-downloads' ),
            'key'          => __( 'Purchase Key', 'easy-digital-downloads' ),
            'date'         => __( 'Date', 'easy-digital-downloads' ),
            'user'         => __( 'User', 'easy-digital-downloads' ),
            'currency'     => __( 'Currency', 'easy-digital-downloads' ),
            'ip'           => __( 'IP Address', 'easy-digital-downloads' ),
            'mode'         => __( 'Mode (Live|Test)', 'easy-digital-downloads' ),
            'status'       => __( 'Status', 'easy-digital-downloads' ),
            'country_name' => __( 'Country Name', 'easy-digital-downloads' ),
        );

        if( ! edd_use_skus() ){
            unset( $cols['skus'] );
        }
        if ( ! edd_get_option( 'enable_sequential' ) ) {
            unset( $cols['seq_id'] );
        }

        return $cols;
    }
    
    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new EddReportsApi;
        }
        return self::$instance;
    }
}

EddReportsApi::getInstance();
