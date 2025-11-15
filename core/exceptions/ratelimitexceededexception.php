<?php

class RateLimitExceededException extends Exception {
    public function __construct(string $message = "Rate limit exceeded", int $code = 429) {
        parent::__construct($message, $code);
    }
}
