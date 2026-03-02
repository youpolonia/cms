<?php
declare(strict_types=1);

/**
 * Jessie CMS — Unified Payment Gateway
 * Supports: Stripe, PayPal, Bank Transfer, Cash on Delivery
 * 
 * Config stored in settings table:
 *   payment_stripe_enabled, payment_stripe_public_key, payment_stripe_secret_key
 *   payment_paypal_enabled, payment_paypal_client_id, payment_paypal_secret, payment_paypal_mode
 *   payment_bank_enabled, payment_bank_instructions
 *   payment_cod_enabled
 *   payment_currency (default: USD)
 */

class PaymentGateway
{
    // ═══════════════════════════════════════════
    //  AVAILABLE METHODS
    // ═══════════════════════════════════════════

    /**
     * Get all enabled payment methods
     */
    public static function getAvailableMethods(): array
    {
        $methods = [];

        if (self::getSetting('payment_stripe_enabled') === '1') {
            $methods[] = [
                'id'          => 'stripe',
                'name'        => 'Credit / Debit Card',
                'icon'        => '💳',
                'description' => 'Pay securely with Visa, Mastercard, Amex',
                'online'      => true,
            ];
        }

        if (self::getSetting('payment_paypal_enabled') === '1') {
            $methods[] = [
                'id'          => 'paypal',
                'name'        => 'PayPal',
                'icon'        => '🅿️',
                'description' => 'Pay with your PayPal account',
                'online'      => true,
            ];
        }

        if (self::getSetting('payment_bank_enabled', '1') === '1') {
            $methods[] = [
                'id'          => 'bank_transfer',
                'name'        => 'Bank Transfer',
                'icon'        => '🏦',
                'description' => 'Manual bank transfer',
                'online'      => false,
            ];
        }

        if (self::getSetting('payment_cod_enabled', '1') === '1') {
            $methods[] = [
                'id'          => 'cash_on_delivery',
                'name'        => 'Cash on Delivery',
                'icon'        => '💵',
                'description' => 'Pay when you receive your order',
                'online'      => false,
            ];
        }

        return $methods;
    }

    /**
     * Check if any online payment method is enabled
     */
    public static function hasOnlinePayment(): bool
    {
        return self::getSetting('payment_stripe_enabled') === '1'
            || self::getSetting('payment_paypal_enabled') === '1';
    }

    // ═══════════════════════════════════════════
    //  STRIPE
    // ═══════════════════════════════════════════

