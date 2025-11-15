<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

declare(strict_types=1);

namespace Services\Analytics;

final class Aggregator
{
    /**
     * Calculate sum of values for a metric
     */
    public static function sum(array $values): float
    {
        return array_sum(array_map('floatval', $values));
    }

    /**
     * Calculate average of values for a metric
     */
    public static function average(array $values): float
    {
        $count = count($values);
        return $count > 0 ? self::sum($values) / $count : 0;
    }

    /**
     * Find minimum value in a metric set
     */
    public static function min(array $values): float
    {
        return min(array_map('floatval', $values));
    }

    /**
     * Find maximum value in a metric set
     */
    public static function max(array $values): float
    {
        return max(array_map('floatval', $values));
    }

    /**
     * Compare metrics between two tenants
     */
    public static function compareTenants(
        array $tenantAValues,
        array $tenantBValues,
        string $metric
    ): array {
        $aValue = self::calculateMetric($tenantAValues, $metric);
        $bValue = self::calculateMetric($tenantBValues, $metric);

        return [
            'tenant_a' => $aValue,
            'tenant_b' => $bValue,
            'difference' => $aValue - $bValue,
            'percentage' => $bValue != 0 
                ? (($aValue - $bValue) / $bValue) * 100 
                : 0
        ];
    }

    private static function calculateMetric(array $values, string $metric): float
    {
        return match($metric) {
            'sum' => self::sum($values),
            'avg' => self::average($values),
            'min' => self::min($values),
            'max' => self::max($values),
            default => 0
        };
    }

    /**
     * Calculate moving average over periods
     */
    public static function movingAverage(array $values, int $periods = 3): array
    {
        $result = [];
        $count = count($values);
        
        for ($i = 0; $i <= $count - $periods; $i++) {
            $slice = array_slice($values, $i, $periods);
            $result[] = self::average($slice);
        }
        
        return $result;
    }

    /**
     * Calculate period-over-period growth rate
     */
    public static function growthRate(array $currentPeriod, array $previousPeriod): float
    {
        $current = self::sum($currentPeriod);
        $previous = self::sum($previousPeriod);
        
        return $previous != 0
            ? (($current - $previous) / $previous) * 100
            : 0;
    }

    /**
     * Calculate compound annual growth rate (CAGR)
     */
    public static function cagr(
        float $beginningValue,
        float $endingValue,
        int $years
    ): float {
        return $years > 0 && $beginningValue > 0
            ? (pow(($endingValue / $beginningValue), (1 / $years)) - 1) * 100
            : 0;
    }

    /**
     * Export data to CSV format
     */
    public static function exportToCsv(array $data, string $filename = 'export.csv'): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export data to JSON format
     */
    public static function exportToJson(array $data, string $filename = 'export.json', bool $pretty = false): void
    {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $pretty
            ? json_encode($data, JSON_PRETTY_PRINT)
            : json_encode($data);
        
        exit;
    }
}
