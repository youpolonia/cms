<?php
declare(strict_types=1);

class ContentWorkflowSystem {
    private static array $workflows = [];
    private static array $states = [];
    private static array $transitions = [];

    public static function initialize(): void {
        self::loadCoreWorkflows();
    }

    private static function loadCoreWorkflows(): void {
        // Default workflow states
        self::$states = [
            'draft' => ['name' => 'Draft', 'color' => '#9e9e9e'],
            'review' => ['name' => 'Review', 'color' => '#ff9800'],
            'approved' => ['name' => 'Approved', 'color' => '#4caf50'],
            'published' => ['name' => 'Published', 'color' => '#2196f3'],
            'archived' => ['name' => 'Archived', 'color' => '#607d8b']
        ];

        // Default transitions
        self::$transitions = [
            'submit_for_review' => [
                'from' => ['draft'],
                'to' => 'review',
                'roles' => ['editor', 'author']
            ],
            'approve' => [
                'from' => ['review'],
                'to' => 'approved',
                'roles' => ['editor', 'admin']
            ],
            'publish' => [
                'from' => ['approved'],
                'to' => 'published',
                'roles' => ['publisher', 'admin']
            ],
            'archive' => [
                'from' => ['published'],
                'to' => 'archived',
                'roles' => ['editor', 'admin']
            ]
        ];
    }

    public static function getAvailableStates(): array {
        return self::$states;
    }

    public static function getAvailableTransitions(string $currentState): array {
        return array_filter(self::$transitions, fn($t) => in_array($currentState, $t['from']));
    }

    public static function isValidTransition(string $currentState, string $transition): bool {
        return isset(self::$transitions[$transition]) && 
               in_array($currentState, self::$transitions[$transition]['from']);
    }
}
