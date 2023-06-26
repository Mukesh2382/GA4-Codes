<?php
namespace WDMCommonFunctions;

/**
 * DowngradeIR to show text and link to download older version of IR
 */
class ReceiptShortcodes
{
    private static $instance;
    public $upgrade_cost;
    /**
     * __construct includes hook calls on ajax processes to process upgrade requests
     *
     * @return void
     */
    private function __construct()
    {
        $this->upgrade_cost = '';
        add_action( 'template_redirect', array( $this, 'edd_leap_ir_receipt_shortcode_scripts' ), 999);
        add_shortcode( 'edd_leap_receipt', array( $this, 'edd_leap_receipt_shortcode' ) );
        add_shortcode( 'edd_ir_receipt', array( $this, 'edd_ir_receipt_shortcode' ));
        add_shortcode( 'wdm_leap_prod_details_on_thankyou', array( $this, 'wdm_leap_prod_details_on_thankyou_callback' ));
        add_shortcode( 'wdm_leap_prod_details_on_thankyou_footer', array( $this, 'wdm_leap_prod_details_on_thankyou_footer_callback' ) );
        add_shortcode( 'edd_csp_receipt', array( $this, 'edd_csp_receipt_shortcode' ) );
        add_shortcode( 'wdm_csp_receipt_upgrade_to_sbp', array( $this, 'wdm_csp_receipt_upgrade_to_sbp' ) );
        add_shortcode( 'wdm_upgrade_to_sbp_amount', array( $this, 'wdm_upgrade_to_sbp_amount' ) );
        add_shortcode( 'elumine_leap_upgrade_percent_off', array( $this, 'elumine_leap_upgrade_percent_off' ) );
        add_shortcode( 'upgrade_leap_to_elumine_leap', array( $this, 'upgrade_leap_to_elumine_leap' ) );
    }

    public function edd_leap_ir_receipt_shortcode_scripts()
    {
        global $post;
        if (edd_is_success_page()) {
            if (has_shortcode($post->post_content, 'edd_leap_receipt')) {
                wp_enqueue_script('edd-leap-receipt-js', get_stylesheet_directory_uri() . '/js/edd-leap-receipt.js', array( 'jquery' ));
                wp_enqueue_script('clipboard-min', get_stylesheet_directory_uri() . '/js/clipboard.min.js', array( 'jquery' ));
                wp_enqueue_style('edd-leap-receipt-style', get_stylesheet_directory_uri() . '/css/edd-leap-receipt-style.css');
            }
            if(has_shortcode($post->post_content, 'edd_ir_receipt')||has_shortcode($post->post_content, 'edd_csp_receipt')){
                wp_enqueue_script('clipboard-min', get_stylesheet_directory_uri() . '/js/clipboard.min.js', array( 'jquery' ));
                wp_enqueue_script('edd-ir-receipt-js', get_stylesheet_directory_uri() . '/js/edd-ir-receipt.js', array( 'jquery' ));
            }
        }
    }

    /******** ********* Thank You Page (If LEAP is bought) *********/

    /**
     * Receipt Shortcode
     *
     * Shows an order receipt.
     *
     * @since 1.4
     * @param array  $atts Shortcode attributes
     * @param string $content
     * @return string
     */
    public function edd_leap_receipt_shortcode($atts, $content = null)
    {
        global $edd_receipt_args;

        $edd_receipt_args = shortcode_atts(
            array(
                'error'          => __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads'),
                'price'          => true,
                'discount'       => true,
                'products'       => true,
                'date'           => true,
                'notes'          => true,
                'payment_key'    => false,
                'payment_method' => true,
                'payment_id'     => true,
            ),
            $atts,
            'edd_receipt'
        );

        if(empty($edd_receipt_args['id'])){
            $session = edd_get_purchase_session();
            if (isset($_GET['payment_key'])) {
                $payment_key = urldecode($_GET['payment_key']);
            } elseif ($session) {
                $payment_key = $session['purchase_key'];
            } elseif ($edd_receipt_args['payment_key']) {
                $payment_key = $edd_receipt_args['payment_key'];
            }
    
            // No key found
            if (! isset($payment_key)) {
                return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
            }
            $edd_receipt_args['id'] = edd_get_purchase_id_by_key($payment_key);
        }

        $user_can_view = edd_can_view_receipt($payment_key);

        // Key was provided, but user is logged out. Offer them the ability to login and view the receipt
        if (! $user_can_view && ! empty($payment_key) && ! is_user_logged_in() && ! edd_is_guest_payment($edd_receipt_args['id'])) {
            global $edd_login_redirect;
            $edd_login_redirect = edd_get_current_page_url();

            ob_start();

            echo '<p class="edd-alert edd-alert-warn">' . __('You must be logged in to view this payment receipt.', 'easy-digital-downloads') . '</p>';
            // edd_get_template_part( 'shortcode', 'login' );
                edd_get_template_part('shortcode', 'leap_receipt');

            $login_form = ob_get_clean();

            return $login_form;
        }

        $user_can_view = apply_filters('edd_user_can_view_receipt', $user_can_view, $edd_receipt_args);

        // If this was a guest checkout and the purchase session is empty, output a relevant error message
        if (empty($session) && ! is_user_logged_in() && ! $user_can_view) {
            return '<p class="edd-alert edd-alert-error">' . apply_filters('edd_receipt_guest_error_message', __('Receipt could not be retrieved, your purchase session has expired.', 'easy-digital-downloads')) . '</p>';
        }

        /*
        * Check if the user has permission to view the receipt
        *
        * If user is logged in, user ID is compared to user ID of ID stored in payment meta
        *
        * Or if user is logged out and purchase was made as a guest, the purchase session is checked for
        *
        * Or if user is logged in and the user can view sensitive shop data
        *
        */

        if (! $user_can_view) {
            return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
        }

        ob_start();

        edd_get_template_part('shortcode', 'leap_receipt');

        $display = ob_get_clean();
        return $display;
    }
    /*****************End of Thank You Page (If LEAP is bought)*********************/
    
