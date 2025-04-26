@component('mail::message')
# {{ $notification->getSubject() }}

{{ $notification->getMessage() }}

**Theme:** {{ $notification->approval->themeVersion->theme->name }}  
**Version:** {{ $notification->approval->themeVersion->version_number }}  
**Rejected By:** {{ $notification->approval->approver->name }}  
**Rejected At:** {{ $notification->approval->updated_at->format('Y-m-d H:i') }}  
**Reason:** {{ $notification->approval->rejection_reason ?? 'No reason provided' }}

@component('mail::button', ['url' => $notification->getActionUrl()])
View Theme Version
@endcomponent

Please address the feedback and resubmit for approval.

Thanks,  
{{ config('app.name') }}
@endcomponent
