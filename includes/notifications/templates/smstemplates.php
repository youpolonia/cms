<?php
/**
 * SMS notification templates for the scheduling system
 */

namespace Includes\Notifications\Templates;

class SmsTemplates
{
    /**
     * Get schedule status change SMS template
     * 
     * @param array $data Template data
     * @return string Formatted SMS content
     */
    public static function scheduleStatusChange(array $data): string
    {
        $workerName = $data['worker_name'] ?? 'Worker';
        $status = $data['status'] ?? 'updated';
        $date = $data['date'] ?? date('F j, Y');
        $startTime = $data['start_time'] ?? '9:00 AM';
        $endTime = $data['end_time'] ?? '5:00 PM';
        $reason = $data['reason'] ?? '';
        
        $reasonText = '';
        if (!empty($reason)) {
            $reasonText = " Reason: {$reason}";
        }
        
        return "SCHEDULE UPDATE: Your shift on {$date} ({$startTime}-{$endTime}) has been {$status}.{$reasonText} Log in for details.";
    }
    
    /**
     * Get schedule reminder SMS template
     * 
     * @param array $data Template data
     * @return string Formatted SMS content
     */
    public static function scheduleReminder(array $data): string
    {
        $workerName = $data['worker_name'] ?? 'Worker';
        $date = $data['date'] ?? date('F j, Y');
        $startTime = $data['start_time'] ?? '9:00 AM';
        $endTime = $data['end_time'] ?? '5:00 PM';
        $location = $data['location'] ?? 'Default Location';
        $hoursUntil = $data['hours_until'] ?? '24';
        
        return "REMINDER: You have a shift scheduled in {$hoursUntil} hours on {$date} from {$startTime} to {$endTime} at {$location}.";
    }
    
    /**
     * Get urgent schedule change SMS template
     * 
     * @param array $data Template data
     * @return string Formatted SMS content
     */
    public static function urgentScheduleChange(array $data): string
    {
        $workerName = $data['worker_name'] ?? 'Worker';
        $date = $data['date'] ?? date('F j, Y');
        $startTime = $data['start_time'] ?? '9:00 AM';
        $endTime = $data['end_time'] ?? '5:00 PM';
        $reason = $data['reason'] ?? 'operational needs';
        
        return "URGENT: Your shift on {$date} has been changed due to {$reason}. New time: {$startTime}-{$endTime}. Please confirm receipt by replying YES.";
    }
}