    /******** ********* Thank You Page (If IR is bought) *********/

    /**
     * Receipt Shortcode
     *
     * Shows an order receipt.
     *
     * @since 1.4
     * @param array  $atts Shortcode attributes
     * @param string $content
     * @return string
     */
    public function edd_ir_receipt_shortcode($atts, $content = null)
    {
        global $edd_receipt_args;

        $edd_receipt_args = shortcode_atts(
            array(
                'error'          => __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads'),
                'price'          => true,
                'discount'       => true,
                'products'       => true,
                'date'           => true,
                'notes'          => true,
                'payment_key'    => false,
                'payment_method' => true,
                'payment_id'     => true,
            ),
            $atts,
            'edd_receipt'
        );

        if(empty($edd_receipt_args['id'])){
            $session = edd_get_purchase_session();
            if (isset($_GET['payment_key'])) {
                $payment_key = urldecode($_GET['payment_key']);
            } elseif ($session) {
                $payment_key = $session['purchase_key'];
            } elseif ($edd_receipt_args['payment_key']) {
                $payment_key = $edd_receipt_args['payment_key'];
            }

            // No key found
            if (! isset($payment_key)) {
                return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
            }

            $edd_receipt_args['id']    = edd_get_purchase_id_by_key($payment_key);
        }
        $user_can_view = edd_can_view_receipt($payment_key);

        // Key was provided, but user is logged out. Offer them the ability to login and view the receipt
        if (! $user_can_view && ! empty($payment_key) && ! is_user_logged_in() && ! edd_is_guest_payment($edd_receipt_args['id'])) {
            global $edd_login_redirect;
            $edd_login_redirect = edd_get_current_page_url();

            ob_start();

            echo '<p class="edd-alert edd-alert-warn">' . __('You must be logged in to view this payment receipt.', 'easy-digital-downloads') . '</p>';
            // edd_get_template_part( 'shortcode', 'login' );
                edd_get_template_part('shortcode', 'leap_receipt');

            $login_form = ob_get_clean();

            return $login_form;
        }

        $user_can_view = apply_filters('edd_user_can_view_receipt', $user_can_view, $edd_receipt_args);

        // If this was a guest checkout and the purchase session is empty, output a relevant error message
        if (empty($session) && ! is_user_logged_in() && ! $user_can_view) {
            return '<p class="edd-alert edd-alert-error">' . apply_filters('edd_receipt_guest_error_message', __('Receipt could not be retrieved, your purchase session has expired.', 'easy-digital-downloads')) . '</p>';
        }

        /*
        * Check if the user has permission to view the receipt
        *
        * If user is logged in, user ID is compared to user ID of ID stored in payment meta
        *
        * Or if user is logged out and purchase was made as a guest, the purchase session is checked for
        *
        * Or if user is logged in and the user can view sensitive shop data
        *
        */

        if (! $user_can_view) {
            return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
        }

        ob_start();

        edd_get_template_part('shortcode', 'ir_receipt');

        $display = ob_get_clean();
        return $display;
    }

