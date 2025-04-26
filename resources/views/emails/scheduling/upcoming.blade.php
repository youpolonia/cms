@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Upcoming Schedule Notification

{{ $notificationMessage }}

@component('mail::panel')
**Content Title:** {{ $schedule->content->title }}  
**Scheduled Time:** {{ $schedule->publish_at->format('F j, Y \a\t g:i A') }}  
**Content Type:** {{ $schedule->content->type->name }}
@endcomponent

@component('mail::button', ['url' => route('content.show', $schedule->content)])
View Content
@endcomponent

@slot('footer')
@component('mail::footer')
You're receiving this email because you have upcoming schedule notifications enabled.  
[Manage notification preferences]({{ $unsubscribeUrl }})
@endcomponent
@endslot
@endcomponent