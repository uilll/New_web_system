<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,
    'http' => [
        'sender_id' => env('FCM_SENDER_ID', '751639536937'),
        'server_key' => env('FCM_SERVER_KEY', 'AAAArwE1JSk:APA91bGXMhgJkYpW2_rwy5F9Luaf5jKbDT1EY28B_B_lzPdYZy-VyeH8v_Y4qNh1Tb6eBGNiZbIUn2ECVlxag8uoqvNqopa32Q-SHJseLh0oAhk86irIKdh0RENw5k-O71qL2ih2Uout'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