    /**
     * wdm_leap_prod_details_on_thankyou_callback a callback function for the shortcode wdm_leap_prod_details 
     * which shows LEAP product details on the purchase confirmation page
     *
     * @return void
     */
    function wdm_leap_prod_details_on_thankyou_callback(){

        global $edd_receipt_args;

        $payment = get_post($edd_receipt_args['id']);
        if (empty($payment) || !$this->wdm_is_payment_session_set()){
            return '<p class="edd-alert edd-alert-error">' . __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads') . '</p>';
        }
        
        $cart   = edd_get_payment_meta_cart_details($payment->ID, true);
        $leap_prod_details_sale = '$0';
        if($cart){
            // Get license limit of the selected cart item and yearly/lifetime data
            // Get all single items data from the IR Leap bundle
            // Get the price for each variation for the license limit fetch from the step 1
        
            $license_limit_selected = $cart_item_id = 0;
            // Default value for price
            $price = '-';

            // Set title, description for each product
            $leap_prod_details[20277] = array(
                'title' => 'WISDM Instructor Role for LearnDash',
                'desc' => 'LearnDash Instructor Role',
                'price' => $price
            );
        
            $leap_prod_details[44670] = array(
                'title' => 'WISDM Group Registration for LearnDash',
                'desc' => 'Create student groups and assign Group Leaders to manage them',
                'price' => $price
            );
        
            $leap_prod_details[109665] = array(
                'title' => 'WISDM Ratings, Reviews, and Feedback',
                'desc' => 'Let learners rate your courses and collect reviews and feedback to add testimonials to your site',
                'price' => $price
            );
        
            $leap_prod_details[14995] = array(
                'title' => 'WISDM Quiz Reporting Extension',
                'desc' => 'Create detailed student reports for assignments, quizzes, and everything else under the sun',
                'price' => $price
            );
        
            $leap_prod_details[34202] = array(
                'title' => 'WISDM Content Cloner',
                'desc' => 'Duplicate the structure and content of your courses in a snap',
                'price' => '-'
            );
            
            // Check if the cart item has a subscription set
            $wdm_is_edd_subscription = $this->wdm_is_edd_subscription($payment);
            
            // To get cart item's license limit
            $license_limit_selected = $this->wdm_cart_item_license_limit($cart);
            
            // To update product description of the current cart product
            foreach ($cart as $key => $item) {
                $cart_item_id = $item['id'];
            }
            if($cart_item_id){
                foreach ($leap_prod_details as $key => $value) {
                    if($key==$cart_item_id){
                        $leap_prod_details[$key]['desc'] = 'You got this already!';
                    }
                }
            }

            // To get total and sale price of the related bundle product
            $ir_leap = '366236';
            $bund_download = new \EDD_Download($ir_leap);
            $bund_download_prices = $bund_download->get_prices();
            foreach ($bund_download_prices as $key => $bund_download_price_single_var) {
                // Multiplying the license limit by 2 because bundle has only business license and IR has single licenses
                if($bund_download_price_single_var['license_limit']==($license_limit_selected*2)){
                    if(($bund_download_price_single_var['recurring']=='yes' && $wdm_is_edd_subscription) ||
                    ($bund_download_price_single_var['recurring']=='no' && !$wdm_is_edd_subscription)){
                        $leap_prod_details_sale = '$'.intval($bund_download_price_single_var['amount']);
                        $leap_prod_details_total = '$'.intval($bund_download_price_single_var['old_amount']);
                    }
                }
            }
          
            // to get individual item's price
            $bundled_products = edd_get_bundled_products($ir_leap);
            $added = array();
            foreach ($bundled_products as $key => $bundled_product_single) {
                $item_single_id = explode('_',$bundled_product_single)[0];
                $b_download = new \EDD_Download($item_single_id);
                $b_item_prices = $b_download->get_prices();
            
                foreach ($b_item_prices as $key => $b_item) {
                    if($b_item['license_limit']==$license_limit_selected){
                        if(!in_array($item_single_id,$added) && ($wdm_is_edd_subscription && $b_item['recurring']=='yes') || 
                                !$wdm_is_edd_subscription && $b_item['recurring']=='no'){
                            $leap_prod_details[$item_single_id]['price'] = '$'. intval($b_item['amount']);
                            array_push($added,$item_single_id);
                        }
                    }
                }
            }
        }else{
            return '<p class="edd-alert edd-alert-error">' . __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads') . '</p>';
        }
        
        ob_start();
        ?>
        <h3 class="product-detail-table-caption"><?php echo apply_filters('edd_payment_receipt_products_title', __('What’s in LEAP?', 'easy-digital-downloads')); ?></h3>
        <table class="edd-leap-det-table">
            <thead>
                <tr>
                    <th class="product-detail-header"><?php _e('Product Name', 'easy-digital-downloads'); ?></th>
                    <th class="product-detail-header"><?php _e('Price', 'easy-digital-downloads'); ?></th>      
                </tr>
            </thead>
            <tbody>
                <?php foreach($leap_prod_details as $leap_detail){?>
                    <tr>
                        <td><?php echo $leap_detail['title']?><span class="prod_desc"><?php echo $leap_detail['desc']?></span></td><td><?php echo $leap_detail['price']?></td>
                    </tr>
                <?php }?>
            </tbody>
            <tfoot>
                <tr>
                    <td><span class="total-cost-label">Total Cost</span></td><td><?php echo '<span class="edd-total-cost">'.$leap_prod_details_total.'</span><span class="edd-sale-cost">'.$leap_prod_details_sale.'</span>'?></td>
                </tr>
            </tfoot>
        </table>
        <?php
        $display = ob_get_clean();
        return $display;
    }

