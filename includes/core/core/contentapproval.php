<?php
declare(strict_types=1);

class ContentApproval {
    // State constants
    public const STATE_DRAFT = 'draft';
    public const STATE_REVIEW = 'review';
    public const STATE_APPROVED = 'approved';
    public const STATE_PUBLISHED = 'published';
    public const STATE_ARCHIVED = 'archived';
    public const STATE_REJECTED = 'rejected';

    private const STATES = [
        self::STATE_DRAFT,
        self::STATE_REVIEW,
        self::STATE_APPROVED,
        self::STATE_PUBLISHED,
        self::STATE_ARCHIVED,
        self::STATE_REJECTED
    ];

    private const TRANSITIONS = [
        self::STATE_DRAFT => [self::STATE_REVIEW, self::STATE_ARCHIVED],
        self::STATE_REVIEW => [self::STATE_APPROVED, self::STATE_REJECTED],
        self::STATE_APPROVED => [self::STATE_PUBLISHED, self::STATE_REJECTED],
        self::STATE_PUBLISHED => [self::STATE_ARCHIVED],
        self::STATE_REJECTED => [self::STATE_DRAFT, self::STATE_ARCHIVED],
        self::STATE_ARCHIVED => []
    ];

    public static function isValidTransition(
        string $currentState,
        string $newState
    ): bool {
        if (!in_array($currentState, self::STATES, true)) {
            return false;
        }

        return in_array($newState, self::TRANSITIONS[$currentState] ?? [], true);
    }

    public static function getAvailableStates(): array {
        return self::STATES;
    }

    public static function getNextStates(string $currentState): array {
        if (!in_array($currentState, self::STATES, true)) {
            return [];
        }

        return self::TRANSITIONS[$currentState] ?? [];
    }
}
