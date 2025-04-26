<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MCPVerificationService;

class MCPVerifyCommand extends Command
{
    protected $signature = 'mcp:verify {--fail-fast}';
    protected $description = 'Verify MCP server connectivity and functionality';

    public function handle(MCPVerificationService $verificationService)
    {
        $verificationService->setOutput($this->output);

        try {
            $result = $verificationService->verifyAll($this->option('fail-fast'));
            return $result ? 0 : 1;
        } catch (\Exception $e) {
            $this->error("Verification failed: " . $e->getMessage());
            return 1;
        }
    }
}