    /**
     * Create a Stripe Checkout Session
     * Returns: ['session_id' => '...', 'url' => '...'] or ['error' => '...']
     */
    public static function stripeCreateSession(array $params): array
    {
        $secretKey = self::getSetting('payment_stripe_secret_key');
        if (empty($secretKey)) {
            return ['error' => 'Stripe is not configured'];
        }

        $currency = strtolower(self::getSetting('payment_currency', 'USD'));
        $siteUrl = rtrim(self::getSetting('site_url', ''), '/');

        // Build line items
        $lineItems = [];
        foreach ($params['items'] as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => [
                        'name'        => $item['name'],
                        'description' => $item['description'] ?? '',
                    ],
                    'unit_amount' => (int)round(((float)$item['price']) * 100), // cents
                ],
                'quantity' => (int)($item['quantity'] ?? 1),
            ];
        }

        // Add shipping if > 0
        if (!empty($params['shipping']) && (float)$params['shipping'] > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount'  => (int)round(((float)$params['shipping']) * 100),
                ],
                'quantity' => 1,
            ];
        }

        // Add tax if > 0
        if (!empty($params['tax']) && (float)$params['tax'] > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => $currency,
                    'product_data' => ['name' => 'Tax'],
                    'unit_amount'  => (int)round(((float)$params['tax']) * 100),
                ],
                'quantity' => 1,
            ];
        }

        $successUrl = $params['success_url'] ?? ($siteUrl . '/checkout/success?session_id={CHECKOUT_SESSION_ID}');
        $cancelUrl = $params['cancel_url'] ?? ($siteUrl . '/checkout/cancel');

        $postData = [
            'payment_method_types[]' => 'card',
            'mode'                   => $params['mode'] ?? 'payment',
            'success_url'            => $successUrl,
            'cancel_url'             => $cancelUrl,
            'client_reference_id'    => $params['reference_id'] ?? '',
            'customer_email'         => $params['customer_email'] ?? '',
        ];

        // Encode line items for curl
        foreach ($lineItems as $i => $li) {
            foreach ($li['price_data'] as $k => $v) {
                if ($k === 'product_data') {
                    foreach ($v as $pk => $pv) {
                        $postData["line_items[{$i}][price_data][product_data][{$pk}]"] = $pv;
                    }
                } else {
                    $postData["line_items[{$i}][price_data][{$k}]"] = $v;
                }
            }
            $postData["line_items[{$i}][quantity]"] = $li['quantity'];
        }

        // Metadata
        if (!empty($params['metadata'])) {
            foreach ($params['metadata'] as $mk => $mv) {
                $postData["metadata[{$mk}]"] = $mv;
            }
        }

        $response = self::stripeRequest('POST', '/v1/checkout/sessions', $postData, $secretKey);

        if (!empty($response['error'])) {
            return ['error' => $response['error']['message'] ?? 'Stripe error'];
        }

        return [
            'session_id' => $response['id'] ?? '',
            'url'        => $response['url'] ?? '',
        ];
    }

    /**
     * Retrieve a Stripe Checkout Session (to verify payment)
     */
    public static function stripeGetSession(string $sessionId): array
    {
        $secretKey = self::getSetting('payment_stripe_secret_key');
        if (empty($secretKey)) {
            return ['error' => 'Stripe is not configured'];
        }

        return self::stripeRequest('GET', '/v1/checkout/sessions/' . urlencode($sessionId), [], $secretKey);
    }

    /**
     * Create a Stripe Payment Intent (for inline card forms)
     */
    public static function stripeCreatePaymentIntent(float $amount, string $currency = '', array $metadata = []): array
    {
        $secretKey = self::getSetting('payment_stripe_secret_key');
        if (empty($secretKey)) {
            return ['error' => 'Stripe is not configured'];
        }

        if (empty($currency)) {
            $currency = strtolower(self::getSetting('payment_currency', 'USD'));
        }

        $postData = [
            'amount'   => (int)round($amount * 100),
            'currency' => strtolower($currency),
            'payment_method_types[]' => 'card',
        ];

        foreach ($metadata as $k => $v) {
            $postData["metadata[{$k}]"] = $v;
        }

        $response = self::stripeRequest('POST', '/v1/payment_intents', $postData, $secretKey);

        if (!empty($response['error'])) {
            return ['error' => $response['error']['message'] ?? 'Stripe error'];
        }

        return [
            'client_secret'    => $response['client_secret'] ?? '',
            'payment_intent_id' => $response['id'] ?? '',
        ];
    }

    /**
     * Process Stripe webhook
     */
    public static function stripeHandleWebhook(string $payload, string $signature): array
    {
        $webhookSecret = self::getSetting('payment_stripe_webhook_secret');
        if (empty($webhookSecret)) {
            return ['error' => 'Webhook secret not configured'];
        }

        // Verify signature
        $timestamp = '';
        $sig = '';
        foreach (explode(',', $signature) as $part) {
            $part = trim($part);
            if (str_starts_with($part, 't=')) $timestamp = substr($part, 2);
            if (str_starts_with($part, 'v1=')) $sig = substr($part, 3);
        }

        if (empty($timestamp) || empty($sig)) {
            return ['error' => 'Invalid signature format'];
        }

        $expectedSig = hash_hmac('sha256', "{$timestamp}.{$payload}", $webhookSecret);
        if (!hash_equals($expectedSig, $sig)) {
            return ['error' => 'Signature verification failed'];
        }

        // Tolerance: 5 minutes
        if (abs(time() - (int)$timestamp) > 300) {
            return ['error' => 'Timestamp too old'];
        }

        $event = json_decode($payload, true);
        if (!$event) {
            return ['error' => 'Invalid JSON payload'];
        }

        return ['success' => true, 'event' => $event];
    }

    /**
     * Low-level Stripe API request
     */
    private static function stripeRequest(string $method, string $endpoint, array $data, string $secretKey): array
    {
        $url = 'https://api.stripe.com' . $endpoint;

        $ch = curl_init();
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($secretKey . ':'),
            ],
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $opts[CURLOPT_URL] = $url;
        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => ['message' => "cURL error: {$error}"]];
        }

        $decoded = json_decode($response, true) ?: [];

        if ($httpCode >= 400) {
            error_log("Stripe API error ({$httpCode}): " . mb_substr($response, 0, 500));
        }

        return $decoded;
    }

    // ═══════════════════════════════════════════
    //  PAYPAL
    // ═══════════════════════════════════════════

    /**
     * Create a PayPal order
     * Returns: ['order_id' => '...', 'approval_url' => '...'] or ['error' => '...']
     */
    public static function paypalCreateOrder(array $params): array
    {
        $accessToken = self::paypalGetAccessToken();
        if (!$accessToken) {
            return ['error' => 'PayPal authentication failed'];
        }

        $currency = strtoupper(self::getSetting('payment_currency', 'USD'));
        $total = number_format((float)$params['total'], 2, '.', '');
        $siteUrl = rtrim(self::getSetting('site_url', ''), '/');

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $params['reference_id'] ?? uniqid('pp_'),
                'description'  => $params['description'] ?? 'Order',
                'amount'       => [
                    'currency_code' => $currency,
                    'value'         => $total,
                    'breakdown'     => [
                        'item_total' => [
                            'currency_code' => $currency,
                            'value'         => number_format((float)($params['subtotal'] ?? $params['total']), 2, '.', ''),
                        ],
                    ],
                ],
                'items' => [],
            ]],
            'application_context' => [
                'return_url' => $params['return_url'] ?? ($siteUrl . '/checkout/paypal-return'),
                'cancel_url' => $params['cancel_url'] ?? ($siteUrl . '/checkout/cancel'),
                'brand_name' => self::getSetting('site_name', 'Store'),
                'user_action' => 'PAY_NOW',
            ],
        ];

        // Add items
        if (!empty($params['items'])) {
            foreach ($params['items'] as $item) {
                $orderData['purchase_units'][0]['items'][] = [
                    'name'        => mb_substr($item['name'], 0, 127),
                    'quantity'    => (string)(int)($item['quantity'] ?? 1),
                    'unit_amount' => [
                        'currency_code' => $currency,
                        'value'         => number_format((float)$item['price'], 2, '.', ''),
                    ],
                ];
            }
        }

        // Add shipping/tax to breakdown
        if (!empty($params['shipping']) && (float)$params['shipping'] > 0) {
            $orderData['purchase_units'][0]['amount']['breakdown']['shipping'] = [
                'currency_code' => $currency,
                'value'         => number_format((float)$params['shipping'], 2, '.', ''),
            ];
        }
        if (!empty($params['tax']) && (float)$params['tax'] > 0) {
            $orderData['purchase_units'][0]['amount']['breakdown']['tax_total'] = [
                'currency_code' => $currency,
                'value'         => number_format((float)$params['tax'], 2, '.', ''),
            ];
        }

        $response = self::paypalRequest('POST', '/v2/checkout/orders', $orderData, $accessToken);

        if (!empty($response['error'])) {
            return ['error' => $response['error']];
        }

        // Find approval URL
        $approvalUrl = '';
        foreach ($response['links'] ?? [] as $link) {
            if ($link['rel'] === 'approve') {
                $approvalUrl = $link['href'];
                break;
            }
        }

        return [
            'order_id'     => $response['id'] ?? '',
            'approval_url' => $approvalUrl,
            'status'       => $response['status'] ?? '',
        ];
    }

    /**
     * Capture a PayPal order (after buyer approves)
     */
    public static function paypalCaptureOrder(string $orderId): array
    {
        $accessToken = self::paypalGetAccessToken();
        if (!$accessToken) {
            return ['error' => 'PayPal authentication failed'];
        }

        $response = self::paypalRequest('POST', "/v2/checkout/orders/{$orderId}/capture", [], $accessToken);

        if (!empty($response['error'])) {
            return ['error' => $response['error']];
        }

        $captureId = '';
        $status = $response['status'] ?? '';
        foreach ($response['purchase_units'] ?? [] as $pu) {
            foreach ($pu['payments']['captures'] ?? [] as $capture) {
                $captureId = $capture['id'] ?? '';
                break 2;
            }
        }

        return [
            'status'     => $status,
            'capture_id' => $captureId,
            'payer'      => $response['payer'] ?? [],
        ];
    }

    /**
     * Get PayPal access token (OAuth2 client credentials)
     */
    private static function paypalGetAccessToken(): ?string
    {
        $clientId = self::getSetting('payment_paypal_client_id');
        $secret = self::getSetting('payment_paypal_secret');
        if (empty($clientId) || empty($secret)) {
            return null;
        }

        $baseUrl = self::paypalBaseUrl();
        $ch = curl_init($baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
            CURLOPT_USERPWD        => $clientId . ':' . $secret,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    /**
     * Low-level PayPal API request
     */
    private static function paypalRequest(string $method, string $endpoint, array $data, string $accessToken): array
    {
        $url = self::paypalBaseUrl() . $endpoint;

        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = !empty($data) ? json_encode($data) : '{}';
        }

        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => "cURL error: {$error}"];
        }

        $decoded = json_decode($response, true) ?: [];

        if ($httpCode >= 400) {
            error_log("PayPal API error ({$httpCode}): " . mb_substr($response, 0, 500));
            $msg = $decoded['message'] ?? $decoded['details'][0]['description'] ?? "HTTP {$httpCode}";
            return ['error' => $msg];
        }

        return $decoded;
    }

    private static function paypalBaseUrl(): string
    {
        $mode = self::getSetting('payment_paypal_mode', 'sandbox');
        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    // ═══════════════════════════════════════════
    //  UNIFIED PROCESSING
    // ═══════════════════════════════════════════

    /**
     * Process a payment for a given order/context.
     * Returns ['success' => true, 'redirect' => '...'] or ['success' => true, 'paid' => false] for manual methods
     * or ['error' => '...'] on failure.
     *
     * @param string $method   Payment method ID (stripe, paypal, bank_transfer, cash_on_delivery)
     * @param float  $total    Total amount
     * @param array  $context  Context: items, customer_email, reference_id, description, subtotal, tax, shipping, metadata, success_url, cancel_url
     */
    public static function processPayment(string $method, float $total, array $context = []): array
    {
        switch ($method) {
            case 'stripe':
                $result = self::stripeCreateSession([
                    'items'          => $context['items'] ?? [['name' => $context['description'] ?? 'Order', 'price' => $total, 'quantity' => 1]],
                    'customer_email' => $context['customer_email'] ?? '',
                    'reference_id'   => $context['reference_id'] ?? '',
                    'shipping'       => $context['shipping'] ?? 0,
                    'tax'            => $context['tax'] ?? 0,
                    'metadata'       => $context['metadata'] ?? [],
                    'success_url'    => $context['success_url'] ?? null,
                    'cancel_url'     => $context['cancel_url'] ?? null,
                ]);
                if (!empty($result['error'])) {
                    return ['error' => $result['error']];
                }
                return [
                    'success'    => true,
                    'redirect'   => $result['url'],
                    'session_id' => $result['session_id'],
                    'provider'   => 'stripe',
                ];

            case 'paypal':
                $result = self::paypalCreateOrder([
                    'total'        => $total,
                    'subtotal'     => $context['subtotal'] ?? $total,
                    'shipping'     => $context['shipping'] ?? 0,
                    'tax'          => $context['tax'] ?? 0,
                    'items'        => $context['items'] ?? [['name' => $context['description'] ?? 'Order', 'price' => $total, 'quantity' => 1]],
                    'reference_id' => $context['reference_id'] ?? '',
                    'description'  => $context['description'] ?? 'Order',
                    'return_url'   => $context['success_url'] ?? null,
                    'cancel_url'   => $context['cancel_url'] ?? null,
                ]);
                if (!empty($result['error'])) {
                    return ['error' => $result['error']];
                }
                return [
                    'success'      => true,
                    'redirect'     => $result['approval_url'],
                    'pp_order_id'  => $result['order_id'],
                    'provider'     => 'paypal',
                ];

            case 'bank_transfer':
                return [
                    'success'      => true,
                    'paid'         => false,
                    'provider'     => 'bank_transfer',
                    'instructions' => self::getSetting('payment_bank_instructions', 'Please transfer the total amount to our bank account. Your order will be processed after payment is confirmed.'),
                ];

            case 'cash_on_delivery':
                return [
                    'success'  => true,
                    'paid'     => false,
                    'provider' => 'cash_on_delivery',
                ];

            default:
                return ['error' => "Unknown payment method: {$method}"];
        }
    }

    /**
     * Verify a completed payment and mark order as paid.
     * Call this from success/return URLs or webhooks.
     */
    public static function verifyAndComplete(string $provider, array $params): array
    {
        switch ($provider) {
            case 'stripe':
                $sessionId = $params['session_id'] ?? '';
                if (empty($sessionId)) {
                    return ['error' => 'Missing session_id'];
                }
                $session = self::stripeGetSession($sessionId);
                if (!empty($session['error'])) {
                    return ['error' => $session['error']['message'] ?? 'Stripe verification failed'];
                }
                if (($session['payment_status'] ?? '') !== 'paid') {
                    return ['error' => 'Payment not completed', 'status' => $session['payment_status'] ?? 'unknown'];
                }
                return [
                    'success'        => true,
                    'provider'       => 'stripe',
                    'transaction_id' => $session['payment_intent'] ?? $sessionId,
                    'reference_id'   => $session['client_reference_id'] ?? '',
                    'amount_paid'    => ($session['amount_total'] ?? 0) / 100,
                    'currency'       => $session['currency'] ?? '',
                    'customer_email' => $session['customer_details']['email'] ?? $session['customer_email'] ?? '',
                ];

            case 'paypal':
                $orderId = $params['order_id'] ?? $params['token'] ?? '';
                if (empty($orderId)) {
                    return ['error' => 'Missing PayPal order_id'];
                }
                $capture = self::paypalCaptureOrder($orderId);
                if (!empty($capture['error'])) {
                    return ['error' => $capture['error']];
                }
                if ($capture['status'] !== 'COMPLETED') {
                    return ['error' => 'Payment not completed', 'status' => $capture['status']];
                }
                return [
                    'success'        => true,
                    'provider'       => 'paypal',
                    'transaction_id' => $capture['capture_id'] ?? $orderId,
                    'payer_email'    => $capture['payer']['email_address'] ?? '',
                ];

            default:
                return ['error' => "Unknown provider: {$provider}"];
        }
    }

    /**
     * Mark an order as paid in the database
     */
    public static function markOrderPaid(int $orderId, string $transactionId, string $provider): bool
    {
        $pdo = db();
        try {
            $stmt = $pdo->prepare(
                "UPDATE orders SET payment_status = 'paid', payment_method = ?, status = 'processing',
                 notes = CONCAT(COALESCE(notes,''), '\nPayment confirmed via {$provider}: {$transactionId} at " . date('Y-m-d H:i:s') . "')
                 WHERE id = ?"
            );
            $result = $stmt->execute([$provider, $orderId]);

            if ($result && function_exists('cms_event')) {
                cms_event('shop.order.paid', [
                    'order_id'       => $orderId,
                    'provider'       => $provider,
                    'transaction_id' => $transactionId,
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            error_log("PaymentGateway::markOrderPaid error: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════
    //  CONFIG / ADMIN
    // ═══════════════════════════════════════════

    /**
     * Get Stripe public key (for frontend JS)
     */
    public static function getStripePublicKey(): string
    {
        return self::getSetting('payment_stripe_public_key', '');
    }

    /**
     * Get PayPal client ID (for frontend JS)
     */
    public static function getPayPalClientId(): string
    {
        return self::getSetting('payment_paypal_client_id', '');
    }

    /**
     * Get PayPal mode (sandbox/live)
     */
    public static function getPayPalMode(): string
    {
        return self::getSetting('payment_paypal_mode', 'sandbox');
    }

    /**
     * Get all payment settings for admin panel
     */
    public static function getSettings(): array
    {
        return [
            'stripe_enabled'        => self::getSetting('payment_stripe_enabled', '0'),
            'stripe_public_key'     => self::getSetting('payment_stripe_public_key', ''),
            'stripe_secret_key'     => self::getSetting('payment_stripe_secret_key', ''),
            'stripe_webhook_secret' => self::getSetting('payment_stripe_webhook_secret', ''),
            'paypal_enabled'        => self::getSetting('payment_paypal_enabled', '0'),
            'paypal_client_id'      => self::getSetting('payment_paypal_client_id', ''),
            'paypal_secret'         => self::getSetting('payment_paypal_secret', ''),
            'paypal_mode'           => self::getSetting('payment_paypal_mode', 'sandbox'),
            'bank_enabled'          => self::getSetting('payment_bank_enabled', '1'),
            'bank_instructions'     => self::getSetting('payment_bank_instructions', ''),
            'cod_enabled'           => self::getSetting('payment_cod_enabled', '1'),
            'currency'              => self::getSetting('payment_currency', 'USD'),
        ];
    }

    /**
     * Save payment settings
     */
    public static function saveSettings(array $data): void
    {
        $allowed = [
            'payment_stripe_enabled', 'payment_stripe_public_key', 'payment_stripe_secret_key', 'payment_stripe_webhook_secret',
            'payment_paypal_enabled', 'payment_paypal_client_id', 'payment_paypal_secret', 'payment_paypal_mode',
            'payment_bank_enabled', 'payment_bank_instructions', 'payment_cod_enabled', 'payment_currency',
        ];

        $pdo = db();
        foreach ($allowed as $key) {
            $shortKey = str_replace('payment_', '', $key);
            if (array_key_exists($shortKey, $data) || array_key_exists($key, $data)) {
                $value = $data[$shortKey] ?? $data[$key] ?? '';
                // Upsert
                $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)")
                    ->execute([$key, $value]);
            }
        }
    }

    // ═══════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════

    private static function getSetting(string $key, string $default = ''): string
    {
        if (function_exists('get_setting')) {
            return get_setting($key, $default);
        }
        // Fallback: direct DB query
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? ($row['value'] ?? $default) : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