    public function wdm_leap_prod_details_on_thankyou_footer_callback(){
        global $edd_receipt_args;
        // Set title string by checking recurring or not
        // Get upgrade id by comparing cart item's license limit and subscription check
        // Get upgrade cost, sale amount and calculate you save cost
    
        $payment = get_post($edd_receipt_args['id']);
        $download = '366236';
        $price_id = 0;
    
        if (empty($payment) || !$this->wdm_is_payment_session_set()){
            return '<p class="edd-alert edd-alert-error">' . __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads') . '</p>';
        }
    
        $wdm_is_edd_subscription = $this->wdm_is_edd_subscription($payment);
        if($wdm_is_edd_subscription){
            $title_text = 'Annual';
        }else{
            $title_text = 'Lifetime';
        }
    
        // To get total and sale price of the related bundle product
        $cart   = edd_get_payment_meta_cart_details($payment->ID, true);
        $leap_prod_details_sale = '$0';
        if($cart){
            $license_limit_selected = $this->wdm_cart_item_license_limit($cart);
            if( !in_array($license_limit_selected,array(2)) ){
                $license_limit_selected = $license_limit_selected*2;
            }
            $ir_leap = '366236';
            list($price_id,$leap_prod_details_sale) = $this->get_matching_price_id($ir_leap,$license_limit_selected,$wdm_is_edd_subscription);
            $license = $this->get_purchased_item_license($cart,$payment);
        }else{
            return '<p class="edd-alert edd-alert-error">' . __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads') . '</p>';
        }
        
        $price = '<span class="edd-total-cost-f">{{sale_price}}</span>&nbsp;&nbsp;&nbsp;<span class="edd-sale-cost-f">{{upgrade_price}}</span>';
        $save = ' You Save: {{you_save}} ({{you_save_percent}}) ';
        
        $view_details = 'https://wisdmlabs.com/upgrade-to-leap/';

        if($license && isset($price_id) && $price_id>=0){
            $ava_upgrades = edd_sl_get_license_upgrades( $license->ID );
            $upgrade_id = $this->get_upgrade_id($ava_upgrades,$download,$price_id);
            if($upgrade_id){
                $cost = edd_sl_get_license_upgrade_cost( $license->ID, $upgrade_id );
               
                $this->upgrade_cost = '$' . $cost;
                $price = str_replace('{{sale_price}}','$'.$leap_prod_details_sale,$price);
                $price = str_replace('{{upgrade_price}}','$'.intval($cost),$price);
                $save = str_replace('{{you_save}}','$'.($leap_prod_details_sale-intval($cost)),$save);
                $save_p = (($leap_prod_details_sale-intval($cost))/$leap_prod_details_sale)*100;
                $save = str_replace('{{you_save_percent}}',round($save_p, -1).'%',$save);
                
                $upgrade_link = site_url().'checkout/?edd_action=sl_license_upgrade&license_id='.$license->ID.'&upgrade_id='.$upgrade_id;
                // https://wisdmlabs.com/checkout/?edd_action=sl_license_upgrade&license_id=36543&upgrade_id=1
                $cta = '<a class="leap-footer-view-det" href="'.$view_details.'">View Details</a> <a class="leap-footer-upgrade-link" href="'.$upgrade_link.'">Get It Now</a>';
                ob_start();
                ?>
                    <p class="edd-leap-offer-title"><?php echo 'LEAP '.$title_text.' License'?></p>
                    <p class="edd-leap-offer-price"><?php echo $price?></p>
                    <p class="edd-leap-offer-save"><?php echo $save?></p>
                    <p class="edd-leap-offer-ctas"><?php echo $cta?></p>
                <?php
                $display = ob_get_clean();
            }
        }else{
            $display = '';    
        }
        return $display;
    }
    
    public function wdm_is_edd_subscription($payment){
        $args    = array(
            'parent_payment_id' => $payment->ID,
            'order'             => 'ASC',
        );
        $db      = new \EDD_Subscriptions_DB();
        $subscriptions = $db->get_subscriptions($args);
        unset($db);
        if($subscriptions){
            return 1;
        }
        return 0;
    }
    
    public function wdm_is_payment_session_set(){
        $session = edd_get_purchase_session();
        if (isset($_GET['payment_key'])) {
            $payment_key = urldecode($_GET['payment_key']);
        } elseif ($session) {
            $payment_key = $session['purchase_key'];
        }
    
        // No key found
        if (! isset($payment_key)) {
            return 0;
        }
        return 1;
    }
    
    /******** ********* Ends Thank You Page (If IR is bought) *********/

