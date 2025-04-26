<?php

namespace App\Jobs;

use App\Models\ScheduledExport;
use App\Services\ReportExportService;
use App\Traits\ExportProcessingTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExportReadyNotification;

class ProcessScheduledExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ScheduledExport $scheduledExport)
    {
    }

    public function handle(ReportExportService $exportService)
    {
        try {
            // Generate the export
            $export = $exportService->export(
                $this->scheduledExport->template,
                $this->scheduledExport->format,
                $this->getExportData()
            );

            // Send notifications
            $this->sendNotifications($export);

            // Update last run time
            $this->scheduledExport->update([
                'last_run_at' => now(),
                'next_run_at' => $this->calculateNextRun()
            ]);

        } catch (\Exception $e) {
            // Log error and retry later
            logger()->error("Scheduled export failed: " . $e->getMessage());
            $this->release(60); // Retry after 1 minute
        }
    }

    use ExportProcessingTrait;

    protected function getAnalyticsData(): array
    {
        return [
            'period' => $this->scheduledExport->period,
            'metrics' => config('analytics.metrics'),
            'data' => \App\Models\AnalyticsData::query()
                ->whereBetween('created_at', $this->getDateRange())
                ->get()
                ->toArray()
        ];
    }

    protected function getContentApprovalData(): array
    {
        return [
            'period' => $this->scheduledExport->period,
            'content' => \App\Models\Content::query()
                ->with(['versions' => function($query) {
                    $query->whereBetween('reviewed_at', $this->getDateRange());
                }])
                ->get()
                ->toArray()
        ];
    }

    protected function getUserActivityData(): array
    {
        return [
            'period' => $this->scheduledExport->period,
            'users' => \App\Models\User::query()
                ->with(['activities' => function($query) {
                    $query->whereBetween('created_at', $this->getDateRange());
                }])
                ->get()
                ->toArray()
        ];
    }

    protected function sendNotifications($export)
    {
        foreach ($this->scheduledExport->recipients as $email) {
            Mail::to($email)->send(new ExportReadyNotification(
                $this->scheduledExport,
                $export
            ));
        }
    }

    public function failed(\Exception $exception)
    {
        $this->scheduledExport->update([
            'last_error' => $exception->getMessage(),
            'status' => 'failed'
        ]);

        // Send failure notification
        Mail::to($this->scheduledExport->owner)
            ->send(new \App\Mail\ExportFailedNotification(
                $this->scheduledExport,
                $exception
            ));
    }
}