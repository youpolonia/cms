<?php

namespace App\Services;

use Illuminate\Http\Request;

class SSEServerTransport
{
    public function handle(Request $request)
    {
        // Initialize SSE connection
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        @ob_end_clean();
        
        set_time_limit(0);
    }

    public function process()
    {
        while (true) {
            $this->sendEvent('ping', ['time' => now()->toDateTimeString()]);
            sleep(5);
        }
    }

    protected function sendEvent(string $event, array $data = [])
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }
}