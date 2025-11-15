<?php
declare(strict_types=1);

namespace CMS\ContentLifecycle;

class StatusTransitionValidator {
    private const VALID_TRANSITIONS = [
        'draft' => ['published', 'archived'],
        'published' => ['draft', 'archived'],
        'archived' => ['published']
    ];

    public function isValidTransition(
        string $currentStatus,
        string $newStatus
    ): bool {
        if (!array_key_exists($currentStatus, self::VALID_TRANSITIONS)) {
            return false;
        }

        return in_array($newStatus, self::VALID_TRANSITIONS[$currentStatus], true);
    }

    public function getAllowedTransitions(string $currentStatus): array {
        return self::VALID_TRANSITIONS[$currentStatus] ?? [];
    }
}
