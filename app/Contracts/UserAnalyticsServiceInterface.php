<?php

namespace App\Contracts;

interface UserAnalyticsServiceInterface
{
    public function trackUserActivity(int $userId, string $activityType): void;
    public function getUserAnalytics(int $userId): array;
    public function exportUserAnalytics(int $userId): string;
}