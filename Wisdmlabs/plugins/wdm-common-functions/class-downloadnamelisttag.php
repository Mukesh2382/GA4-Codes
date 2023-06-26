<?php
/**
 * File that deals with eleumine email tag
 *
 * Long description for file (if any)...
 *
 */

if (! defined('ABSPATH')) {
    exit;
}

class DownloadNameListTag
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init_hooks();
    }


    /**
     * Function to check if download is a elumin or Elumine Bundle.
     *
     * @param  Int $payment_id Payemnt ID.
     * @return String $message Message to be sent to customer.
     */
    public function edd_download_name_list($payment_id)
    {
        $payment = new EDD_Payment($payment_id);

        $payment_data  = $payment->get_meta();
        $download_list = '<ul>';
        $cart_items    = $payment->cart_details;
        $email         = $payment->email;

        if ($cart_items) {
            $show_names = apply_filters('edd_email_show_names', true);
            $show_links = apply_filters('edd_email_show_links', true);

            foreach ($cart_items as $item) {
                $price_id = edd_get_cart_item_price_id($item);
                if ($show_names && edd_is_bundled_product($item['id'])) {
                    $title = '<strong>' . get_the_title($item['id']) . ':-</strong>';
                    $download_list .= '<li>' . apply_filters('edd_email_receipt_download_title', $title, $item, $price_id, $payment_id) . '<br/>';
                } elseif ($show_names) {
                    $title = '<strong>' . get_the_title($item['id']) . '</strong>';
                    $download_list .= '<li>' . apply_filters('edd_email_receipt_download_title', $title, $item, $price_id, $payment_id) . '<br/>';
                }

                $files = edd_get_download_files($item['id'], $price_id);

                if (! empty($files)) {
                    // foreach ( $files as $filekey => $file ) {
                    //  $my_file = $file;
                    //  if ( strpos( $my_file, '_' ) > -1 ) {
                    //              $new_file = substr( $my_file, 0, strpos( $my_file, '_' ) );

                    //  } else {
                    //      $new_file = $my_file;
                    //  }
                    //      $download_list .= '<div>';
                    //      $download_list .= edd_get_file_name( $file );
                    //      $download_list .= '-<u>' . get_post_field( 'post_name', get_post( $my_file ) ) . '</u>';
                    //      $download_list .= '</div>';
                    // }
                } elseif (edd_is_bundled_product($item['id'])) {
                    $bundled_products = apply_filters('edd_email_tag_bundled_products', edd_get_bundled_products($item['id'], $price_id), $item, $payment_id, 'download_list');

                    foreach ($bundled_products as $bundle_item) {
                        $my_bundle_item = $bundle_item;
                        if (strpos($my_bundle_item, '_') > -1) {
                            $new_bundle_item = substr($my_bundle_item, 0, strpos($my_bundle_item, '_'));
                        } else {
                            $new_bundle_item = $my_bundle_item;
                        }
                        $download_list .= '<div class="edd_bundled_product">' . get_the_title($bundle_item) . '</div>';
                    }
                } else {
                    $no_downloads_message = apply_filters('edd_receipt_no_files_found_text', __('No downloadable files found.', 'easy-digital-downloads'), $item['id']);
                    $no_downloads_message = apply_filters('edd_email_receipt_no_downloads_message', $no_downloads_message, $item['id'], $price_id, $payment_id);

                    if (! empty($no_downloads_message)) {
                        $download_list .= '<div>';
                        $download_list .= $no_downloads_message;
                        $download_list .= '</div>';
                    }
                }

                if ($show_names) {
                    $download_list .= '</li>';
                }
            }
        }
        $download_list .= '</ul>';

        return $download_list;
    }

    /**
     * Add edd_email_tag_elumine_body to email template tag array.
     *
     * @param  array $email_tags email template tag array.
     * @return array $email_tags email template tag array.
     */
    public function wdm_edd_download_name_list_template_tag($email_tags)
    {

        $email_tags = array_merge(
            $email_tags,
            array(
                array(
                    'tag'         => 'download_name_list',
                    'description' => __('A list of tthe names of each download purchased.', 'edd'),
                    'function'    => array( $this, 'edd_download_name_list' ),
                ),
            )
        );

        return $email_tags;
    }

    /**
     * Function to modify email preview
     *
     * @param  String $message Message to be sent to customer.
     * @return String $message Message to be sent to customer.
     */
    public function wdm_edd_download_name_list_email_preview($message)
    {

        $message = str_replace(
            '{download_name_list}',
            'Download Name List',
            $message
        );
        return $message;
    }
    /**
     * Hooks for the functions.
     */
    public function init_hooks()
    {

        add_filter('edd_email_tags', array( $this, 'wdm_edd_download_name_list_template_tag' ));
        add_filter('edd_email_preview_template_tags', array( $this, 'wdm_edd_download_name_list_email_preview' ), 10, 1);
    }
}

new DownloadNameListTag();
