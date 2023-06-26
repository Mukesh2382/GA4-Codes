<?php
/**
 * File which contains InformWpSesStatus class which will inform admins
 * if the WP Ses emails are disabled
 */
namespace WdmSiteAlert;

require_once 'library/PHPMailer/PHPMailer.php';
require_once 'library/PHPMailer/SMTP.php';
use PHPMailer\PHPMailer;

/**
* Class to inform admins about the wp ses email status
*/
if (! class_exists('InformWpSesStatus')) {
    class InformWpSesStatus
    {
        // To store current class object
        private static $instance;
        // private $api_endpoint;
        // private $key;

        // To add expensive codes and to prevent direct object instantiation
        private function __construct()
        {
            add_action('update_option', array($this,'wdmSesOptionCheck'), 10, 3);
        }

        public static function getInstance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new InformWpSesStatus;
            }
            return self::$instance;
        }

        public function wdmSesOptionCheck($name, $old, $new)
        {
            if (isset($old) && $name=='wpses_options' && isset($new['active']) && $new['active'] == 0) {
                $current_user = wp_get_current_user();
                $body = "Someone has changed the WP Ses status to disable";
                if ($current_user->exists()) {
                    $body = "User with ID $current_user->ID and email $current_user->user_email changed the WP Ses status to disable";
                }
                $admin_emails = array(
                                    'Tariq Kotwal'      =>  'tariq.kotwal@wisdmlabs.com',
                                    'Arunesh Parab'     =>  'arunesh.parab@wisdmlabs.com'
                                );
                $subject = 'WP Ses is in inactive state';
                $mail = new PHPMailer\PHPMailer;

                // Tell PHPMailer to use SMTP
                $mail->isSMTP();

                // Replace sender@example.com with your "From" address.
                // This address must be verified with Amazon SES.
                $mail->setFrom('support@wisdmlabs.com', 'Wisdmlabs');

                // Replace recipient@example.com with a "To" address. If your account
                // is still in the sandbox, this address must be verified.
                // Also note that you can include several addAddress() lines to send
                // email to multiple recipients.
                foreach ($admin_emails as $key => $value) {
                    # code...
                    $mail->addAddress($value, $key);
                }

                // Replace smtp_username with your Amazon SES SMTP user name.
                $mail->Username = 'AKIA2MXCLD4N4AM53NNL';

                // Replace smtp_password with your Amazon SES SMTP password.
                $mail->Password = 'BDJ3pwEDMBjhf1IsoSCFef/V4bNBMQCcavAjnkHhRudu';
                $mail->Host = 'email-smtp.us-west-2.amazonaws.com';

                // The subject line of the email
                $mail->Subject = $subject;

                // The HTML-formatted body of the email
                $mail->Body = $body;

                // Tells PHPMailer to use SMTP authentication
                $mail->SMTPAuth = true;

                // Enable TLS encryption over port 587
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Tells PHPMailer to send HTML-formatted email
                $mail->isHTML(true);

                // The alternative email body; this is only displayed when a recipient
                // opens the email in a non-HTML email client. The \r\n represents a
                // line break.
                $mail->AltBody = $body;

                $site_url = get_site_url();

                if (strpos($site_url, 'https://wisdmlabs.com') !== false && !$mail->send()) {
                    error_log("Email not sent. ", $mail->ErrorInfo, PHP_EOL);
                }
            }
        }
    }
}