    /**
     * Receipt Shortcode
     *
     * Shows an order receipt.
     *
     * @since 1.4
     * @param array  $atts Shortcode attributes
     * @param string $content
     * @return string
     */
    public function edd_csp_receipt_shortcode($atts, $content = null)
    {
        global $edd_receipt_args;

        $edd_receipt_args = shortcode_atts(
            array(
                'error'          => __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads'),
                'price'          => true,
                'discount'       => true,
                'products'       => true,
                'date'           => true,
                'notes'          => true,
                'payment_key'    => false,
                'payment_method' => true,
                'payment_id'     => true,
            ),
            $atts,
            'edd_receipt'
        );

        if(empty($edd_receipt_args['id'])){
            $session = edd_get_purchase_session();
            if (isset($_GET['payment_key'])) {
                $payment_key = urldecode($_GET['payment_key']);
            } elseif ($session) {
                $payment_key = $session['purchase_key'];
            } elseif ($edd_receipt_args['payment_key']) {
                $payment_key = $edd_receipt_args['payment_key'];
            }
    
            // No key found
            if (! isset($payment_key)) {
                return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
            }
            $edd_receipt_args['id']    = edd_get_purchase_id_by_key($payment_key);
        }

        $user_can_view = edd_can_view_receipt($payment_key);

        // Key was provided, but user is logged out. Offer them the ability to login and view the receipt
        if (! $user_can_view && ! empty($payment_key) && ! is_user_logged_in() && ! edd_is_guest_payment($edd_receipt_args['id'])) {
            global $edd_login_redirect;
            $edd_login_redirect = edd_get_current_page_url();

            ob_start();

            echo '<p class="edd-alert edd-alert-warn">' . __('You must be logged in to view this payment receipt.', 'easy-digital-downloads') . '</p>';
            // edd_get_template_part( 'shortcode', 'login' );
                edd_get_template_part('shortcode', 'leap_receipt');

            $login_form = ob_get_clean();

            return $login_form;
        }

        $user_can_view = apply_filters('edd_user_can_view_receipt', $user_can_view, $edd_receipt_args);

        // If this was a guest checkout and the purchase session is empty, output a relevant error message
        if (empty($session) && ! is_user_logged_in() && ! $user_can_view) {
            return '<p class="edd-alert edd-alert-error">' . apply_filters('edd_receipt_guest_error_message', __('Receipt could not be retrieved, your purchase session has expired.', 'easy-digital-downloads')) . '</p>';
        }

        /*
        * Check if the user has permission to view the receipt
        *
        * If user is logged in, user ID is compared to user ID of ID stored in payment meta
        *
        * Or if user is logged out and purchase was made as a guest, the purchase session is checked for
        *
        * Or if user is logged in and the user can view sensitive shop data
        *
        */

        if (! $user_can_view) {
            return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
        }

        ob_start();

        edd_get_template_part('shortcode', 'csp_receipt');

        $display = ob_get_clean();
        return $display;
    }

    public function wdm_csp_receipt_upgrade_to_sbp($atts, $content = null){
        global $edd_receipt_args;

        $edd_receipt_args = shortcode_atts(
            array(
                'error'          => __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads'),
                'price'          => true,
                'discount'       => true,
                'products'       => true,
                'date'           => true,
                'notes'          => true,
                'payment_key'    => false,
                'payment_method' => true,
                'payment_id'     => true,
            ),
            $atts,
            'edd_receipt'
        );
        // Set title string by checking recurring or not
        // Get upgrade id by comparing cart item's license limit and subscription check
        // Get upgrade cost, sale amount and calculate you save cost
        if(empty($edd_receipt_args['id'])){
            $session = edd_get_purchase_session();
            if ( isset( $_GET['payment_key'] ) ) {
                $payment_key = urldecode( $_GET['payment_key'] );
            } else if ( $session ) {
                $payment_key = $session['purchase_key'];
            } elseif ( $edd_receipt_args['payment_key'] ) {
                $payment_key = $edd_receipt_args['payment_key'];
            }
            // No key found
            if (! isset($payment_key)) {
                return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
            }
            $edd_receipt_args['id']    = edd_get_purchase_id_by_key( $payment_key );
        }
        
        // $payment = get_post($edd_receipt_args['id']);
        $payment = edd_get_payment($edd_receipt_args['id']);
        
        $download = '432909';
       
        if (empty($payment) || !$this->wdm_is_payment_session_set()){
            return '<p class="edd-alert edd-alert-error">' . __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads') . '</p>';
        }
    
        $wdm_is_edd_subscription = $this->wdm_is_edd_subscription($payment);
        
        // To get total and sale price of the related bundle product
        $cart   = edd_get_payment_meta_cart_details($edd_receipt_args['id'], true);
        $leap_prod_details_sale = '$0';
        // $cart   = $payment->get_cart_details();
        if($cart){
            $license_limit_selected = $this->wdm_cart_item_license_limit($cart);
            
            if( !in_array($license_limit_selected,array(2)) ){
                $license_limit_selected = $license_limit_selected*2;
            }
            $csp_sbp = '432909';
            list($price_id,$leap_prod_details_sale) = $this->get_matching_price_id($csp_sbp,$license_limit_selected,$wdm_is_edd_subscription);
            $license = $this->get_purchased_item_license($cart,$payment);
        }else{
            return '<p class="edd-alert edd-alert-error">' . __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads') . '</p>';
        }
        
        $price = '<span class="edd-sale-cost-f">{{upgrade_price}}</span>';
        
        if( $license && isset($price_id) && $price_id>=0 ){
            $ava_upgrades = edd_sl_get_license_upgrades( $license->ID );
            $upgrade_id = $this->get_upgrade_id($ava_upgrades,$download,$price_id);
            if(isset($upgrade_id)){
                $cost = edd_sl_get_license_upgrade_cost( $license->ID, $upgrade_id );
                $this->upgrade_cost = '$' . $cost;
                $price = str_replace('{{upgrade_price}}','$'.intval($cost),$price);
                $upgrade_link = site_url().'checkout/?edd_action=sl_license_upgrade&license_id='.$license->ID.'&upgrade_id='.$upgrade_id;
                $cta = '<a id="sbp-upgrade-link" class="button" href="'.$upgrade_link.'">Buy Sales Booster Pack Just for '.$price.'</a>';
                ob_start();
                echo $cta;
                $display = ob_get_clean();
            }
        }else{
            $display = '';
        }
        return $display;
    }

