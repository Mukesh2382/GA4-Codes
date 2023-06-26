<?php
/**
 * File which contains InformRobotsBlock class which will inform admins
 * if the robots are being blocked
 */
namespace WdmSiteAlert;

require_once 'library/PHPMailer/PHPMailer.php';
require_once 'library/PHPMailer/SMTP.php';
use PHPMailer\PHPMailer;

/**
* Class to inform admins about the wp ses email status
*/
if (! class_exists('InformRobotsBlock')) {
    class InformRobotsBlock
    {
        // To store current class object
        private static $_instance;
        
        /**
         * To add expensive codes and to prevent direct object instantiation
         */
        private function __construct()
        {
            add_action('admin_init', array( $this, 'blogPublicNotice' ), 15);
        }

        /**
         * It will check if robots is being blocked or not
         */
        public function blogPublicNotice()
        {
            if (! ('1' === (string) get_option('blog_public'))) {
                // Inform admins about the Robots Blocking issue
                if (!get_transient('informed_about_robots_blocked')) {
                    $this->informAdmins();
                }
            }
        }

        /**
         * To get instance of the current class
         */
        public static function getInstance()
        {
            if (!isset(self::$_instance)) {
                self::$_instance = new InformRobotsBlock;
            }
            return self::$_instance;
        }

        /**
         * Method which will send emails to admin
         */
        public function informAdmins()
        {
            $body = "Yoast Alert, robots are blocked on your <a href='wisdmlabs.com'>site</a>";
            $site_url = get_site_url();
            $admin_emails = array(
                                    'Tariq Kotwal' => 'tariq.kotwal@wisdmlabs.com',
                                    'Arunesh Parab' => 'arunesh.parab@wisdmlabs.com'
                                );
            // Get site name in subject
            $find = array( 'http://', 'https://' );
            $replace = '';
            $subject = str_replace($find, $replace, $site_url) . ' Alert, Robots Blocked';

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

            if (strpos($site_url, 'https://wisdmlabs.com') !== false && !$mail->send()) {
                error_log("Email not sent. ", $mail->ErrorInfo, PHP_EOL);
            } else {
                set_transient('informed_about_robots_blocked', '1', 60*60*3);
            }
        }
    }
}
