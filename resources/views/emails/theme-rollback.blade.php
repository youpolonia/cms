@component('mail::message')
# Theme Rollback: {{ $theme->name }}

The theme **{{ $theme->name }}** has been rolled back to version **{{ $rollback->previous_version }}**.

**Rollback Details:**
- **Previous Version:** {{ $rollback->previous_version }}
- **Current Version:** {{ $rollback->current_version }}
- **Rollback Date:** {{ $rollback->created_at->format('Y-m-d H:i') }}
- **Initiated By:** {{ $rollback->user->name ?? 'System' }}

@if($rollback->reason)
**Reason:**  
{{ $rollback->reason }}
@endif

@component('mail::button', ['url' => route('themes.versions.show', [$theme, $rollback->previous_version])])
View Rolled Back Version
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
