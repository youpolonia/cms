@component('mail::message')
# Analytics Export Ready

Your analytics data export has been completed and is ready for download.

**Export Details:**
- Generated: {{ now()->format('F j, Y g:i a') }}
- File Type: CSV 
- Expires: {{ now()->addHours(24)->format('F j, Y g:i a') }}

**Report Includes:**
- Content performance metrics
- User engagement data  
- Category analytics
- Version comparison statistics

@component('mail::button', ['url' => $url])
Download Export
@endcomponent

The download link will expire in 24 hours. If you have any questions, please contact support.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
