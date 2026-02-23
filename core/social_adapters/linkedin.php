<?php
declare(strict_types=1);

/**
 * LinkedIn Social Media Adapter
 * Uses LinkedIn API v2 for posting (ugcPosts / posts)
 * OAuth 2.0 three-legged flow
 */

require_once __DIR__ . '/base.php';

class LinkedInAdapter extends SocialAdapter
{
    protected string $platform = 'linkedin';

    private const AUTH_URL = 'https://www.linkedin.com/oauth/v2/authorization';
    private const TOKEN_URL = 'https://www.linkedin.com/oauth/v2/accessToken';
    private const API_BASE = 'https://api.linkedin.com/v2';

    /**
     * Publish a post to LinkedIn
     */
    public function publish(string $content, ?string $mediaUrl = null, ?string $linkUrl = null): array
    {
        $account = $this->getAccount();
        if (!$account || empty($account['access_token'])) {
            return ['ok' => false, 'external_id' => null, 'error' => 'LinkedIn not connected'];
        }

        $personUrn = $this->getMeta('person_urn', '');
        if (empty($personUrn)) {
            // Try to fetch profile URN
            $personUrn = $this->fetchPersonUrn($account['access_token']);
            if (empty($personUrn)) {
                return ['ok' => false, 'external_id' => null, 'error' => 'Could not determine LinkedIn person URN'];
            }
        }

        // Append link if provided
        if ($linkUrl !== null && $linkUrl !== '') {
            $content .= "\n\n" . $linkUrl;
        }

        // Build UGC Post payload
        $payload = [
            'author' => $personUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => [
                        'text' => $content,
                    ],
                    'shareMediaCategory' => 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        // If we have a link, change category to ARTICLE
        if ($linkUrl !== null && $linkUrl !== '') {
            $payload['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory'] = 'ARTICLE';
            $payload['specificContent']['com.linkedin.ugc.ShareContent']['media'] = [
                [
                    'status' => 'READY',
                    'originalUrl' => $linkUrl,
                ],
            ];
        }

        $resp = $this->httpRequest(
            self::API_BASE . '/ugcPosts',
            'POST',
            [
                'Authorization: Bearer ' . $account['access_token'],
                'Content-Type: application/json',
                'X-Restli-Protocol-Version: 2.0.0',
            ],
            $payload
        );

        if ($resp['error']) {
            return ['ok' => false, 'external_id' => null, 'error' => $resp['error']];
        }

        $data = json_decode($resp['body'], true);

        if ($resp['code'] === 201 && !empty($data['id'])) {
            return ['ok' => true, 'external_id' => $data['id'], 'error' => null];
        }

        $errorMsg = $data['message'] ?? ('HTTP ' . $resp['code']);
        return ['ok' => false, 'external_id' => null, 'error' => 'LinkedIn API: ' . $errorMsg];
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
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $callbackUrl,
            'scope' => 'openid profile w_member_social',
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
            error_log('[SOCIAL] LinkedIn: client_id or client_secret not configured');
            return false;
        }

        $payload = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $callbackUrl,
        ]);

        $resp = $this->httpRequest(
            self::TOKEN_URL,
            'POST',
            ['Content-Type: application/x-www-form-urlencoded'],
            $payload
        );

        if ($resp['error'] || $resp['code'] !== 200) {
            error_log('[SOCIAL] LinkedIn token exchange failed: ' . ($resp['error'] ?? $resp['body']));
            return false;
        }

        $data = json_decode($resp['body'], true);
        if (empty($data['access_token'])) {
            return false;
        }

        $expires = null;
        if (!empty($data['expires_in'])) {
            $expires = date('Y-m-d H:i:s', time() + (int)$data['expires_in']);
        }

        $this->saveToken($data['access_token'], $data['refresh_token'] ?? null, $expires);

        // Fetch and save person URN and name
        $personUrn = $this->fetchPersonUrn($data['access_token']);
        if ($personUrn) {
            $this->saveMeta(['person_urn' => $personUrn]);
        }

        return true;
    }

    /**
     * Fetch the LinkedIn person URN
     */
    private function fetchPersonUrn(string $accessToken): string
    {
        $resp = $this->httpRequest(
            self::API_BASE . '/userinfo',
            'GET',
            ['Authorization: Bearer ' . $accessToken]
        );

        if ($resp['code'] === 200) {
            $data = json_decode($resp['body'], true);
            if (!empty($data['sub'])) {
                $urn = 'urn:li:person:' . $data['sub'];

                // Also update account name
                $name = trim(($data['given_name'] ?? '') . ' ' . ($data['family_name'] ?? ''));
                if ($name !== '') {
                    $db = \core\Database::connection();
                    $db->prepare("UPDATE social_accounts SET account_name = ? WHERE platform = 'linkedin' AND active = 1")
                       ->execute([$name]);
                }

                return $urn;
            }
        }

        return '';
    }

    /**
     * Check if LinkedIn is connected
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
