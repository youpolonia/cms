<?php
declare(strict_types=1);

namespace Api\Gateway;

class Logger
{
    private string $logDir;
    private string $logFile;

    public function __construct(string $logDir = __DIR__ . '/logs')
    {
        $this->logDir = $logDir;
        $this->logFile = $logDir . '/gateway_' . date('Y-m-d') . '.log';
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function logRequest(array $request, array $response): void
    {
        $entry = sprintf(
            "[%s] %s %s - %d\n",
            date('Y-m-d H:i:s'),
            $request['method'],
            $request['path'],
            $response['code']
        );
        
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }

    public function logError(\Throwable $e): void
    {
        $entry = sprintf(
            "[%s] ERROR %s:%d - %s\n",
            date('Y-m-d H:i:s'),
            $e->getFile(),
            $e->getLine(),
            $e->getMessage()
        );
        
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }
}
