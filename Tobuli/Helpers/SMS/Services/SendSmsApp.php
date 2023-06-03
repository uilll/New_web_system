<?php

namespace Tobuli\Helpers\SMS\Services;

use Facades\Repositories\SmsEventQueueRepo;

class SendSmsApp extends SendSmsManager
{
    private $user_id;

    /**
     * SendSmsApp constructor.
     */
    public function __construct($gateway_args)
    {
        $this->user_id = $gateway_args['user_id'];
    }

    protected function sendSingle($receiver_phone, $message_body)
    {
        SmsEventQueueRepo::create([
            'user_id' => $this->user_id,
            'phone' => $receiver_phone,
            'message' => $message_body,
        ]);
    }
}
