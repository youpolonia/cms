<?php
class NotificationService {
    private static $instance;
    private $pdo;
    private $channels = [];
    private $maxRetries = 3;

    private function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public static function getInstance(\PDO $pdo): self {
        if (!isset(self::$instance)) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function registerChannel(string $name, callable $sender): void {
        $this->channels[$name] = $sender;
    }

    public function sendNotification(
        string $channel,
        array $recipients,
        string $message,
        array $options = []
    ): bool {
        if (!isset($this->channels[$channel])) {
            throw new \InvalidArgumentException("Channel $channel not registered");
        }

        $attempt = 0;
        $success = false;

        while ($attempt < $this->maxRetries && !$success) {
            try {
                $success = $this->channels[$channel]($recipients, $message, $options);
                $attempt++;
            } catch (\Exception $e) {
                error_log("Notification failed (attempt $attempt): " . $e->getMessage());
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                sleep(1 << $attempt); // Exponential backoff
            }
        }

        return $success;
    }

    public function getAvailableChannels(): array {
        return array_keys($this->channels);
    }
}
