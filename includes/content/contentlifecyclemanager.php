<?php declare(strict_types=1);

namespace CMS\Content;

use CMS\Workflow\WorkflowHistory;
use CMS\Middleware\ActivityTracker;
use CMS\Content\Exception\InvalidStateTransitionException;

final class ContentLifecycleManager
{
    private const STATE_TRANSITIONS = [
        'draft' => ['review', 'archived'],
        'review' => ['published', 'draft'],
        'published' => ['updated', 'archived'],
        'updated' => ['published', 'archived'],
        'archived' => ['restored']
    ];

    public function __construct(
        private WorkflowHistory $workflowHistory,
        private ActivityTracker $activityTracker
    ) {}

    /**
     * @throws InvalidStateTransitionException
     */
    public function transitionState(string $currentState, string $newState, int $contentId): void
    {
        if (!$this->isValidTransition($currentState, $newState)) {
            $this->activityTracker->logInvalidTransitionAttempt($contentId, $currentState, $newState);
            throw new InvalidStateTransitionException("Invalid transition from $currentState to $newState");
        }

        $this->workflowHistory->recordTransition($contentId, $currentState, $newState);
        $this->activityTracker->logStateChange($contentId, $newState);
    }

    public function scheduleContentPublication(int $contentId, \DateTimeInterface $publishTime): void
    {
        $this->workflowHistory->recordScheduledEvent($contentId, 'publication', $publishTime);
    }

    public function handleGDPRAnonymization(int $contentId, string $csrfToken): void
    {
        if (!SecurityService::validateCSRFToken($csrfToken, 'gdpr', true)) {
            throw new SecurityException('Invalid CSRF token for GDPR operation');
        }

        $this->activityTracker->logAnonymizationAttempt($contentId);
        // Actual implementation would go here
    }

    private function isValidTransition(string $current, string $new): bool
    {
        return in_array($new, self::STATE_TRANSITIONS[$current] ?? [], true);
    }

    public function registerSchedulingHook(string $hookName, callable $callback): void
    {
        // Hook registration logic would be implemented here
    }

    public function applyContentRetentionPolicy(int $contentId): void
    {
        // Retention policy application stub
    }
}
