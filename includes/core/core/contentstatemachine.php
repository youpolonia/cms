<?php
declare(strict_types=1);

class ContentStateMachine {
    private const VALID_TRANSITIONS = [
        'draft' => ['published', 'archived'],
        'published' => ['updated', 'archived'],
        'updated' => ['published', 'archived'],
        'archived' => ['published']
    ];

    public static function canTransition(
        string $currentState,
        string $newState
    ): bool {
        return in_array($newState, self::VALID_TRANSITIONS[$currentState] ?? []);
    }

    public static function transition(
        string $currentState,
        string $newState,
        array $content
    ): array {
        if (!self::canTransition($currentState, $newState)) {
            throw new LogicException("Invalid state transition");
        }

        $content['state'] = $newState;
        $content['state_changed_at'] = time();
        return $content;
    }
}
