<?php

namespace App\Jobs;

use App\Models\ExportHistory;
use App\Services\AutoClassificationService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBulkClassification implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public int $exportId
    ) {}

    public function handle(AutoClassificationService $classifier)
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $export = ExportHistory::findOrFail($this->exportId);
        
        $classification = $classifier->classify($export->error_message);
        
        if ($classification) {
            $export->update([
                'error_classified' => true,
                'error_category_id' => $classification['category']->id,
                'classification_confidence' => $classification['confidence']
            ]);
        }
    }
}