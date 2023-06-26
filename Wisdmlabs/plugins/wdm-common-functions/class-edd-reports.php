<?php

/**
 * Edd Reports  
 * 
 * Api implemented to be used by google Apps script
 * 
 * @package   EdwiserCustomisations
 * @author    Swapnil Mahadik <swapnil.mahadik@wisdmlabs.com>
 * 
*/

class EddReports{
    private static $instance;
    public function __construct(){
        add_action('edd_pre_get_payments', [$this, 'edd_pre_get_payments']);
        add_action( 'edd_reports_tab_export_content_bottom',  [$this, 'add_upgraded_report_tab'] );
    }

    public function add_upgraded_report_tab(){
        ?>
        <div class="postbox edd-export-payment-history">
            <h3><span><?php _e('Export Upgraded Payment History','easy-digital-downloads' ); ?></span></h3>
            <div class="inside">
                <p><?php _e( 'Download a CSV of all payments recorded.', 'easy-digital-downloads' ); ?></p>

                <form id="edd-export-upgraded-payments" class="edd-export-form edd-import-export-form" method="post">
                    <?php echo EDD()->html->date_field( array( 'id' => 'edd-upgrade-payment-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'easy-digital-downloads' ) )); ?>
                    <?php echo EDD()->html->date_field( array( 'id' => 'edd-upgrade-payment-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'easy-digital-downloads' ) )); ?>
                    <select name="status" style='display:none'>
                        <option value="upgraded"><?php _e( 'upgraded', 'easy-digital-downloads' ); ?></option>
                    </select>
                    <?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>
                    <input type="hidden" name="edd-export-class" value="EDD_Batch_Payments_Export"/>
                    <span>
                        <input type="submit" value="<?php _e( 'Generate CSV', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
                        <span class="spinner"></span>
                    </span>
                </form>
            </div><!-- .inside -->
        </div><!-- .postbox -->
        <?php
    }

    public function edd_pre_get_payments($edd_payment_query){
        if(isset($edd_payment_query->args['post_status'])){
            if($edd_payment_query->args['post_status'] == 'upgraded'){
                global $wpdb;
                parse_str($_POST['form'], $inputs);
                $start_date = $inputs['start'];
                $end_date = $inputs['end'];
                $where = "WHERE `post_type` = 'edd_payment' ";
                if(!empty($start_date) ){
                    $start_date = date( 'Y-m-d 00:00:00', strtotime( $start_date ) );
                    $where .= " AND `post_date` >= '$start_date'";
                }
                if(!empty($end_date) ){
                    $end_date = date( 'Y-m-d 23:59:59', strtotime( $end_date ) );
                    $where .= " AND `post_date` <= '$end_date'";
                }
                $sql = "SELECT ID FROM `wp_posts`  $where";
                $payment_ids = $wpdb->get_col($sql,0);
                $payment_ids_str = implode(",",$payment_ids);
               
                unset($edd_payment_query->args['date_query']);
                $edd_payment_query->args['post_status'] = 'any';
                if(!isset($edd_payment_query->args['meta_query'])){
                    $edd_payment_query->args['meta_query'] = [];
                }
                $edd_payment_query->args['meta_query'][] =  array(
                    'key'     => '_edd_sl_upgraded_payment_id',
                    'value'   => $payment_ids,
		            'compare' => 'IN',
                    'type'      => 'NUMERIC',
                );
            }
        }
    }

    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new EddReports;
        }
        return self::$instance;
    }
}

EddReports::getInstance();
