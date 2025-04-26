@component('mail::message')
# Your Scheduled Report is Ready

**Template:** {{ $template->name }}  
**Frequency:** {{ ucfirst($frequency) }}  
**Format:** {{ strtoupper($format) }}

You can download your report using the link below:

@component('mail::button', ['url' => '#'])
Download Report
@endcomponent

If you have any questions, please contact support.

Thanks,  
{{ config('app.name') }}
@endcomponent