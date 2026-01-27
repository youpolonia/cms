<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Includes\Models\PreferenceModel;
use CMS\Includes\Models\UserModel;

class PreferenceManager {
    private PreferenceModel $preferenceModel;
    private UserModel $userModel;

    public function __construct(
        PreferenceModel $preferenceModel,
        UserModel $userModel
    ) {
        $this->preferenceModel = $preferenceModel;
        $this->userModel = $userModel;
    }

    /**
     * Get user preferences (both explicit and inferred)
     */
    public function getUserPreferences(int $userId): array {
        $explicit = $this->preferenceModel->getExplicitPreferences($userId);
        $inferred = $this->preferenceModel->getInferredPreferences($userId);
        
        return array_merge($explicit, $inferred);
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(int $userId, array $preferences): bool {
        return $this->preferenceModel->updatePreferences($userId, $preferences);
    }

    /**
     * Reset preferences to defaults
     */
    public function resetPreferences(int $userId): bool {
        return $this->preferenceModel->resetToDefaults($userId);
    }

    /**
     * Inherit preferences from another user (e.g. team lead)
     */
    public function inheritPreferences(int $targetUserId, int $sourceUserId): bool {
        $preferences = $this->getUserPreferences($sourceUserId);
        return $this->updatePreferences($targetUserId, $preferences);
    }

    /**
     * Get preference tracking opt-in status
     */
    public function getTrackingStatus(int $userId): bool {
        return $this->preferenceModel->getTrackingStatus($userId);
    }

    /**
     * Set preference tracking opt-in status
     */
    public function setTrackingStatus(int $userId, bool $status): bool {
        return $this->preferenceModel->setTrackingStatus($userId, $status);
    }
}
