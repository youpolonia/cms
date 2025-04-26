<?php

namespace App\Services;

use App\Models\AnalyticsExport;

class ExportAnonymizer
{
    public function shouldAnonymize(AnalyticsExport $export): bool
    {
        return $export->anonymize || config('analytics.default_anonymize');
    }

    public function anonymize(array $data, array $options = []): array
    {
        $options = array_merge([
            'remove_emails' => true,
            'hash_user_ids' => true,
            'generalize_dates' => false,
        ], $options);

        if ($options['remove_emails']) {
            unset($data['email']);
        }

        if ($options['hash_user_ids'] && isset($data['user_id'])) {
            $data['user_id'] = hash('sha256', $data['user_id'] . config('app.key'));
        }

        if ($options['generalize_dates'] && isset($data['viewed_at'])) {
            $date = new \DateTime($data['viewed_at']);
            $data['viewed_at'] = $date->format('Y-m-d'); // Remove time component
        }

        return $data;
    }

    public function getDefaultOptions(): array
    {
        return [
            'remove_emails' => true,
            'hash_user_ids' => true,
            'generalize_dates' => false,
            'remove_ip_addresses' => true,
        ];
    }
}