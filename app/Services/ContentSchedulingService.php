<?php

namespace App\Services;

use App\Models\Content;
use Carbon\Carbon;

class ContentSchedulingService
{
    public function schedulePublish(Content $content, Carbon $publishAt): void
    {
        $content->update([
            'publish_at' => $publishAt,
            'status' => 'draft'
        ]);
    }

    public function scheduleUnpublish(Content $content, Carbon $expireAt): void
    {
        $content->update([
            'expire_at' => $expireAt
        ]);
    }

    public function cancelSchedule(Content $content): void
    {
        $content->update([
            'publish_at' => null,
            'expire_at' => null
        ]);
    }

    public function getScheduledContent(): array
    {
        return [
            'to_publish' => Content::scheduledForPublish()->count(),
            'to_expire' => Content::scheduledForExpire()->count()
        ];
    }
}