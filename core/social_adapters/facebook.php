<?php
declare(strict_types=1);

/**
 * Facebook Social Media Adapter
 * Uses Facebook Graph API for posting to Pages
 * OAuth 2.0 flow
 */

require_once __DIR__ . '/base.php';

class FacebookAdapter extends SocialAdapter
{
    protected string $platform = 'facebook';

    private const AUTH_URL = 'https://www.facebook.com/v19.0/dialog/oauth';
    private const TOKEN_URL = 'https://graph.facebook.com/v19.0/oauth/access_token';
    private const GRAPH_URL = 'https://graph.facebook.com/v19.0';

    /**
     * Publish a post to Facebook Page
     */
    public function publish(string $content, ?string $mediaUrl = null, ?string $linkUrl = null): array
    {
        $account = $this->getAccount();
        if (!$account || empty($account['access_token'])) {
            return ['ok' => false, 'external_id' => null, 'error' => 'Facebook not connected'];
        }

        $pageId = $account['page_id'] ?? '';
        $pageToken = $this->getMeta('page_access_token', $account['access_token']);

        if (empty($pageId)) {
            return ['ok' => false, 'external_id' => null, 'error' => 'Facebook page_id not configured'];
        }

        $payload = ['message' => $content];
        if ($linkUrl !== null && $linkUrl !== '') {
            $payload['link'] = $linkUrl;
        }

        $resp = $this->httpRequest(
            self::GRAPH_URL . '/' . $pageId . '/feed',
            'POST',
            [
                'Authorization: Bearer ' . $pageToken,
                'Content-Type: application/json',
            ],
            $payload
        );

        if ($resp['error']) {
            return ['ok' => false, 'external_id' => null, 'error' => $resp['error']];
        }

        $data = json_decode($resp['body'], true);

        if ($resp['code'] === 200 && !empty($data['id'])) {
            return ['ok' => true, 'external_id' => $data['id'], 'error' => null];
        }

        $errorMsg = $data['error']['message'] ?? ('HTTP ' . $resp['code']);
        return ['ok' => false, 'external_id' => null, 'error' => 'Facebook API: ' . $errorMsg];
    }

    /**
     * Get OAuth 2.0 authorization URL
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
            'scope' => 'pages_manage_posts,pages_read_engagement,pages_show_list',
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
            error_log('[SOCIAL] Facebook: client_id or client_secret not configured');
            return false;
        }

        // Exchange code for short-lived user token
        $url = self::TOKEN_URL . '?' . http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $callbackUrl,
            'code' => $code,
        ]);

        $resp = $this->httpRequest($url, 'GET');

        if ($resp['error'] || $resp['code'] !== 200) {
            error_log('[SOCIAL] Facebook token exchange failed: ' . ($resp['error'] ?? $resp['body']));
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

        // Fetch pages and get the first page token
        $pagesResp = $this->httpRequest(
            self::GRAPH_URL . '/me/accounts',
            'GET',
            ['Authorization: Bearer ' . $userToken]
        );

        if ($pagesResp['code'] === 200) {
            $pagesData = json_decode($pagesResp['body'], true);
            $pages = $pagesData['data'] ?? [];
            if (!empty($pages[0])) {
                $page = $pages[0];
                $db = \core\Database::connection();
                $db->prepare("UPDATE social_accounts SET page_id = ?, account_name = ? WHERE platform = 'facebook' AND active = 1")
                   ->execute([$page['id'], $page['name'] ?? 'Facebook Page']);
                $this->saveMeta([
                    'page_access_token' => $page['access_token'] ?? '',
                    'page_name' => $page['name'] ?? '',
                    'available_pages' => array_map(function ($p) {
                        return ['id' => $p['id'], 'name' => $p['name'] ?? ''];
                    }, $pages),
                ]);
            }
        }

        return true;
    }

    /**
     * Check if Facebook is connected
     */
    public function isConnected(): bool
    {
        $account = $this->getAccount();
        if (!$account || empty($account['access_token'])) {
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
