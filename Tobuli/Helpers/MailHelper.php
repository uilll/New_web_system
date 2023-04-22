<?php namespace Tobuli\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use PHPMailer;
use SendGrid\Attachment;
use Swift_MailTransport as MailTransport;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkAttachment;
use Mailgun\Mailgun;

class MailHelper
{
    private $settings;
    private $to;
    private $body;
    private $subject;
    private $lang;
    private $fallback;
    private $files;

    public function __construct($settings = [])
    {
        $this->settings = [
            'provider' => !array_key_exists('provider', $settings) ? Config::get('mail.provider') : $settings['provider'],
            'host' => !array_key_exists('host', $settings) ? Config::get('mail.host') : $settings['host'],
            'port' => !array_key_exists('port', $settings) ? Config::get('mail.port') : $settings['port'],
            'encryption' => !array_key_exists('encryption', $settings) ? Config::get('mail.encryption') : $settings['encryption'],
            'username' => !array_key_exists('username', $settings) ? Config::get('mail.username') : $settings['username'],
            'password' => !array_key_exists('password', $settings) ? Config::get('mail.password') : $settings['password'],
            'from_name' => !array_key_exists('from_name', $settings) ? Config::get('mail.from.name') : $settings['from_name'],
            'from_address' => !array_key_exists('from_address', $settings) ? Config::get('mail.from.address') : $settings['from_address'],
            'api_key' => trim(!array_key_exists('api_key', $settings) ? Config::get('mail.api_key') : $settings['api_key']),
            'domain' => !array_key_exists('domain', $settings) ? Config::get('mail.domain') : $settings['domain'],
        ];
    }

    public function send($to, $body, $subject, $lang = NULL, $fallback = TRUE, $files = [])
    {
        if (is_array($to)) {
            $this->to = array_map('trim', $to);
        }
        else {
            $this->to = ['0' => $to];
        }
        $this->body = $body;
        $this->subject = $subject;
        $this->lang = $lang;
        $this->fallback = $fallback;
        $this->files = $files;

        $type = $this->settings['provider'];
        if (is_null($type))
            $type = 'smtp';

        return call_user_func(array($this, $type));
    }

    public function smtp()
    {
        try {
            $mail = new PHPMailer;
            $mail->CharSet = "UTF-8";

            # SMTP
            if (!empty($this->settings['username'])) {
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = $this->settings['host'];  // Specify main and backup SMTP servers
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = $this->settings['username'];                 // SMTP username
                $mail->Password = $this->settings['password'];                           // SMTP password
                if (!empty($this->settings['encryption']))
                    $mail->SMTPSecure = $this->settings['encryption'];                            // Enable TLS encryption, `ssl` also accepted
                if (!empty($this->settings['port']))
                    $mail->Port = $this->settings['port'];                                    // TCP port to connect to
            }

            $mail->setFrom($this->settings['from_address'], $this->settings['from_name']);
            foreach ($this->to as $key => $email) {
                $mail->addAddress($email);
            }

            $mail->isHTML(true);

            $mail->Subject = $this->subject;
            $mail->Body    = $this->body;

            if (!empty($this->files)) {
                foreach ($this->files as $file)
                    $mail->AddAttachment($file);
            }

            if(!$mail->send())
                throw new \Exception( $mail->ErrorInfo );

            return ['status' => 1];
        }
        catch(\Exception $e) {
            if ($this->fallback) {
                Mail::setSwiftMailer(new \Swift_Mailer(MailTransport::newInstance()));
                Mail::send('front::Emails.template', array('body' => $this->body, 'lang' => $this->lang), function($message)
                {
                    foreach ($this->to as $key => $email) {
                        $message->to($email)->subject($this->subject);
                    }
                });
            }

            return ['status' => 0, 'error' => $e->getMessage()];
        }
    }

    public function sendgrid()
    {
        $res = NULL;
        $from = new \SendGrid\Email($this->settings['from_name'], $this->settings['from_address']);
        $content = new \SendGrid\Content("text/html", $this->body);
        $sg = new \SendGrid($this->settings['api_key']);

        foreach ($this->to as $key => $email) {
            $to = new \SendGrid\Email(null, $email);
            $mail = new \SendGrid\Mail($from, $this->subject, $to, $content);

            if (!empty($this->files)) {
                foreach ($this->files as $file) {
                    $name = File::name($file);
                    $attachment = new Attachment();
                    $attachment->setContent(base64_encode(file_get_contents($file)));
                    $attachment->setType(File::mimeType($file));
                    $attachment->setFilename($name.'.'.File::extension($file));
                    $attachment->setDisposition("attachment");
                    $attachment->setContentId("Balance Sheet");
                    $mail->addAttachment($attachment);
                }
            }

            $res = $sg->client->mail()->send()->post($mail);
        }

        $error = '';
        $body = json_decode($res->body(), TRUE);
        if (isset($body['errors'])) {
            $error = current($body['errors'])['message'];
        }


        return ['status' => ($res->statusCode() == '202' ? 1 : 0), 'error' => $res->statusCode().' - '.$error];
    }

    public function postmark()
    {
        $error = NULL;
        $client = new PostmarkClient($this->settings['api_key']);

        foreach ($this->to as $key => $email) {
            try {
                $files = [];
                if (!empty($this->files)) {
                    foreach ($this->files as $file) {
                        $files[] = PostmarkAttachment::fromRawData(file_get_contents($file), File::name($file).'.'.File::extension($file), File::mimeType($file));
                    }
                }
                $res = $client->sendEmail(
                    $this->settings['from_address'],
                    $email,
                    $this->subject,
                    $this->body,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    $files
                );
            }
            catch(\Exception $e) {
                $error = $e->getMessage();
            }
        }


        return ['status' => (is_null($error) ? 1 : 0), 'error' => $error];
    }

    public function mailgun()
    {
        $error = NULL;
        $mgClient = new Mailgun($this->settings['api_key']);

        foreach ($this->to as $key => $email) {
            try {
                $mgClient->sendMessage($this->settings['domain'], [
                    'from' => $this->settings['from_address'],
                    'to' => $email,
                    'subject' => $this->subject,
                    'html' => $this->body
                ]);

            }
            catch(\Exception $e) {
                $error = $e->getMessage();
            }
        }


        return ['status' => (is_null($error) ? 1 : 0), 'error' => $error];
    }
}