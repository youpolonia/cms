<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBulkContent implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $content;
    public $action;

    public function __construct(Content $content, string $action)
    {
        $this->content = $content;
        $this->action = $action;
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        switch ($this->action) {
            case 'publish':
                $this->content->update(['status' => 'published']);
                break;
            case 'archive':
                $this->content->update(['status' => 'archived']);
                break;
            case 'delete':
                $this->content->delete();
                break;
        }
    }
}