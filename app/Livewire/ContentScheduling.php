<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Jobs\ContentSchedulingJob;
use Illuminate\Support\Facades\Bus;

class ContentScheduling extends Component
{
    public $contentId;
    public $versionId;
    public $scheduledAt;
    public $scheduledJobs = [];

    public function mount($contentId, $versionId)
    {
        $this->contentId = $contentId;
        $this->versionId = $versionId;
        $this->loadScheduledJobs();
    }

    public function loadScheduledJobs()
    {
        $this->scheduledJobs = Bus::batch([])
            ->where('name', 'like', '%ContentSchedulingJob%')
            ->where('options->content_id', $this->contentId)
            ->where('options->version_id', $this->versionId)
            ->get();
    }

    public function schedule()
    {
        $this->validate([
            'scheduledAt' => 'required|date|after:now'
        ]);

        $batch = Bus::batch([
            new ContentSchedulingJob(
                contentId: $this->contentId,
                versionId: $this->versionId,
                scheduledAt: $this->scheduledAt
            )
        ])->name("Schedule version {$this->versionId} for content {$this->contentId}")
         ->dispatch();

        $this->scheduledAt = null;
        $this->loadScheduledJobs();
    }

    public function cancelSchedule($batchId)
    {
        Bus::findBatch($batchId)->cancel();
        $this->loadScheduledJobs();
    }

    public function render()
    {
        $content = Content::find($this->contentId);
        $version = ContentVersion::find($this->versionId);

        return view('livewire.content-scheduling', [
            'content' => $content,
            'version' => $version
        ]);
    }
}