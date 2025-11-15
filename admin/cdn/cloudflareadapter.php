<?php

require_once __DIR__ . '/cdnadapter.php';

class CloudflareAdapter implements CdnAdapter {
    private string $accountId;
    private string $apiToken;
    private string $namespaceId;
    private string $cdnUrl;

    public function __construct(string $accountId, string $apiToken, string $namespaceId, string $cdnUrl) {
        $this->accountId = $accountId;
        $this->apiToken = $apiToken;
        $this->namespaceId = $namespaceId;
        $this->cdnUrl = rtrim($cdnUrl, '/');
    }

    public function upload(string $localPath, string $cdnPath): bool {
        $ch = curl_init();
        $url = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/storage/kv/namespaces/{$this->namespaceId}/values/" . urlencode($cdnPath);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => file_get_contents($localPath),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->apiToken}",
                "Content-Type: application/octet-stream"
            ]
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status === 200;
    }

    public function delete(string $cdnPath): bool {
        $ch = curl_init();
        $url = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/storage/kv/namespaces/{$this->namespaceId}/values/" . urlencode($cdnPath);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->apiToken}"
            ]
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status === 200;
    }

    public function exists(string $cdnPath): bool {
        $ch = curl_init();
        $url = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/storage/kv/namespaces/{$this->namespaceId}/values/" . urlencode($cdnPath);
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->apiToken}"
            ]
        ]);

        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status === 200;
    }

    public function getUrl(string $cdnPath): string {
        return $this->cdnUrl . '/' . ltrim($cdnPath, '/');
    }
}
