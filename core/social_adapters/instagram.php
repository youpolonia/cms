<?php
declare(strict_types=1);

/**
 * Instagram Social Media Adapter
 * Uses Instagram Graph API (via Facebook) for publishing
 * Requires Facebook Page connected to Instagram Professional Account
 */

require_once __DIR__ . '/base.php';

class InstagramAdapter extends SocialAdapter
{
    protected string $platform = 'instagram';

    private const AUTH_URL = 'https://www.facebook.com/v19.0/dialog/oauth';
    private const TOKEN_URL = 'https://graph.facebook.com/v19.0/oauth/access_token';
    private const GRAPH_URL = 'https://graph.facebook.com/v19.0';

    /**
     * Publish a post to Instagram
     * Instagram requires media (image/video) for publishing
     * Two-step process: create media container, then publish
     */
    public function publish(string $content, ?string $mediaUrl = null, ?string $linkUrl = null): array
    {
        $account = $this->getAccount();
        if (!$account || empty($account['access_token'])) {
            return ['ok' => false, 'external_id' => null, 'error' => 'Instagram not connected'];
        }

        $igUserId = $this->getMeta('ig_user_id', '');
        $pageToken = $this->getMeta('page_access_token', $account['access_token']);

        if (empty($igUserId)) {
            return ['ok' => false, 'external_id' => null, 'error' => 'Instagram user ID not configured. Connect via Facebook Page.'];
        }

        // Instagram requires an image URL for feed posts
        if (empty($mediaUrl)) {
            // For text-only posts, we can try a carousel or just return an error
            return ['ok' => false, 'external_id' => null, 'error' => 'Instagram requires an image URL for publishing. Add a media_url to the post.'];
        }

        // Step 1: Create media container
        $containerPayload = [
            'image_url' => $mediaUrl,
            'caption' => $content,
        ];

        $resp = $this->httpRequest(
            self::GRAPH_URL . '/' . $igUserId . '/media',
            'POST',
            [
                'Authorization: Bearer ' . $pageToken,
                'Content-Type: application/json',
            ],
            $containerPayload
        );

        if ($resp['error']) {
            return ['ok' => false, 'external_id' => null, 'error' => $resp['error']];
        }

        $data = json_decode($resp['body'], true);

        if (empty($data['id'])) {
            $errorMsg = $data['error']['message'] ?? ('HTTP ' . $resp['code']);
            return ['ok' => false, 'external_id' => null, 'error' => 'Instagram container: ' . $errorMsg];
        }

        $containerId = $data['id'];

        // Step 2: Publish the container
        $publishResp = $this->httpRequest(
            self::GRAPH_URL . '/' . $igUserId . '/media_publish',
            'POST',
            [
                'Authorization: Bearer ' . $pageToken,
                'Content-Type: application/json',
            ],
            ['creation_id' => $containerId]
        );

        if ($publishResp['error']) {
            return ['ok' => false, 'external_id' => null, 'error' => $publishResp['error']];
        }

        $publishData = json_decode($publishResp['body'], true);

        if (!empty($publishData['id'])) {
            return ['ok' => true, 'external_id' => $publishData['id'], 'error' => null];
        }

        $errorMsg = $publishData['error']['message'] ?? ('HTTP ' . $publishResp['code']);
        return ['ok' => false, 'external_id' => null, 'error' => 'Instagram publish: ' . $errorMsg];
    }

    /**
     * Get OAuth 2.0 authorization URL (via Facebook)
     */
    public function getAuthUrl(string $callbackUrl): string
    {
        $clientId = $this->getMeta('client_id', '');
        if (empty($clientId)) {
            return '#error=client_id_not_configured';
        }

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $callbackUrl,
            'scope' => 'instagram_basic,instagram_content_publish,pages_show_list,pages_read_engagement',
            'response_type' => 'code',
            'state' => bin2hex(random_bytes(16)),
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Handle OAuth callback
     */
    public function handleCallback(string $code, string $callbackUrl): bool
    {
        $clientId = $this->getMeta('client_id', '');
        $clientSecret = $this->getMeta('client_secret', '');

        if (empty($clientId) || empty($clientSecret)) {
            error_log('[SOCIAL] Instagram: client_id or client_secret not configured');
            return false;
        }

        // Exchange code for token
        $url = self::TOKEN_URL . '?' . http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $callbackUrl,
            'code' => $code,
        ]);