    public function wdm_cart_item_license_limit($cart){
        if($cart){
            foreach ($cart as $key => $item) {
                $cart_item_id = $item['id'];
                $cart_download = new \EDD_Download($item['id']);
                $cart_item_prices = $cart_download->get_prices();
                foreach ($cart_item_prices as $cart_item_price_id => $cart_item_var) {
                    if($cart_item_price_id==$item['item_number']['options']['price_id']){
                        return $cart_item_var['license_limit'];
                    }
                }
            }
        }
        return 0;
    }

    public function get_matching_price_id($upgrade_to_download,$license_limit_selected,$wdm_is_edd_subscription){
        $bund_download = new \EDD_Download($upgrade_to_download);
        $bund_download_prices = $bund_download->get_prices();
        $price_id = '';
        $leap_prod_details_sale = '$0';
        foreach ($bund_download_prices as $key => $bund_download_price_single_var) {
            // Multiplying the license limit by 2 because bundle has only business license and IR has single licenses
            if($bund_download_price_single_var['license_limit']==($license_limit_selected)){
                if(($bund_download_price_single_var['recurring']=='yes' && $wdm_is_edd_subscription) ||
                ($bund_download_price_single_var['recurring']=='no' && !$wdm_is_edd_subscription)){
                    $leap_prod_details_sale = intval($bund_download_price_single_var['amount']);
                    $price_id = $key;
                    // $leap_prod_details_total = '$'.$bund_download_price_single_var['old_amount'];
                }
            }
        }
        return array($price_id,$leap_prod_details_sale);
    }

    public function get_purchased_item_license($cart,$payment){
        $licensing = edd_software_licensing();
        $license = '';
        foreach ($cart as $key => $item) {// 1st for
            if (! apply_filters('edd_user_can_view_receipt_item', true, $item)) :
                continue; // Skip this item if can't view it
            endif;
            $license = $licensing->get_license_by_purchase($payment->ID, $item['id'], $key);
        }
        return $license;
    }

    public function get_purchased_item_license_bundle($item_id,$payment){
        $licensing = edd_software_licensing();
        $license = $licensing->get_license_by_purchase($payment->ID, $item_id);
        return $license;
    }

    public function wdm_upgrade_to_sbp_amount($atts, $content=null){
        $wdm_upgrade_to_sbp_amount = shortcode_atts(
            array(
                'upgrade_to'          => 0
            ),
            $atts
        );
        $cost = '';
        if($atts['upgrade_to']){
            if(!empty($this->upgrade_cost)){
                return $this->upgrade_cost;
            }else{
                global $edd_receipt_args;
                // Set title string by checking recurring or not
                // Get upgrade id by comparing cart item's license limit and subscription check
                // Get upgrade cost, sale amount and calculate you save cost
                
                if(empty($edd_receipt_args['id'])){
                    $session = edd_get_purchase_session();
                    if ( isset( $_GET['payment_key'] ) ) {
                        $payment_key = urldecode( $_GET['payment_key'] );
                    } else if ( $session ) {
                        $payment_key = $session['purchase_key'];
                    } elseif ( $edd_receipt_args['payment_key'] ) {
                        $payment_key = $edd_receipt_args['payment_key'];
                    }
                    // No key found
                    if (! isset($payment_key)) {
                        return '$0';
                    }
                    $edd_receipt_args['id']    = edd_get_purchase_id_by_key( $payment_key );
                }
                // $payment = get_post($edd_receipt_args['id']);
                $payment = edd_get_payment($edd_receipt_args['id']);
                $download = $atts['upgrade_to'];
                $price_id = 0;
            
                if (empty($payment) || !$this->wdm_is_payment_session_set()){
                    return '$0';
                }
            
                $wdm_is_edd_subscription = $this->wdm_is_edd_subscription($payment);
                // To get total and sale price of the related bundle product
                $cart   = edd_get_payment_meta_cart_details($payment->ID, true);
                $leap_prod_details_sale = '$0';
                if($cart){
                    $license_limit_selected = $this->wdm_cart_item_license_limit($cart);
                    if( !in_array($license_limit_selected,array(2)) ){
                        $license_limit_selected = $license_limit_selected*2;
                    }
                    list($price_id,$leap_prod_details_sale) = $this->get_matching_price_id($atts['upgrade_to'],$license_limit_selected,$wdm_is_edd_subscription);
                    $license = $this->get_purchased_item_license($cart,$payment);
                    
                    if( $license && isset($price_id) && $price_id>=0 ){
                        $ava_upgrades = edd_sl_get_license_upgrades( $license->ID );
                        $upgrade_id = $this->get_upgrade_id($ava_upgrades,$download,$price_id);
                        if(isset($upgrade_id)){
                            $cost = edd_sl_get_license_upgrade_cost( $license->ID, $upgrade_id );
                            $this->upgrade_cost = '$'.$cost;
                            $cost = '$'.$cost;
                        }
                    }   
                }
            }
        }
        ob_start();
        echo $cost;
        return ob_get_clean();
    }

