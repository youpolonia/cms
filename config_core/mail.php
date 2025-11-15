<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => getenv('MAIL_MAILER') ?: 'log',

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => getenv('MAIL_SCHEME') ?: null,
            'url' => getenv('MAIL_URL') ?: null,
            'host' => getenv('MAIL_HOST') ?: '127.0.0.1',
            'port' => getenv('MAIL_PORT') !== false ? (int)getenv('MAIL_PORT') : 2525,
            'username' => getenv('MAIL_USERNAME') ?: null,
            'password' => getenv('MAIL_PASSWORD') ?: null,
            'timeout' => null,
            'local_domain' => getenv('MAIL_EHLO_DOMAIN') ?: parse_url(getenv('APP_URL') ?: 'http://localhost', PHP_URL_HOST),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => getenv('MAIL_SENDMAIL_PATH') ?: '/usr/sbin/sendmail -bs -i',
        ],

        'log' => [
            'transport' => 'log',
            'channel' => getenv('MAIL_LOG_CHANNEL') ?: null,
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: 'hello@example.com',
        'name' => getenv('MAIL_FROM_NAME') ?: 'Example',
    ],

];
