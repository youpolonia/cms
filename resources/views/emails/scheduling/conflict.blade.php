@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Schedule Conflict Detected

{{ $notificationMessage }}

@component('mail::panel')
**Content Title:** {{ $schedule->content->title }}  
**Scheduled Time:** {{ $schedule->publish_at->format('F j, Y \a\t g:i A') }}  
**Conflict With:** {{ $metadata['conflicting_content']->title }}  
**Conflict Type:** {{ $metadata['conflict_type'] }}
@endcomponent

@component('mail::button', ['url' => route('scheduling.conflicts')])
Resolve Conflict
@endcomponent

@slot('footer')
@component('mail::footer')
You're receiving this email because you have conflict notifications enabled.  
[Manage notification preferences]({{ $unsubscribeUrl }})
@endcomponent
@endslot
@endcomponent