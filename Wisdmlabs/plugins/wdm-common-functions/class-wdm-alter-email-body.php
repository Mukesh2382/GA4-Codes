<?php
/**
 * File that deals with email template tag
 */

if (! defined('ABSPATH')) {
    exit;
}


/**
 * Alters Emails templates
 */
class Wdm_Alter_Email_body
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Hooks for the functions.
     */
    public function init_hooks()
    {
        add_filter('edd_email_tags', array( $this, 'wdm_edd_email_template_tags' ));
    }

    /**
     * Function to add email template tags
     * @param  array $email_tags Default email tags array
     * @return array $email_tags
     */
    public function wdm_edd_email_template_tags($email_tags)
    {
        $email_tags = array_merge(
            $email_tags,
            array(
                array(
                    'tag'         => 'wdm_other_solutions',
                    'description' => __('Tag for adding other solutions content with services or elumine link in email.', 'easy-digital-downloads'),
                    'function'    => array( $this, 'edd_email_tag_elumine_body' ), // This callback function returns the body of the tag created
                ),
                array(
                    'tag'         => 'wdm_consultaion_call',
                    'description' => __('Tag for showing consultation call details with link in email.', 'easy-digital-downloads'),
                    'function'    => array( $this, 'edd_email_tag_consultaion_call_body' ), // This callback function returns the body of the tag created
                ),
            )
        );

        return $email_tags;
    }

    /**
     * Function to return email message for wdm_consultaion_call tag(all products) Excludes individual LDCC.
     *
     * @param  Int $payment_id Payemnt ID.
     * @return String $message Message to be sent to customer.
     */
    public function edd_email_tag_consultaion_call_body($payment_id)
    {
        $payment    = new EDD_Payment($payment_id);
        $cart_items = $payment->cart_details;

        if (isset($cart_items[0]['id']) && count($cart_items) == 1 && ( $cart_items[0]['id']==34202 || $cart_items[0]['id']==399006 )) {
            /*LDCC and PCM Excluded*/
            $message = '';
        } else {
            $message = '<div style="background-color: #9c959517;border-radius: 2%;padding: 30px;margin-top: 15px;"><p><strong>Need advice? Consult our experts on 1-on-1 call.</strong><br/><br/>Our product expert will guide you to provide you with an efficient solution for your challenges. <a href="https://calendly.com/wisdmlabs-expert/consultation--part-1">Book your slot here</a> to share your business requirements.</p></div>';
        }

        return $message;
    }

    /**
     * Function to return email message for elumine_body tag(elumin or Elumine Bundle).
     *
     * @param  Int $payment_id Payemnt ID.
     * @return String $message Message to be sent to customer.
     */
    public function edd_email_tag_elumine_body($payment_id)
    {
        if ($this->is_elumine_bundle($payment_id)) {
            $message = '<p><strong>Our Other Solutions:</strong><br/><br/>We also provide other services such as LearnDash Setup, LearnDash Customization and LearnDash Development. <a href="https://wisdmlabs.com/learndash-services-consultation/?utm_source=elumine&utm-medium=auto-responder&utm_campaign=ldc">Get in touch</a> with our Business Development Executives to know how to get the best out of your LearnDash System.</p>';
        } else {
            $message = '<p><strong>Our Other Solutions:</strong><br/><br/>We also provide other WordPress Services such as <strong>Plugin / Theme Development &amp; Customization</strong>, <strong>API Programming</strong>, <strong>Payment Gateway Integration</strong>. View our <a href="https://wisdmlabs.com/services/?utm_source=product-purchase&amp;utm_medium=mail&amp;utm_campaign=services_upsell">Services</a> for more details.</p>';
        }

        return $message;
    }

    /**
     * Function to check if download is a elumin or Elumine Bundle.
     *
     * @param  Int  $payment_id Payemnt ID.
     * @return bool $flag.
     */
    public function is_elumine_bundle($payment_id)
    {
        $flag       = false;
        $payment    = new EDD_Payment($payment_id);
        $cart_items = $payment->cart_details;

        $is_elumine = false;

        $is_elumine_bundle = false;
        foreach ($cart_items as $cart_item) {
            if (isset($cart_item['id'])) {
                $cart_item_id = (int) $cart_item['id'];

                switch ($cart_item_id) {
                    case 377970:
                    case 366218:
                    case 366221:
                    case 162691:
                    case 162694:
                    case 162696:
                    case 127679: //elumine
                        $is_elumine = true;

                        if ($is_elumine) {
                            $product_type = get_post_meta($cart_item_id, '_edd_product_type', true);
                            if ('bundle' === $product_type) {
                                $is_elumine_bundle = true;
                            }
                        }
                        break;

                    default:
                        $bundle_list  = get_post_meta($cart_item_id, '_edd_bundled_products', true);
                        $product_type = get_post_meta($cart_item_id, '_edd_product_type', true);
                        if ('bundle' === $product_type) {
                            $new_bundle = array();
                            foreach ($bundle_list as $key => $value) {
                                if (strpos($value, '_') > -1) {
                                    $new_bundle_item = substr($value, 0, strpos($value, '_'));
                                    array_push($new_bundle, $new_bundle_item);
                                } else {
                                    array_push($new_bundle, $value);
                                }
                            }

                            $new_unique_bundle = array_unique($new_bundle);

                            foreach ($new_unique_bundle as $value) {
                                if ('download' === get_post_type($value)) {
                                    $value = (int) $value;

                                    switch ($value) {
                                        case 377970:
                                        case 366218:
                                        case 366221:
                                        case 162691:
                                        case 162694:
                                        case 162696:
                                        case 127679:
                                            $is_elumine_bundle = true;

                                            break;
                                    }
                                }
                            }
                        }
                        break;
                }
            }

            if (true === $is_elumine || true === $is_elumine_bundle) {
                $flag = true;
            }
        }

        return $flag;
    }
}

new Wdm_Alter_Email_body();
