<?php
declare(strict_types=1);

class Notification {
    private string $type;
    private string $recipient;
    private string $message;

    public function __construct(string $type, string $recipient, string $message) {
        if (!in_array($type, ['alert', 'message', 'warning'])) {
            throw new InvalidArgumentException("Invalid notification type: $type");
        }

        $this->type = $type;
        $this->recipient = $recipient;
        $this->message = $message;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getRecipient(): string {
        return $this->recipient;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function deliver(): bool {
        if (str_starts_with($this->recipient, 'invalid_')) {
            throw new RuntimeException("Invalid recipient: {$this->recipient}");
        }
        return true;
    }
}
