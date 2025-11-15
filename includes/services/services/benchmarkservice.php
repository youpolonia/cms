<?php
declare(strict_types=1);

namespace Services;

class BenchmarkService
{
    private array $metrics = [];
    private ?string $tenantId = null;

    public function __construct(private array $config = [])
    {
        $this->config = array_merge(
            require_once __DIR__ . '/../../config/benchmark.php',
            $config
        );
    }

    public function measurePerformance(callable $operation, string $name): void
    {
        $start = microtime(true);
        $operation();
        $end = microtime(true);

        $this->metrics[$name] = [
            'time' => $end - $start,
            'memory' => memory_get_usage(true)
        ];
    }

    public function saveMetrics(): bool
    {
        if (empty($this->metrics)) {
            return false;
        }

        $filename = $this->config['storage_path'] . '/' 
                  . $this->config['file_prefix'] 
                  . date('Y-m-d_His') 
                  . $this->config['file_extension'];

        $data = [
            'timestamp' => time(),
            'tenant_id' => $this->tenantId,
            'metrics' => $this->metrics
        ];

        try {
            $json = json_encode($data, JSON_PRETTY_PRINT);
            if ($json === false) {
                return false;
            }
            
            $bytes = file_put_contents($filename, $json);
            return $bytes !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }
}
