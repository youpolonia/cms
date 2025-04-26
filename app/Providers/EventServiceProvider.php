<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ContentVersionRestored;
use App\Listeners\RestoreVersion;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\CommentCreated::class => [
            \App\Listeners\SendCommentNotification::class,
        ],
        \App\Events\ContentVersionCompared::class => [
            \App\Listeners\RecordVersionComparison::class,
        ],
        ContentVersionRestored::class => [
            RestoreVersion::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}