<?php
declare(strict_types=1);

/**
 * Content Management - Content Lifecycle
 * Manages automated content workflows and state transitions
 */
class ContentLifecycle {
    private static array $states = [
        'draft',
        'review',
        'approved',
        'published',
        'archived'
    ];

    private static array $transitions = [
        'draft' => ['review'],
        'review' => ['draft', 'approved'],
        'approved' => ['published'],
        'published' => ['archived'],
        'archived' => ['draft']
    ];

    private static array $workflows = [];

    /**
     * Transition content to new state
     */
    public static function transition(
        string $contentId,
        string $newState,
        string $userId
    ): bool {
        $currentState = self::getCurrentState($contentId);
        
        if (!self::isValidTransition($currentState, $newState)) {
            throw new InvalidArgumentException(
                "Invalid transition from $currentState to $newState"
            );
        }

        self::$workflows[$contentId] = [
            'state' => $newState,
            'last_updated' => time(),
            'updated_by' => $userId
        ];

        self::logTransition($contentId, $currentState, $newState, $userId);
        
        // Trigger notifications for state changes
        NotificationTriggers::contentStateChanged(
            $contentId,
            $currentState,
            $newState,
            $userId
        );
        
        return true;
    }

    /**
     * Get valid transitions for current state
     */
    public static function getAvailableTransitions(string $contentId): array {
        $currentState = self::getCurrentState($contentId);
        return self::$transitions[$currentState] ?? [];
    }

    private static function isValidTransition(
        string $currentState,
        string $newState
    ): bool {
        return in_array($newState, self::$transitions[$currentState] ?? []);
    }

    private static function getCurrentState(string $contentId): string {
        return self::$workflows[$contentId]['state'] ?? 'draft';
    }

    private static function logTransition(
        string $contentId,
        string $fromState,
        string $toState,
        string $userId
    ): void {
        file_put_contents(
            __DIR__ . '/../logs/content_transitions.log',
            sprintf(
                "[%s] %s: %s -> %s by %s\n",
                date('Y-m-d H:i:s'),
                $contentId,
                $fromState,
                $toState,
                $userId
            ),
            FILE_APPEND
        );
    }

    // BREAKPOINT: Continue with workflow automation features
}
