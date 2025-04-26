@component('mail::message')
# Theme Version Rolled Back

The theme **{{ $version->theme->name }}** version **{{ $version->getSemanticVersion() }}** has been rolled back to version **{{ $rollback->previousVersion->getSemanticVersion() }}**.

**Rolled back by:** {{ $rollback->initiatedBy->name }}  
**Date:** {{ $rollback->created_at->format('M j, Y g:i a') }}

@if($rollback->reason)
**Reason:**  
{{ $rollback->reason }}
@endif

@component('mail::button', ['url' => $actionUrl])
View Theme Version
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
