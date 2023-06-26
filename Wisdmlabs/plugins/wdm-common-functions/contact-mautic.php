<?php
/**
 * Add Contact details from current Contact form to Mautic and also from edd checkout form to mautic.
 *
 * @package   Wdm_Shortcode_To_Mautic
 * @author    WisdmLabs <support@wisdmlabs.com>
 * @license   GPL-2.0+
 * @link      http://wisdmlabs.com
 * @copyright 2019 WisdmLabs
 */
if (! class_exists('Wdm_Contact_Form_To_Mautic')) {
    class Wdm_Contact_Form_To_Mautic
    {
        private $form_data;
        private $mautic_checkout_form;
        private $formUrl;
        public function __construct()
        {
            // Mautic setup url
            $this->formUrl =  'https://wdmmautic.wisdmlabs.com/form/submit?formId=';

            // Mautic form created for checkout
            $this->mautic_checkout_form = 18;

            // Contact form 7 with 92629 linked with Mautic's form with id 3
            // 92629 Contact form 7
            // Keys are mautic form field name and values are contact form 7 fields name
            $this->form_data['10'] = array(
                                    'yourname' => 'your-name',
                                    'youremail' => 'your-email',
                                    'skype' => 'skype',
                                    'phone' => 'your-phone',
                                    'yourbudget' => 'your-budget',
                                    'formId' => 'mformid'
                                    );
            // CF7 789
            $this->form_data['12'] = array(
                                    'yourname' => 'your-name',
                                    'youremail' => 'your-email',
                                    'skype' => 'skype',
                                    'yourphone' => 'your-phone',
                                    'yourbudget' => 'your-budget',
                                    'yoursubject' => 'your-subject',
                                    'yourmessage' => 'your-message',
                                    'formId' => 'mformid'
                                    );

            // CF7 3158
            $this->form_data['17'] = array(
                                    'yourname' => 'your-name',
                                    'youremail' => 'your-email',
                                    'text873' => 'text-873',
                                    'menu948' => 'menu-948',
                                    'yoursubject' => 'your-subject',
                                    'yourmessage' => 'your-message',
                                    'formId' => 'mformid'
                                    );

            // CF7 163222
            $this->form_data['14'] = array(
                                    'yourname' => 'your-name',
                                    'youremail' => 'your-email',
                                    'formId' => 'mformid'
                                    );

            // CF7 26291
            $this->form_data['15'] = array(
                                    'yourname' => 'your-name',
                                    'youremail' => 'your-email',
                                    'yoururl' => 'your-url',
                                    'youre_interested_in' => 'your-plan',
                                    'formId' => 'mformid'
                                    );

            // CF7 136911
            $this->form_data['13'] = array(
                                    'youremail' => 'your-email',
                                    'formId' => 'mformid'
                                    );

            // CF7 207145
            $this->form_data['16'] = array(
                                    'youremail' => 'your-email',
                                    'formId' => 'mformid'
                                    );

            // EDD Checkout form
            $this->form_data[$this->mautic_checkout_form] = array(
                                    'first_name'    => 'first_name',
                                    'last_name'     => 'last_name',
                                    'user_email'    => 'user_email',
                                    'product'       => 'products',
                                    'price'         => 'price',
                                    'formId'        => 'mformid'
                                    );

            // Single Blog Subscription Forms
            $this->form_data['11'] = array(
                                    'youremail'    => 'your-email',
                                    'categories'    => 'categories',
                                    'formId'        => 'mformid'
                                    );
        }
        /**
         * Add Contact details from current Contact from to Mautic form.
         *
         * @param object $contact_form Information and Post data from Current Contact Form 7
         */
        public function wdm_add_contact_form_to_mautic($contact_form)
        {
            $formid	 = filter_input(INPUT_POST, 'mformid');
            $mautic_data = $this->getMauticFormFields(array('formid'=>$formid), $contact_form);
            if ($mautic_data) {
                $ipaddress = getRemoteIpAddress();
                // Data to send
                $data = array('mauticform' => $mautic_data);
                // make curl request to insert the data
                makeCurl($this->formUrl.$formid, $data, $ipaddress);
            }
            return;
        }

        /**
        * Get contact form fields mapped with mautic form fields
        *
        * @param object $contact_form Information and Post data from Current Contact Form 7
        */
        public function getMauticFormFields($data=array(), $form_obj=null)
        {
            if (!empty($data['formid'])) {
                if (array_key_exists($data['formid'], $this->form_data)) {
                    // Is a sinlge blog article
                    foreach ($this->form_data[$data['formid']] as $field => $post) {
                        if ($field == 'categories') {
                            continue;
                        }
                        $data[$field] = filter_input(INPUT_POST, $post);
                    }
                    if ($data['formid'] == '11' && $form_obj) {
                        $data['categories'] = $this->getCategories($form_obj->id());
                    }
                    $data['return'] = 'https://wisdmlabs.com';
                    return $data;
                }
            }
            return;
        }

        public function wdmAddMauticCodeToBeforePayment($purchase_data, $valid_data)
        {
            $user_info['products'] = array();
            
            $user_info['user_email'] = !empty($valid_data['user']['user_email'])?$valid_data['user']['user_email']:'';
            $user_info['first_name'] = !empty($valid_data['user']['user_first'])?$valid_data['user']['user_first']:'';
            $user_info['last_name'] = !empty($valid_data['user']['user_last'])?$valid_data['user']['user_last']:'';
            
            foreach ($purchase_data['downloads'] as $key => $value) {
                $user_info['products'][] = get_the_title($value['id']);
            }

            // EDD Checkout form id
            $user_info['mformid'] = $this->mautic_checkout_form;

            // To get mautic form data
            $mautic_data = $this->getMauticFormFieldsForCheckout($user_info);
            if ($mautic_data) {
                $ipaddress = getRemoteIpAddress();

                // Data to send
                $data = array('mauticform' => $mautic_data);

                // make curl request to insert the data
                makeCurl($this->formUrl.$this->mautic_checkout_form, $data, $ipaddress);

                // Set transient to avoid form submission on page refresh
                // set_transient( 'm_submit_'.$payment->ID, 1, 30 * 24 * HOUR_IN_SECONDS );
            }
            return;
        }

        public function wdmEddCheckoutUserErrorChecks($post, $user_info, $valid_data)
        {
            $user_info_data = array(
                                    'user_email'    => !empty($user_info['email'])?$user_info['email']:'',
                                    'first_name'    => !empty($user_info['first_name'])?$user_info['first_name']:'',
                                    'last_name'     => !empty($user_info['last_name'])?$user_info['last_name']:''
                                );

            $user_info_data['products'] = array();
            
            $downloads = edd_get_cart_contents();
            foreach ($downloads as $key => $value) {
                $user_info_data['products'][] = get_the_title($value['id']);
            }

            // EDD checkout total price
            $user_info_data['price'] = edd_get_cart_total();

            // EDD Checkout form id
            $user_info_data['mformid'] = $this->mautic_checkout_form;

            // To get mautic form data
            $mautic_data = $this->getMauticFormFieldsForCheckout($user_info_data);
            if ($mautic_data) {
                $ipaddress = getRemoteIpAddress();

                // Data to send
                $data = array('mauticform' => $mautic_data);

                // make curl request to insert the data
                makeCurl($this->formUrl.$this->mautic_checkout_form, $data, $ipaddress);
            }
            return;
        }

        /**
        * Get checkout form fields mapped with mautic form fields
        *
        * @param array $data User Information from EDD Payment object
        */
        public function getMauticFormFieldsForCheckout($data=array())
        {
            $return_data = array();
            if (array_key_exists($this->mautic_checkout_form, $this->form_data)) {
                foreach ($this->form_data[$this->mautic_checkout_form] as $field => $post) {
                    $return_data[$field] = $data[$post];
                }
                return $return_data;
            }
            return;
        }

        /**
        * Method which will be called on EDD Receipt hook to start the mautic form submission process
        *
        */
        public function wdmAddMauticCodeToPaymentReceipt($payment, $edd_receipt_args)
        {
            if (edd_is_success_page()) {
                global $edd_receipt_args;
                $payment   = get_post($edd_receipt_args['id']);

                // Check if the form is submitted
                if (true /*!get_transient( 'm_submit_'.$payment->ID)*/) {
                    // Get the user info to create mautic form data
                    $user_info = edd_get_payment_meta_user_info($payment->ID, true);
                    // $user_info now contains
                    // 'id','email','first_name','last_name','discount','address'

                    // To get user email id
                    $wp_user_info = get_user_by('id', $user_info['id']);

                    // Unset unused data
                    unset($user_info['id']);
                    unset($user_info['discount']);
                    unset($user_info['address']);

                    // Customer email
                    $user_info['user_email'] = $wp_user_info->user_email;

                    // EDD Checkout form id
                    $user_info['mformid'] = $this->mautic_checkout_form;

                    // To get mautic form data
                    $mautic_data = $this->getMauticFormFieldsForCheckout($user_info);

                    if ($mautic_data) {
                        $ipaddress = getRemoteIpAddress();

                        // Data to send
                        $data = array('mauticform' => $mautic_data);

                        // make curl request to insert the data
                        makeCurl($this->formUrl.$this->mautic_checkout_form, $data, $ipaddress);

                        // Set transient to avoid form submission on page refresh
                        set_transient('m_submit_'.$payment->ID, 1, 30 * 24 * HOUR_IN_SECONDS);
                    }
                }
            }
            return;
        }

        /*
        *   Method to get menu filters / categories from the contact form 7 object
        */
        public function getCategories($contact_form_id)
        {
            $return = array();

            if ($contact_form_id) {
                $args = array(
                            'hide_empty' => false, // also retrieve terms which are not used yet
                            'meta_query' => array(
                                array(
                                   'key'       => 'wdm_cf7_linked',
                                   'value'     => $contact_form_id,
                                )
                            ),
                            'taxonomy'  => 'menu_filter',
                        );
                $terms = get_terms($args);
                foreach ($terms as $value) {
                    $return[] = $value->name;
                }
            }
            return $return;
        }
    }
}
