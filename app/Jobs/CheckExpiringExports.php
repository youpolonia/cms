<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use App\Models\User;
use App\Notifications\ExportExpiringSoon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiringExports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $warningDays = config('analytics.exports.warning_days_before');
        $threshold = now()->addDays($warningDays);
        
        $expiringExports = AnalyticsExport::whereBetween('expires_at', [now(), $threshold])
            ->where('status', 'completed')
            ->whereNotNull('file_path')
            ->get();

        $admins = User::role('admin')->get();

        foreach ($expiringExports as $export) {
            $daysRemaining = now()->diffInDays($export->expires_at);
            
            foreach ($admins as $admin) {
                $admin->notify(new ExportExpiringSoon($export, $daysRemaining));
            }
        }
    }
}