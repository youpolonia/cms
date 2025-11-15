<?php
class RateLimiter {
    private string $key;
    private int $maxRequests;
    private int $timeFrame;

    public function __construct(string $key, int $maxRequests, int $timeFrame) {
        $this->key = $key;
        $this->maxRequests = $maxRequests;
        $this->timeFrame = $timeFrame;
    }

    public function checkLimit(): bool {
        if (!isset($_SESSION['rate_limits'][$this->key])) {
            $_SESSION['rate_limits'][$this->key] = [
                'count' => 1,
                'timestamp' => time()
            ];
            return true;
        }

        $limit = &$_SESSION['rate_limits'][$this->key];

        if (time() - $limit['timestamp'] > $this->timeFrame) {
            $limit['count'] = 1;
            $limit['timestamp'] = time();
            return true;
        }

        if ($limit['count'] >= $this->maxRequests) {
            return false;
        }

        $limit['count']++;
        return true;
    }
}
