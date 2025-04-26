<?php

namespace App\Listeners;

use App\Events\ContentVersionRestored;
use App\Models\ContentRestoration;

class RestoreVersion
{
    public function handle(ContentVersionRestored $event)
    {
        ContentRestoration::create([
            'content_id' => $event->contentId,
            'version_id' => $event->versionId,
            'restored_by' => $event->userId,
            'restored_at' => now(),
            'reason' => $event->reason,
        ]);

        // Log the restoration
        activity()
            ->causedBy($event->userId)
            ->performedOn(Content::find($event->contentId))
            ->withProperties([
                'version_id' => $event->versionId,
                'reason' => $event->reason
            ])
            ->log('Content version restored');
    }
}