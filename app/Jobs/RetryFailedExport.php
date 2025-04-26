<?php

namespace App\Jobs;

use App\Models\ExportHistory;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\ExportRetryNotification;

class RetryFailedExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [30, 60, 120];

    protected $history;
    protected $user;

    public function __construct(ExportHistory $history, User $user)
    {
        $this->history = $history;
        $this->user = $user;
    }

    public function handle()
    {
        try {
            // Get original export parameters
            $template = $this->history->template;
            $parameters = $this->history->parameters;
            
            // Execute the export
            $result = $template->executeExport($parameters);
            
            // Update history record
            $this->history->update([
                'status' => 'success',
                'file_path' => $result['path'],
                'file_size' => $result['size'],
                'duration' => $result['duration'],
                'retry_of' => $this->history->id,
                'error_log' => null
            ]);

            // Notify user
            $this->user->notify(new ExportRetryNotification(
                $this->history,
                true,
                'Export retry completed successfully'
            ));

        } catch (\Exception $e) {
            // Update history with new error
            $this->history->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
                'retry_count' => $this->history->retry_count + 1
            ]);

            // Notify user
            $this->user->notify(new ExportRetryNotification(
                $this->history,
                false,
                $e->getMessage()
            ));

            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        $this->history->update([
            'status' => 'failed',
            'error_log' => $exception->getMessage(),
            'retry_count' => $this->history->retry_count + 1
        ]);
    }
}