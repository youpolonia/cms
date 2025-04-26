@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Schedule Change Notification

{{ $notificationMessage }}

@component('mail::panel')
**Content Title:** {{ $schedule->content->title }}  
**Original Time:** {{ $metadata['original_time']->format('F j, Y \a\t g:i A') }}  
**New Time:** {{ $schedule->publish_at->format('F j, Y \a\t g:i A') }}  
**Changed By:** {{ $metadata['changed_by']->name }}
@endcomponent

@component('mail::button', ['url' => route('content.schedule', $schedule->content)])
View Updated Schedule
@endcomponent

@slot('footer')
@component('mail::footer')
You're receiving this email because you have change notifications enabled.  
[Manage notification preferences]({{ $unsubscribeUrl }})
@endcomponent
@endslot
@endcomponent