<?php

return [
    'templates' => [
        'default' => [
            'name' => 'Default Notification',
            'subject' => 'New Notification',
            'content' => 'You have a new notification: {message}',
            'variables' => ['message'],
            'is_active' => true
        ],
        'content-update' => [
            'name' => 'Content Update',
            'subject' => 'Content Updated: {title}',
            'content' => 'The content "{title}" has been updated by {author}',
            'variables' => ['title', 'author'],
            'is_active' => true
        ],
        'system-alert' => [
            'name' => 'System Alert',
            'subject' => 'System Notification: {alert_type}',
            'content' => 'System notification: {alert_message}',
            'variables' => ['alert_type', 'alert_message'],
            'is_active' => true
        ]
    ]
];