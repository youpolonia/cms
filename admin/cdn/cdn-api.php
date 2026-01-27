<?php

require_once __DIR__ . '/cdnadapter.php';
require_once __DIR__ . '/cloudflareadapter.php';
require_once __DIR__ . '/s3adapter.php';

class CdnApi {
    private CdnAdapter $adapter;

    public function __construct(string $adapterType, array $config) {
        switch ($adapterType) {
            case 'cloudflare':
                $this->adapter = new CloudflareAdapter(
                    $config['account_id'],
                    $config['api_token'],
                    $config['namespace_id'],
                    $config['cdn_url']
                );
                break;
            case 's3':
                $this->adapter = new S3Adapter(
                    $config['access_key'],
                    $config['secret_key'],
                    $config['region'],
                    $config['bucket'],
                    $config['cdn_url'],
                    $config['endpoint'] ?? ''
                );
                break;
            default:
                throw new InvalidArgumentException("Invalid CDN adapter type: $adapterType");
        }
    }

    public function uploadFile(string $localPath, string $cdnPath): array {
        $success = $this->adapter->upload($localPath, $cdnPath);
        return [
            'success' => $success,
            'url' => $success ? $this->adapter->getUrl($cdnPath) : null
        ];
    }

    public function deleteFile(string $cdnPath): bool {
        return $this->adapter->delete($cdnPath);
    }

    public function fileExists(string $cdnPath): bool {
        return $this->adapter->exists($cdnPath);
    }

    public function getFileUrl(string $cdnPath): string {
        return $this->adapter->getUrl($cdnPath);
    }
}

// Example usage:
// $cdn = new CdnApi('cloudflare', [
//     'account_id' => 'your_account_id',
//     'api_token' => 'your_api_token',
//     'namespace_id' => 'your_namespace_id',
//     'cdn_url' => 'https://your.cdn.url'
// ]);
// $result = $cdn->uploadFile('/local/path/file.jpg', 'images/file.jpg');
