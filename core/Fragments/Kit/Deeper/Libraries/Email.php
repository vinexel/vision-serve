<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Deeper\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Vision\Modules\Config;

class Email
{
    protected $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        $this->mailer->isSMTP();
        $this->mailer->Host       = Config::get('SMTP', 'visioniconic.com');
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = Config::get('SMTP_USER', 'info@visioniconic.com');
        $this->mailer->Password   = Config::get('SMTP_PASS', '@Master337');
        $this->mailer->SMTPSecure = Config::get('SMTP_SECURE', 'ssl');
        $this->mailer->Port       = Config::get('SMTP_PORT', 465);

        $this->mailer->setFrom(Config::get('SMTP_FROM', 'info@visioniconic.com'), Config::get('SMTP_FROM_NAME', 'Flymetrade'));

        // For debugging (testing/development) mode
        // $this->mailer->SMTPDebug = 2;
        // $this->mailer->Debugoutput = 'error_log';
    }

    public function send($toEmail, $toName, $subject, $htmlBody, $plainText = '')
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $plainText ?: strip_tags($htmlBody);
            $this->mailer->send();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
