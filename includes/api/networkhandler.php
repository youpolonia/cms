<?php
declare(strict_types=1);

namespace CMS\API;

class NetworkHandler
{
    private const MAX_RETRIES = 3;
    private const INITIAL_DELAY_MS = 100;
    private const TIMEOUT_SECONDS = 30;

    public static function executeWithRetry(callable $request): mixed
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < self::MAX_RETRIES) {
            try {
                $context = stream_context_create([
                    'http' => ['timeout' => self::TIMEOUT_SECONDS]
                ]);
                
                return $request($context);
            } catch (\Exception $e) {
                $lastError = $e;
                $attempt++;
                
                if ($attempt < self::MAX_RETRIES) {
                    $delay = self::INITIAL_DELAY_MS * (2 ** ($attempt - 1));
                    usleep($delay * 1000);
                }
            }
        }

        throw new \RuntimeException(
            "Request failed after " . self::MAX_RETRIES . " attempts",
            0,
            $lastError
        );
    }

    public static function checkHeartbeat(string $url): bool
    {
        try {
            $response = self::executeWithRetry(
                fn() => file_get_contents($url, false, stream_context_create([
                    'http' => ['timeout' => 5]
                ]))
            );
            return $response !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
