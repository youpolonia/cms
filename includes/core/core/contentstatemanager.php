<?php
declare(strict_types=1);

class ContentStateManager {
    private const STATES = [
        'draft' => 1,
        'review' => 2,
        'approved' => 3,
        'published' => 4,
        'archived' => 5,
        'deleted' => 6
    ];

    private const TRANSITIONS = [
        1 => [2], // draft -> review
        2 => [1, 3], // review -> draft or approved
        3 => [4], // approved -> published
        4 => [5, 1], // published -> archived or draft
        5 => [4, 6], // archived -> published or deleted
        6 => [] // deleted (terminal)
    ];

    public static function isValidTransition(int $fromStateId, int $toStateId): bool {
        return in_array($toStateId, self::TRANSITIONS[$fromStateId] ?? [], true);
    }

    public static function getStateId(string $stateName): ?int {
        return self::STATES[strtolower($stateName)] ?? null;
    }

    public static function getStateName(int $stateId): ?string {
        return array_flip(self::STATES)[$stateId] ?? null;
    }

    public static function getAllStates(): array {
        return self::STATES;
    }

    public static function getAvailableTransitions(int $currentStateId): array {
        return self::TRANSITIONS[$currentStateId] ?? [];
    }
}
