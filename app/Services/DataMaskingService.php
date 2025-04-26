<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DataMaskingService
{
    protected $maskingStrategies = [
        'full' => [self::class, 'fullMask'],
        'partial' => [self::class, 'partialMask'],
        'email' => [self::class, 'emailMask'],
        'credit_card' => [self::class, 'creditCardMask'],
        'phone' => [self::class, 'phoneMask'],
        'custom' => [self::class, 'customMask']
    ];

    protected $policies = [];

    /**
     * Mask data according to field policies
     */
    public function maskData(array $data, string $context = 'default'): array
    {
        if (!isset($this->policies[$context])) {
            return $data;
        }

        $maskedData = [];
        $user = Auth::user();

        foreach ($data as $field => $value) {
            if (isset($this->policies[$context][$field])) {
                $policy = $this->policies[$context][$field];

                // Check role-based access first
                if (isset($policy['roles']) && $user && $user->hasAnyRole($policy['roles'])) {
                    $maskedData[$field] = $value;
                    continue;
                }

                // Apply masking if no role access
                $strategy = $policy['strategy'] ?? 'full';
                $options = $policy['options'] ?? [];

                $maskedData[$field] = $this->applyMaskingStrategy($value, $strategy, $options);
            } else {
                $maskedData[$field] = $value;
            }
        }

        return $maskedData;
    }

    protected function applyMaskingStrategy($value, string $strategy, array $options = [])
    {
        if (!isset($this->maskingStrategies[$strategy])) {
            return $this->fullMask($value);
        }

        return call_user_func($this->maskingStrategies[$strategy], $value, $options);
    }

    /**
     * Masking strategies
     */
    public static function fullMask($value): string
    {
        return '********';
    }

    public static function partialMask($value, array $options = []): string
    {
        $visibleChars = $options['visible'] ?? 4;
        $maskChar = $options['char'] ?? '*';
        $fromEnd = $options['from_end'] ?? false;

        $length = strlen($value);
        if ($length <= $visibleChars) {
            return str_repeat($maskChar, $length);
        }

        if ($fromEnd) {
            return substr($value, 0, $length - $visibleChars) . str_repeat($maskChar, $visibleChars);
        }

        return substr($value, 0, $visibleChars) . str_repeat($maskChar, $length - $visibleChars);
    }

    public static function emailMask($value): string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return self::fullMask($value);
        }

        list($local, $domain) = explode('@', $value, 2);
        $maskedLocal = substr($local, 0, 1) . '****' . substr($local, -1);
        return $maskedLocal . '@' . $domain;
    }

    public static function creditCardMask($value): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        $length = strlen($cleaned);

        if ($length < 12 || $length > 19) {
            return self::fullMask($value);
        }

        return substr($cleaned, 0, 4) . ' **** **** ' . substr($cleaned, -4);
    }

    public static function phoneMask($value, array $options = []): string
    {
        $countryCode = $options['country_code'] ?? null;
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        // Handle country-specific masking
        if ($countryCode === 'US' || $countryCode === 'CA') {
            if (strlen($cleaned) === 10) {
                return '***-***-' . substr($cleaned, -4);
            }
        }

        // Default international masking
        return substr($cleaned, 0, 3) . '****' . substr($cleaned, -3);
    }

    public static function customMask($value, array $options = []): string
    {
        $pattern = $options['pattern'] ?? null;
        $replacement = $options['replacement'] ?? '*';

        if ($pattern) {
            return preg_replace($pattern, $replacement, $value);
        }

        return self::fullMask($value);
    }

    /**
     * Add or update a masking policy
     */
    public function addPolicy(string $context, array $policy): void
    {
        $this->policies[$context] = array_merge(
            $this->policies[$context] ?? [],
            $policy
        );
    }

    /**
     * Remove a masking policy
     */
    public function removePolicy(string $context, ?string $field = null): void
    {
        if ($field) {
            unset($this->policies[$context][$field]);
        } else {
            unset($this->policies[$context]);
        }
    }

    /**
     * Add a custom masking strategy
     */
    public function addStrategy(string $name, callable $strategy): void
    {
        $this->maskingStrategies[$name] = $strategy;
    }

    /**
     * Check if a field should be masked
     */
    public function shouldMask(string $field, string $context = 'default'): bool
    {
        return isset($this->policies[$context][$field]);
    }

    /**
     * Get masking configuration for a field
     */
    public function getFieldConfig(string $field, string $context = 'default'): ?array
    {
        return $this->policies[$context][$field] ?? null;
    }

    /**
     * Bulk load policies from config
     */
    public function loadPolicies(array $policies): void
    {
        $this->policies = $policies;
    }

    /**
     * Get all policies
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * Get all strategies
     */
    public function getStrategies(): array
    {
        return array_keys($this->maskingStrategies);
    }
}