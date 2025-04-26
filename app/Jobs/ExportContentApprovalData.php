<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use App\Notifications\AnalyticsExportReady;
use Carbon\Carbon;
use App\Services\ContentApprovalAnalyticsService;
use App\Exports\ContentApprovalAnalyticsExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportContentApprovalData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $type;
    protected $startDate;
    protected $endDate;
    protected $workflowId;
    protected $contentType;

    public function __construct(
        $userId,
        $type = 'csv',
        $startDate = null,
        $endDate = null,
        $workflowId = null,
        $contentType = null
    ) {
        $this->userId = $userId;
        $this->type = $type;
        $this->startDate = $startDate ? Carbon::parse($startDate) : null;
        $this->endDate = $endDate ? Carbon::parse($endDate) : null;
        $this->workflowId = $workflowId;
        $this->contentType = $contentType;
    }

    public function handle(ContentApprovalAnalyticsService $analyticsService)
    {
        try {
            $filename = 'content-approval-export-'.Carbon::now()->format('Y-m-d-H-i-s').'.'.$this->type;
            $path = 'exports/'.$filename;

            $user = User::findOrFail($this->userId);
            $export = $user->analyticsExports()->create([
                'file_path' => $path,
                'status' => 'processing',
                'expires_at' => now()->addHours(24)
            ]);

            $workflow = $this->workflowId 
                ? ApprovalWorkflow::findOrFail($this->workflowId)
                : null;

            $data = $analyticsService->getWorkflowMetrics($workflow ?? ApprovalWorkflow::first());
            
            if ($this->type === 'xlsx') {
                Excel::store(
                    new ContentApprovalAnalyticsExport($data),
                    $path
                );
            } else {
                $fileContent = $this->type === 'json' 
                    ? $this->formatAsJson($data)
                    : $this->formatAsCsv($data);
                Storage::put($path, $fileContent);
            }

            $export->update([
                'status' => 'completed',
                'file_size' => Storage::size($path)
            ]);
            
            $user->notify(new AnalyticsExportReady($export));
        } catch (\Exception $e) {
            if (isset($export)) {
                $export->update([
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    protected function formatAsCsv($data)
    {
        $output = "Content Approval Analytics Export\n";
        $output .= "Generated: ".now()->toDateTimeString()."\n";
        $output .= "Total Approvals: {$data['total_approvals']}\n";
        $output .= "Total Rejections: {$data['total_rejections']}\n";
        $output .= "Approval Rate: ".number_format($data['approval_rate'], 2)."%\n";
        $output .= "Average Approval Time: ".$this->formatTime($data['average_approval_time'])."\n";
        $output .= "Efficiency Score: ".number_format($data['efficiency_score'], 2)."\n\n";

        $output .= "Step Completion Rates\n";
        $output .= "Step Name,Completion Rate (%)\n";
        foreach ($data['step_completion_rates'] as $step => $rate) {
            $output .= "\"$step\",".number_format($rate, 2)."\n";
        }

        $output .= "\nRejection Reasons\n";
        $output .= "Reason,Count\n";
        foreach ($data['rejection_reasons']['breakdown'] as $reason => $count) {
            $output .= "\"$reason\",$count\n";
        }

        $output .= "\nContent Type Metrics\n";
        $output .= "Type,Count,Approval Rate (%),Avg Approval Time\n";
        foreach ($data['content_type_metrics'] as $type => $metrics) {
            $output .= sprintf(
                "\"%s\",%d,%.2f,%s\n",
                $type,
                $metrics['count'],
                $metrics['approval_rate'],
                $this->formatTime($metrics['avg_time'])
            );
        }

        return $output;
    }

    protected function formatAsJson($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    protected function formatTime($seconds)
    {
        if (!$seconds) return 'N/A';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