        $resp = $this->httpRequest($url, 'GET');

        if ($resp['error'] || $resp['code'] !== 200) {
            error_log('[SOCIAL] Instagram token exchange failed: ' . ($resp['error'] ?? $resp['body']));
            return false;
        }

        $data = json_decode($resp['body'], true);
        if (empty($data['access_token'])) {
            return false;
        }

        $userToken = $data['access_token'];
        $expires = null;
        if (!empty($data['expires_in'])) {
            $expires = date('Y-m-d H:i:s', time() + (int)$data['expires_in']);
        }

        $this->saveToken($userToken, null, $expires);

        // Exchange for long-lived token
        $llResp = $this->httpRequest(
            self::GRAPH_URL . '/oauth/access_token?' . http_build_query([
                'grant_type' => 'fb_exchange_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'fb_exchange_token' => $userToken,
            ]),
            'GET'
        );

        if ($llResp['code'] === 200) {
            $llData = json_decode($llResp['body'], true);
            if (!empty($llData['access_token'])) {
                $llExpires = null;
                if (!empty($llData['expires_in'])) {
                    $llExpires = date('Y-m-d H:i:s', time() + (int)$llData['expires_in']);
                }
                $this->saveToken($llData['access_token'], null, $llExpires);
                $userToken = $llData['access_token'];
            }
        }

        // Fetch pages to find Instagram Business account
        $pagesResp = $this->httpRequest(
            self::GRAPH_URL . '/me/accounts?fields=id,name,instagram_business_account',
            'GET',
            ['Authorization: Bearer ' . $userToken]
        );

        if ($pagesResp['code'] === 200) {
            $pagesData = json_decode($pagesResp['body'], true);
            $pages = $pagesData['data'] ?? [];

            foreach ($pages as $page) {
                if (!empty($page['instagram_business_account']['id'])) {
                    $igId = $page['instagram_business_account']['id'];

                    // Get page token
                    $pageTokenResp = $this->httpRequest(
                        self::GRAPH_URL . '/' . $page['id'] . '?fields=access_token',
                        'GET',
                        ['Authorization: Bearer ' . $userToken]
                    );

                    $pageToken = $userToken;
                    if ($pageTokenResp['code'] === 200) {
                        $ptData = json_decode($pageTokenResp['body'], true);
                        $pageToken = $ptData['access_token'] ?? $userToken;
                    }

                    // Get IG username
                    $igResp = $this->httpRequest(
                        self::GRAPH_URL . '/' . $igId . '?fields=username,name',
                        'GET',
                        ['Authorization: Bearer ' . $pageToken]
                    );

                    $igUsername = '';
                    if ($igResp['code'] === 200) {
                        $igData = json_decode($igResp['body'], true);
                        $igUsername = $igData['username'] ?? '';
                    }

                    $db = \core\Database::connection();
                    $db->prepare("UPDATE social_accounts SET account_name = ? WHERE platform = 'instagram' AND active = 1")
                       ->execute(['@' . ($igUsername ?: $igId)]);

                    $this->saveMeta([
                        'ig_user_id' => $igId,
                        'page_id' => $page['id'],
                        'page_access_token' => $pageToken,
                        'ig_username' => $igUsername,
                    ]);

                    return true;
                }
            }

            error_log('[SOCIAL] Instagram: no Instagram Business account found on connected Facebook pages');
        }

        return true;
    }

    /**
     * Check if Instagram is connected
     */
    public function isConnected(): bool
    {
        $account = $this->getAccount();
        if (!$account || empty($account['access_token'])) {
            return false;
        }

        $igUserId = $this->getMeta('ig_user_id', '');
        if (empty($igUserId)) {
            return false;
        }

        if (!empty($account['token_expires'])) {
            $expires = strtotime($account['token_expires']);
            if ($expires !== false && $expires < time()) {
                return false;
            }
        }

        return true;
    }
}
