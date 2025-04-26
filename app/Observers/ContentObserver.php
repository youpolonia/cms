<?php

namespace App\Observers;

use App\Jobs\ContentSchedulingJob;
use App\Models\Content;
use Carbon\Carbon;

class ContentObserver
{
    public function updated(Content $content)
    {
        $this->handleScheduling($content);
    }

    public function created(Content $content)
    {
        $this->handleScheduling($content);
    }

    protected function handleScheduling(Content $content)
    {
        if ($content->isDirty(['publish_at', 'expire_at', 'status'])) {
            if ($content->publish_at) {
                $delay = Carbon::parse($content->publish_at);
                ContentSchedulingJob::dispatch($content)
                    ->delay($delay);
            }

            if ($content->expire_at) {
                $delay = Carbon::parse($content->expire_at);
                ContentSchedulingJob::dispatch($content)
                    ->delay($delay);
            }
        }
    }
}
