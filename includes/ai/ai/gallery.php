<?php
declare(strict_types=1);

namespace CMS\AI;

use CMS\WorkerMonitoringService;

final class Gallery
{
    const QUANT_SCALE = 0.0078125;
    const ZERO_POINT = 128;
    
    private static $quantizedWeights;
    private static $activationBuffer;

    public static function initQuantization(): void
    {
        self::$quantizedWeights = new \SplFixedArray(0);
        self::$activationBuffer = new \SplFixedArray(0);
        
        WorkerMonitoringService::logInferenceMetrics(
            'quant_init',
            memory_get_usage(true),
            microtime(true)
        );
    }

    public static function loadWeights(string $weightPath): void
    {
        // Validate available memory before loading
        $fileSize = filesize($weightPath);
        $requiredMemory = $fileSize * 2; // Buffer for processing
        $availableMemory = self::getAvailableMemory();
        
        if ($requiredMemory > $availableMemory) {
            WorkerMonitoringService::logError(
                'Insufficient memory for weights',
                [
                    'required' => $requiredMemory,
                    'available' => $availableMemory,
                    'file_size' => $fileSize
                ]
            );
            throw new \RuntimeException('Insufficient memory for model weights');
        }

        WorkerMonitoringService::recordMemory('Before weight loading');
        
        // Adaptive chunk sizing based on available memory
        $optimalChunkSize = min(8192, (int)($availableMemory / 4));
        $handle = fopen($weightPath, 'rb');
        $weights = [];
        $startTime = microtime(true);
        
        try {
            while (!feof($handle)) {
                $buffer = fread($handle, $optimalChunkSize);
                $bytes = unpack('C*', $buffer);
                $weights = array_merge($weights, $bytes);
                
                // Periodic memory check
                if (count($weights) % 10000 === 0) {
                    $currentUsage = memory_get_usage(true);
                    if ($currentUsage > $availableMemory * 0.8) {
                        $optimalChunkSize = max(1024, (int)($optimalChunkSize * 0.5));
                    }
                }
            }
            
            self::$quantizedWeights = \SplFixedArray::fromArray($weights);
            unset($weights);
            
            WorkerMonitoringService::recordMemory('After weight loading');
            WorkerMonitoringService::logInferenceMetrics(
                'weight_loading',
                microtime(true) - $startTime,
                memory_get_peak_usage(true)
            );
        } finally {
            fclose($handle);
        }
    }

    public static function quantizeActivations(array $activations): \SplFixedArray
    {
        $quantized = new \SplFixedArray(count($activations));
        foreach ($activations as $i => $val) {
            $quantized[$i] = (int)round(
                ($val / self::QUANT_SCALE) + self::ZERO_POINT
            );
        }
        return $quantized;
    }

    private static function getAvailableMemory(): int
    {
        $limit = ini_get('memory_limit');
        $used = memory_get_usage(true);
        
        if (preg_match('/^(\d+)(.)$/', $limit, $matches)) {
            $value = (int)$matches[1];
            $unit = strtoupper($matches[2]);
            
            switch ($unit) {
                case 'G': $value *= 1024 * 1024 * 1024; break;
                case 'M': $value *= 1024 * 1024; break;
                case 'K': $value *= 1024; break;
            }
            
            return max(0, $value - $used - (10 * 1024 * 1024)); // 10MB buffer
        }
        
        return 128 * 1024 * 1024; // Default 128MB if can't parse
    }
}
