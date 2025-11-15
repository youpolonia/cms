<?php

require_once __DIR__ . '/cdnadapter.php';

class S3Adapter implements CdnAdapter {
    private string $accessKey;
    private string $secretKey;
    private string $region;
    private string $bucket;
    private string $cdnUrl;
    private string $endpoint;

    public function __construct(
        string $accessKey,
        string $secretKey,
        string $region,
        string $bucket,
        string $cdnUrl,
        string $endpoint = ''
    ) {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region = $region;
        $this->bucket = $bucket;
        $this->cdnUrl = rtrim($cdnUrl, '/');
        $this->endpoint = $endpoint;
    }

    public function upload(string $localPath, string $cdnPath): bool {
        $s3 = $this->getS3Client();
        try {
            $s3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $cdnPath,
                'SourceFile' => $localPath,
                'ACL' => 'public-read'
            ]);
            return true;
        } catch (Exception $e) {
            error_log("S3 upload failed: " . $e->getMessage());
            return false;
        }
    }

    public function delete(string $cdnPath): bool {
        $s3 = $this->getS3Client();
        try {
            $s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $cdnPath
            ]);
            return true;
        } catch (Exception $e) {
            error_log("S3 delete failed: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $cdnPath): bool {
        $s3 = $this->getS3Client();
        try {
            $s3->headObject([
                'Bucket' => $this->bucket,
                'Key' => $cdnPath
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUrl(string $cdnPath): string {
        return $this->cdnUrl . '/' . ltrim($cdnPath, '/');
    }

    private function getS3Client(): Aws\S3\S3Client {
        $config = [
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => [
                'key' => $this->accessKey,
                'secret' => $this->secretKey
            ]
        ];

        if (!empty($this->endpoint)) {
            $config['endpoint'] = $this->endpoint;
            $config['use_path_style_endpoint'] = true;
        }

        return new Aws\S3\S3Client($config);
    }
}