    public function elumine_leap_upgrade_percent_off($atts){
        global $edd_receipt_args;
        $session = edd_get_purchase_session();
        if ( isset( $_GET['payment_key'] ) ) {
            $payment_key = urldecode( $_GET['payment_key'] );
        } else if ( $session ) {
            $payment_key = $session['purchase_key'];
        } elseif ( $edd_receipt_args['payment_key'] ) {
            $payment_key = $edd_receipt_args['payment_key'];
        }

        // No key found
        if ( ! isset( $payment_key ) ) {
            return '0%';
        }
        $payment_id    = edd_get_purchase_id_by_key( $payment_key );
        $payment = get_post($payment_id);

        $user_can_view = edd_can_view_receipt( $payment_key );
        if ( ! $user_can_view ){
            return '0%';
        }
        if ( ! $user_can_view && ! empty( $payment_key ) && ! is_user_logged_in() && ! edd_is_guest_payment( $payment_id ) ) {
            return '0%';
        }
        
        // elumine - leap id
        $download = 366221;
        $leap_ids = array(368743,368744,368742,368714,366236,479075);

        $cart   = edd_get_payment_meta_cart_details($payment_id, true);
        $leap_prod_details_sale = '$0';
        if($cart){
            $wdm_is_edd_subscription = $this->wdm_is_edd_subscription($payment);
            foreach ($cart as $key => $item) {
                if(in_array($item['id'],$leap_ids)){
                    $cart_item_id = $item['id'];
                }
            }
            
            if(in_array($cart_item_id,$leap_ids)){
                $license_limit_selected = $this->wdm_cart_item_license_limit($cart);
                $elumine_id = 127679;
                $license_limit_elumine = intval($license_limit_selected/2);
                $elumine_download = new \EDD_Download($elumine_id);
                $elumine_item_prices = $elumine_download->get_prices();

                foreach ($elumine_item_prices as $elumine_item_key => $elumine_item_value) {
                    if($elumine_item_value['license_limit']==$license_limit_elumine && $wdm_is_edd_subscription && !empty($elumine_item_value['recurring'])){
                        $elumin_item_price = intval($elumine_item_value['amount']);
                    }elseif($elumine_item_value['license_limit']==$license_limit_elumine && !$wdm_is_edd_subscription && !empty($elumine_item_value['is_lifetime'])){
                        $elumin_item_price = intval($elumine_item_value['amount']);
                    }
                }
                list($price_id,$leap_prod_details_sale) = $this->get_matching_price_id($download,$license_limit_selected,$wdm_is_edd_subscription);
                $license = $this->get_purchased_item_license_bundle($cart_item_id,$payment);
                if($license && isset($price_id) && $price_id>=0){
                    $ava_upgrades = edd_sl_get_license_upgrades( $license->ID );
                    $upgrade_id = $this->get_upgrade_id($ava_upgrades,$download,$price_id);
                    if($upgrade_id){
                        $cost = edd_sl_get_license_upgrade_cost( $license->ID, $upgrade_id );
                        $save_p = (($elumin_item_price-intval($cost))/$elumin_item_price)*100;
                        $save_p = round($save_p).'%';
                        ob_start();
                        echo $save_p;
                        $display = ob_get_clean();
                        return $display;
                    }
                }
            }
        }
        return '';
    }

