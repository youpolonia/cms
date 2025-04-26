<?php

namespace App\Observers;

use App\Models\Media;
use App\Services\MCPMediaService;
use App\Jobs\ProcessMediaJob;
use App\Jobs\TagMediaJob;
use App\Jobs\ModerateMediaJob;

class MediaObserver
{
    public function __construct(
        protected MCPMediaService $mediaService
    ) {}

    public function created(Media $media)
    {
        ProcessMediaJob::dispatch($media);
        TagMediaJob::dispatch($media);
        ModerateMediaJob::dispatch($media);
    }
}