@component('mail::message')
# {{ $notification->getSubject() }}

{{ $notification->getMessage() }}

**Theme:** {{ $notification->approval->themeVersion->theme->name }}  
**Version:** {{ $notification->approval->themeVersion->version_number }}  
**Approved By:** {{ $notification->approval->approver->name }}  
**Approved At:** {{ $notification->approval->updated_at->format('Y-m-d H:i') }}

@component('mail::button', ['url' => $notification->getActionUrl()])
View Theme Version
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
