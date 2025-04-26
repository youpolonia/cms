@component('mail::message')
# Theme Rollback Completed

The rollback for **{{ $rollback->version->theme->name }}** has been completed successfully.

**Details:**  
- Rolled back from version #{{ $rollback->version->id }}  
- Rolled back to version #{{ $rollback->rollbackToVersion->id }}  
- Completed at: {{ $rollback->completed_at->format('Y-m-d H:i:s') }}  

@if($rollback->file_changes)
**File Changes:**  
@foreach(json_decode($rollback->file_changes, true) as $change)
- {{ $change['path'] }} ({{ $change['type'] }})  
@endforeach
@endif

@component('mail::button', ['url' => $url])
View Rollback Details
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
