<?php

namespace App\Jobs;

use App\Models\Content;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\ContentExpiringSoon;

class CheckContentExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $thresholds = [1, 3, 7]; // Days before expiration to notify

        foreach ($thresholds as $days) {
            $contents = Content::whereNotNull('expire_at')
                ->where('expire_at', '<=', Carbon::now()->addDays($days))
                ->where('expire_at', '>', Carbon::now())
                ->whereDoesntHave('notifications', function($q) use ($days) {
                    $q->where('type', ContentExpiringSoon::class)
                      ->where('data->daysRemaining', $days);
                })
                ->get();

            foreach ($contents as $content) {
                $content->user->notify(new ContentExpiringSoon($content, $days));
            }
        }
    }
}