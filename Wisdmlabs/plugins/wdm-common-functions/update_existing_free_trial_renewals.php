<?php
class WDM_CLI {

    /**
     * Returns 'Hello World'
     *
     * @since  0.0.1
     * @author Scott Anderson
     */
    public function hello_world() {
        WP_CLI::line( 'Hello World!' );
    }

    public function update_existing_free_trial_renewals() {
        $args = array( 
                    'post_type'=>'edd_payment',
                    'post_status'=>'edd_subscription',
                    'post_parent__not_in'=> array(0),
                    'posts_per_page' => -1
                );
        $the_query = new WP_Query( $args );
        // echo '<pre>';
        // print_r($the_query->posts);
        // echo '</pre>';
        
        if ( $the_query->have_posts() ) :
            foreach ( $the_query->posts as $post ):
            // while ( $the_query->have_posts() ) :
                // the_post();
                $payment_id = $post->ID;
                $payment      = new EDD_Payment( $payment_id );
                $payment_exists = $payment->ID;
                // echo '<pre>';
                // print_r($payment_id);
                // echo '</pre>,';
                if ( empty( $payment_exists ) ) {
                    continue;
                }
                $cart_items     = $payment->cart_details;

                // echo '<pre>';
                // print_r($cart_items);
                // echo '</pre>';
                unset($payment);
                if ( empty( $cart_items ) ) {
                    continue;
                }
                $cntr = 0;
                foreach ( $cart_items as $key => $cart_item ) {
                    $item_id    = isset( $cart_item['id']    )                                  ? $cart_item['id']                                 : $cart_item;
                    $price_id   = isset( $cart_item['item_number']['options']['price_id'] )     ? $cart_item['item_number']['options']['price_id'] : null;
                    $download   = new EDD_Download( $item_id );
                    $initial_payment = wp_get_post_parent_id( $payment_id );
                    // echo $initial_payment. ' ';
                    if ( isset( $price_id ) && edd_recurring()->has_free_trial( $item_id, $price_id ) && $initial_payment ) {
                         update_post_meta( $payment_id,'free_trial_renewal',$initial_payment );
                         update_post_meta( $payment_id,'free_trial_renewal_download',$item_id );
                        $cntr++;
                        // WP_CLI::line( 'Payment exists is: ' . $payment_exists . ' and Parent is: ' . $initial_payment );
                    }
                }
                WP_CLI::line( 'Total: ' . $cntr );
            endforeach;
        endif;
        WP_CLI::line( 'Done.' );
    }

}

/**
 * Registers our command when cli get's initialized.
 *
 * @since  1.0.0
 * @author Scott Anderson
 */
function wdm_cli_register_commands() {
    WP_CLI::add_command( 'wdm', 'WDM_CLI' );
}

add_action( 'cli_init', 'wdm_cli_register_commands' );
