<?php
/**
 * Webhook Configuration Template
 * 
 * Framework-free PHP webhook configuration for CMS integration with external services
 * 
 * Required Parameters:
 * - endpoint_url: The URL where webhook events will be sent (string)
 * - secret_key: The secret key used for signing webhook payloads (string)
 * - events: Array of subscribed event types (array)
 * - retry_settings: Number of retry attempts for failed deliveries (int)
 * 
 * @package CMS
 * @subpackage Config
 */

return [
    /**
     * Default Webhook Configuration
     */
    'default' => [
        'endpoint_url' => '', // Required: Webhook endpoint URL
        'secret_key' => '',   // Required: Secret key for HMAC signature
        'events' => [],       // Required: Array of subscribed event types
        'retry_settings' => 3 // Required: Number of retry attempts (0-5)
    ],

    /**
     * Example Integration with n8n
     */
    'n8n_integration' => [
        'endpoint_url' => 'https://your-n8n-instance.com/webhook',
        'secret_key' => 'your-secret-key-here',
        'events' => [
            'content.published',
            'content.updated',
            'user.created'
        ],
        'retry_settings' => 2
    ],

    /**
     * Validation Requirements
     * - endpoint_url: Must be valid URL
     * - secret_key: Must be at least 32 chars
     * - events: Must be non-empty array
     * - retry_settings: Must be integer 0-5
     */
];
