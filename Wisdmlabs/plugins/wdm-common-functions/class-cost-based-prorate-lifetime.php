<?php
namespace WDMCommonFunctions;

/**
* Class to handle cost based proration for upgrade to lifetime licenses
*/

class CostBasedProrateLifetime
{
    // To store current class object
    private static $instance;
    
    // To add expensive codes and to prevent direct object instantiation
    private function __construct()
    {
        add_filter( 'edd_sl_license_upgrade_cost', array($this,'wdm_edd_sl_license_upgrade_cost'), 10, 3 );
    }

    public function wdm_edd_sl_license_upgrade_cost ($cost, $license_id, $upgrade_id){
        $download_id = edd_software_licensing()->get_download_id( $license_id );
        $download    = new \EDD_SL_Download( $download_id );
        $upgrades    = edd_sl_get_upgrade_paths( $download_id );

        if( $download->has_variable_prices() ) {

            $price_id = edd_software_licensing()->get_price_id( $license_id );

            if ( false !== $price_id && '' !== $price_id ) {

                $prices    = $download->get_prices();

                /**
                 * Allow using the previously paid amount as the $old_price
                 *
                 * Some store owners would prefer that the old price be based off what was previously paid, instead of what
                 * the current price ID value is. Returning false here, allows the $old_price to be based on the last amount paid
                 * instead of the current price of the Price ID, in the event it has been changed.
                 *
                 * @since 3.6.4
                 *
                 * @param bool             Should we use the current price of the Price ID for prorated estimates.
                 * @param int  $license_id The License ID requesting the prorated cost.
                 * @param int  $download_id The Download ID associated with the license.
                 */
                $use_current_price = apply_filters( 'edd_sl_use_current_price_proration', true, $license_id, $download_id );
                if ( array_key_exists( $price_id, $prices ) && $use_current_price ) {

                    // The old price ID still exists, use the current price of it as the old price.
                    $old_price = edd_get_price_option_amount( $download_id, $price_id );

                } else {

                    // The old price ID was removed, so just figure out what they paid last.
                    $license         = edd_software_licensing()->get_license( $license_id );
                    $last_payment_id = max( $license->payment_ids );
                    $payment         = edd_get_payment( $last_payment_id );

                    $old_price = 0.00;
                    foreach ( $payment->cart_details as $item ) {
                        if ( (int) $item['id'] !== $download->ID ) {
                            continue;
                        }

                        $old_price = $item['item_price'];
                        break;
                    }

                }

            } else {

                $old_price = edd_get_lowest_price_option( $download_id );

            }

        } else {

            $old_price = edd_get_download_price( $download_id );

        }


        if ( isset( $upgrades[ $upgrade_id ][ 'price_id' ] ) && false !== $upgrades[ $upgrade_id ][ 'price_id' ] ) {

            $new_price = edd_get_price_option_amount( $upgrades[ $upgrade_id ][ 'download_id' ], $upgrades[ $upgrade_id ][ 'price_id' ] );

        } else {

            $new_price = edd_get_download_price( $upgrades[ $upgrade_id ][ 'download_id' ] );

        }

        $cost = $new_price;

        if ( ! empty( $upgrades[ $upgrade_id ][ 'pro_rated' ] ) ) {
            // echo '<pre>';
            // print_r($upgrades[ $upgrade_id ]);
            // echo '</pre>';
            $upgraded_download = new \EDD_SL_Download( $upgrades[ $upgrade_id ]['download_id'] );

			if ( $upgraded_download->has_variable_prices() ) {
				$download_is_lifetime = $upgraded_download->is_price_lifetime( $upgrades[ $upgrade_id ]['price_id'] );
			} else {
				$download_is_lifetime = $upgraded_download->is_lifetime();
			}
            // If not upgrade to lifetime
            if($download_is_lifetime){
                $cost = $this->wdm_edd_sl_get_pro_rated_upgrade_cost( $license_id, $old_price, $new_price );
            }else{
                $cost = edd_sl_get_pro_rated_upgrade_cost( $license_id, $old_price, $new_price );
            }

        }


        if ( isset( $upgrades[ $upgrade_id ][ 'discount' ] ) ) {

            $cost -= $upgrades[ $upgrade_id ][ 'discount' ];

        }

        if ( $cost < 0 ) {
            $cost = 0;
        }
        return $cost;
    }       

    public function wdm_edd_sl_get_pro_rated_upgrade_cost( $license_id = 0, $old_price, $new_price ) {
        $proration_method = 'cost-based';
        // $proration_method = apply_filters( 'edd_sl_proration_method', $proration_method, $license_id, $old_price, $new_price );
    
        // Check global setting and handle accordingly, if the filter is used
        // to fall back to simple pro-ration, return the simple new - old price
        if ( $proration_method == 'cost-based' || apply_filters( 'edd_sl_license_upgrade_pro_rate_simple', false ) ) {
            $prorated = edd_sl_get_cost_based_pro_rated_upgrade_cost( $license_id, $old_price, $new_price );
        } /*else {
            $prorated = edd_sl_get_time_based_pro_rated_upgrade_cost( $license_id, $old_price, $new_price );
        }*/
    
        return apply_filters( 'edd_sl_get_pro_rated_upgrade_cost', $prorated, $license_id, $old_price, $new_price );
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CostBasedProrateLifetime();
        }
        return self::$instance;
    }
}

// CostBasedProrateLifetime::getInstance();