    public function upgrade_leap_to_elumine_leap($atts, $content = null){
        global $edd_receipt_args;

        $edd_receipt_args = shortcode_atts(
            array(
                'error'          => __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads'),
                'price'          => true,
                'discount'       => true,
                'products'       => true,
                'date'           => true,
                'notes'          => true,
                'payment_key'    => false,
                'payment_method' => true,
                'payment_id'     => true,
            ),
            $atts,
            'edd_receipt'
        );
        // Set title string by checking recurring or not
        // Get upgrade id by comparing cart item's license limit and subscription check
        // Get upgrade cost, sale amount and calculate you save cost
        if(empty($edd_receipt_args['id'])){
            $session = edd_get_purchase_session();
            if ( isset( $_GET['payment_key'] ) ) {
                $payment_key = urldecode( $_GET['payment_key'] );
            } else if ( $session ) {
                $payment_key = $session['purchase_key'];
            } elseif ( $edd_receipt_args['payment_key'] ) {
                $payment_key = $edd_receipt_args['payment_key'];
            }
            // No key found
            if (! isset($payment_key)) {
                return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
            }
            $edd_receipt_args['id']    = edd_get_purchase_id_by_key( $payment_key );
        }
        
        $payment = get_post($edd_receipt_args['id']);

        $wdm_is_edd_subscription = $this->wdm_is_edd_subscription($payment);
        if($wdm_is_edd_subscription){
            $title_text = 'annual';
        }else{
            $title_text = 'lifetime';
        }
        
        $download = '366221';
       
        if (empty($payment) || !$this->wdm_is_payment_session_set()){
            return '<p class="edd-alert edd-alert-error">' . __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads') . '</p>';
        }
    
        // $wdm_is_edd_subscription = $this->wdm_is_edd_subscription($payment);
        
        // To get total and sale price of the related bundle product
        $cart   = edd_get_payment_meta_cart_details($edd_receipt_args['id'], true);
        $elumin_item_price = '$0';
        // $cart   = $payment->get_cart_details();
        if($cart){
            $license_limit_selected = $this->wdm_cart_item_license_limit($cart);
            
            if( !in_array($license_limit_selected,array(2)) ){
                $license_limit_selected = $license_limit_selected*2;
            }
            $elumine_id = 127679;
            $license_limit_elumine = intval($license_limit_selected/2);

            $elumine_download = new \EDD_Download($elumine_id);
            $elumine_item_prices = $elumine_download->get_prices();

            foreach ($elumine_item_prices as $elumine_item_key => $elumine_item_value) {
                if($elumine_item_value['license_limit']==$license_limit_elumine && $wdm_is_edd_subscription && !empty($elumine_item_value['recurring'])){
                    $elumin_item_price = '$' . intval($elumine_item_value['amount']);
                }elseif($elumine_item_value['license_limit']==$license_limit_elumine && !$wdm_is_edd_subscription && !empty($elumine_item_value['is_lifetime'])){
                    $elumin_item_price = '$' . intval($elumine_item_value['amount']);
                }
            }
            
            $leap_ids = array(368743,368744,368742,368714,366236,479075);

            $cart   = edd_get_payment_meta_cart_details($payment->ID, true);
            $leap_prod_details_sale = '$0';
            foreach ($cart as $key => $item) {
                if(in_array($item['id'],$leap_ids)){
                    $cart_item_id = $item['id'];
                }
            }
            if(in_array($cart_item_id,$leap_ids)){
                list($price_id,$leap_prod_details_sale) = $this->get_matching_price_id($cart_item_id,$license_limit_selected,$wdm_is_edd_subscription);
                $license = $this->get_purchased_item_license_bundle($cart_item_id,$payment);
            }
        }else{
            return '<p class="edd-alert edd-alert-error">' . __('Sorry, trouble retrieving payment receipt.', 'easy-digital-downloads') . '</p>';
        }
          
        if( $license && isset($price_id) && $price_id>=0 ){
            $ava_upgrades = edd_sl_get_license_upgrades( $license->ID );
            $upgrade_id = $this->get_upgrade_id($ava_upgrades,$download,$price_id);
            if(isset($upgrade_id)){
                $cost = edd_sl_get_license_upgrade_cost( $license->ID, $upgrade_id );
                $price =  '<s>'.$elumin_item_price.'</s>'. ' $'.intval($cost);
                $upgrade_link = site_url().'checkout/?edd_action=sl_license_upgrade&license_id='.$license->ID.'&upgrade_id='.$upgrade_id;
                $cta = '<a id="sbp-upgrade-link" class="button" href="'.$upgrade_link.'">Get eLumine '.$title_text.' license at '.$price.'</a>';
                ob_start();
                echo $cta;
                $display = ob_get_clean();
            }
        }else{
            $display = '';
        }
        return $display;
    }    

    public function get_upgrade_id($ava_upgrades,$download,$price_id){
        $upgrade_id = '';
        foreach ($ava_upgrades as $key => $ava_upgrade) {
            if($price_id==$ava_upgrade['price_id'] && $download==$ava_upgrade['download_id']){
                $upgrade_id = $key;
            }
        }
        return $upgrade_id;
    }
    
    /**
     * getInstance to get object of the current class
     *
     * @return void
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ReceiptShortcodes;
        }
        return self::$instance;
    }
}
