<?php

namespace App\Services\Mail;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Mail;
use Swift_MailTransport as MailTransport;

class MailHelper
{
    public function send($to, $body, $subject, $lang = null, $fallback = true, $attaches = [], $view = null)
    {
        if (empty($to)) {
            return [
                'status' => false,
                'error' => 'Empty to',
            ];
        }

        if (! is_array($to)) {
            $to = [$to];
        }

        $to = array_map('trim', $to);
        $to = array_filter($to, function ($value) {
            return ! empty($value);
        });

        if (empty($to)) {
            return [
                'status' => false,
                'error' => 'Empty to',
            ];
        }

        if (empty($view)) {
            $view = 'front::Emails.template';
        }

        if (! empty($attaches) && is_string($attaches)) {
            $attaches = [$attaches];
        }

        $data = [
            'to' => array_map('trim', $to),
            'subject' => $subject,
            'body' => $body,
            'lang' => $lang,
            'attaches' => $attaches,
        ];

        try {
            Mail::send($view, $data, function ($message) use ($data) {
                $message
                    ->to($data['to'])
                    ->subject($data['subject']);

                if (! empty($data['attaches'])) {
                    foreach ($data['attaches'] as $attach) {
                        $message->attach($attach);
                    }
                }
            });
        } catch (ClientException $e) {
            $error = $e->getMessage();

            $response = $e->getResponse();

            if ($response && $response->getStatusCode() == 422) {
                $fallback = false;
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if (! empty($error) && $fallback) {
            $backupMailer = Mail::getSwiftMailer();

            Mail::setSwiftMailer(new \Swift_Mailer(MailTransport::newInstance()));

            Mail::send($view, $data, function ($message) use ($data) {
                $message
                    ->to($data['to'])
                    ->subject($data['subject']);

                if (! empty($data['attaches'])) {
                    foreach ($data['attaches'] as $attach) {
                        $message->attach($attach);
                    }
                }
            });

            Mail::setSwiftMailer($backupMailer);
        }

        return [
            'status' => empty($error),
            'error' => empty($error) ? null : $error,
        ];
    }
}
