<?php
declare(strict_types=1);

class FederationService {
    private static string $publicKey = '';
    private static string $privateKey = '';

    public static function verifySignature(string $data, string $signature, string $publicKey): bool {
        if (empty($publicKey)) {
            throw new InvalidArgumentException('Public key cannot be empty');
        }

        $result = openssl_verify(
            $data,
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        return $result === 1;
    }

    public static function generateSignature(string $data): string {
        if (empty(self::$privateKey)) {
            throw new RuntimeException('Private key not configured');
        }

        $signature = '';
        openssl_sign(
            $data,
            $signature,
            self::$privateKey,
            OPENSSL_ALGO_SHA256
        );

        return base64_encode($signature);
    }

    public static function setKeys(string $publicKey, string $privateKey): void {
        self::$publicKey = $publicKey;
        self::$privateKey = $privateKey;
    }

    public static function getPublicKey(): string {
        return self::$publicKey;
    }
}
