<?php

namespace App\Jobs;

use App\Models\ScheduledExport;
use App\Models\ScheduledExportRun;
use App\Models\ContentUserView;
use App\Notifications\AnalyticsExportReady;
use App\Notifications\AnalyticsExportFailed;
use App\Services\ExportAnonymizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExportAnalyticsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ScheduledExport $export
    ) {}

    public function handle()
    {
        $run = ScheduledExportRun::create([
            'scheduled_export_id' => $this->export->id,
            'status' => 'processing'
        ]);

        try {
            $startDate = $this->export->last_run_at ?? $this->export->start_date;
            $endDate = now();

            $views = ContentUserView::query()
                ->whereBetween('viewed_at', [$startDate, $endDate])
                ->with(['content', 'user'])
                ->get();

            $csv = Writer::createFromString('');
            $csv->insertOne([
                'Content ID', 'Content Title', 'User ID',
                'Viewed At', 'Engagement Score'
            ]);

            foreach ($views as $view) {
                $data = [
                    'content_id' => $view->content_id,
                    'content_title' => $view->content->title,
                    'user_id' => $view->user_id,
                    'email' => $view->user->email,
                    'viewed_at' => $view->viewed_at,
                    'engagement_score' => $view->content->engagement_score
                ];

                $anonymizer = app(ExportAnonymizer::class);
                if ($anonymizer->shouldAnonymize($this->export)) {
                    $options = $this->export->anonymization_options
                        ? $this->export->anonymization_options
                        : [];
                    
                    $data = $anonymizer->anonymize($data, $options);
                    
                    if (!isset($data['email'])) {
                        unset($data['email']);
                    }
                }

                $csv->insertOne([
                    $data['content_id'],
                    $data['content_title'],
                    $data['user_id'],
                    $data['viewed_at'],
                    $data['engagement_score']
                ]);
            }

            $filename = "scheduled-export-{$this->export->id}-{$run->id}.csv";
            $path = "exports/{$filename}";
            Storage::put($path, $csv->toString());

            $run->update([
                'file_path' => $path,
                'status' => 'completed'
            ]);

            $this->export->update(['last_run_at' => $endDate]);

            $this->export->user->notify(new AnalyticsExportReady($path));

        } catch (\Exception $e) {
            $run->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            $this->export->user->notify(new AnalyticsExportFailed($e->getMessage()));
            throw $e;
        }
    }
}
