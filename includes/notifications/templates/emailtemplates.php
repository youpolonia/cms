<?php
/**
 * Email notification templates for the scheduling system
 */

namespace Includes\Notifications\Templates;

class EmailTemplates
{
    /**
     * Get schedule status change email template
     * 
     * @param array $data Template data
     * @return string Formatted email content
     */
    public static function scheduleStatusChange(array $data): string
    {
        $workerName = $data['worker_name'] ?? 'Worker';
        $status = $data['status'] ?? 'updated';
        $date = $data['date'] ?? date('F j, Y');
        $startTime = $data['start_time'] ?? '9:00 AM';
        $endTime = $data['end_time'] ?? '5:00 PM';
        $location = $data['location'] ?? 'Default Location';
        $reason = $data['reason'] ?? '';
        
        $reasonHtml = '';
        if (!empty($reason)) {
            $reasonHtml = "<p><strong>Reason:</strong> {$reason}</p>";
        }
        $statusColorMap = [
            'approved' => '#28a745',
            'rejected' => '#dc3545',
            'cancelled' => '#6c757d',
            'completed' => '#17a2b8',
            'scheduled' => '#007bff',
            'pending' => '#ffc107'
        ];
        
        $statusColor = $statusColorMap[$status] ?? '#6c757d';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        .content {
            padding: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            background-color: {$statusColor};
            color: white;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Schedule Status Update</h1>
        </div>
        <div class="content">
            <p>Hello {$workerName},</p>

            <p>Your shift scheduled for <strong>{$date}</strong> has been <span class="status-badge">{$status}</span>.</p>
            <h3>Shift Details:</h3>
            <p><strong>Date:</strong> {$date}</p>
            <p><strong>Time:</strong> {$startTime} - {$endTime}</p>
            <p><strong>Location:</strong> {$location}</p>
            {$reasonHtml}
            
            <p>Please log in to your account to view more details or contact your supervisor if you have any questions.</p>
            
            <p>Thank you,<br>
            The Management Team</p>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get plain text version of schedule status change email
     *
     * @param array $data Template data
     * @return string Formatted plain text email
     */
    public static function scheduleStatusChangePlainText(array $data): string
    {
        $workerName = $data['worker_name'] ?? 'Worker';
        $status = $data['status'] ?? 'updated';
        $date = $data['date'] ?? date('F j, Y');
        $startTime = $data['start_time'] ?? '9:00 AM';
        $endTime = $data['end_time'] ?? '5:00 PM';
        $location = $data['location'] ?? 'Default Location';
        $reason = $data['reason'] ?? '';

        $reasonText = '';
        if (!empty($reason)) {
            $reasonText = "Reason: {$reason}\n\n";
        }
        return <<<TEXT
SCHEDULE STATUS UPDATE

Hello {$workerName},

Your shift scheduled for {$date} has been {$status}.

SHIFT DETAILS:
Date: {$date}
Time: {$startTime} - {$endTime}
Location: {$location}
{$reasonText}
Please log in to your account to view more details or contact your supervisor if you have any questions.

Thank you,
The Management Team

---
This is an automated message. Please do not reply to this email.
TEXT;
    }

    /**
     * Get password reset email template
     *
     * @param array $data Template data
     * @return string Formatted email content
     */
    public static function passwordReset(array $data): string
    {
        $name = $data['name'] ?? 'User';
        $resetLink = $data['reset_link'] ?? '#';
        $expiryHours = $data['expiry_hours'] ?? 1;

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hello {$name},</p>
        <p>We received a request to reset your password. Click the button below to reset it:</p>
        
        <p>
            <a href="{$resetLink}" class="button">Reset Password</a>
        </p>
        
        <p>This link will expire in {$expiryHours} hour(s). If you didn't request this, please ignore this email.</p>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
