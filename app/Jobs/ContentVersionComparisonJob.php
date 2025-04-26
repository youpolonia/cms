<?php

namespace App\Jobs;

use App\Models\ContentVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ContentVersionComparisonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ContentVersion $versionA,
        public ContentVersion $versionB
    ) {}

    public function handle()
    {
        $metrics = $this->compareVersions();
        $this->versionA->update(['comparison_metrics' => $metrics]);
        $this->versionB->update(['comparison_metrics' => $metrics]);
    }

    protected function compareVersions(): array
    {
        return [
            'content_diff' => $this->compareText(
                $this->versionA->content,
                $this->versionB->content
            ),
            'title_diff' => $this->compareText(
                $this->versionA->title,
                $this->versionB->title
            ),
            'word_count_diff' => abs(
                str_word_count($this->versionA->content) - 
                str_word_count($this->versionB->content)
            ),
            'character_count_diff' => abs(
                strlen($this->versionA->content) - 
                strlen($this->versionB->content)
            ),
            'changed_at' => now()->toDateTimeString()
        ];
    }

    protected function compareText(string $old, string $new): string
    {
        // Simple line-based diff for now
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);
        
        $diff = [];
        foreach ($newLines as $i => $line) {
            if (!isset($oldLines[$i]) || $oldLines[$i] !== $line) {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $oldLines[$i] ?? '',
                    'new' => $line
                ];
            }
        }
        
        return json_encode($diff);
    }
}