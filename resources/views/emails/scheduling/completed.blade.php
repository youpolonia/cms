@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

# Content Successfully Published

{{ $notificationMessage }}

@component('mail::panel')
**Content Title:** {{ $schedule->content->title }}  
**Published At:** {{ $schedule->published_at->format('F j, Y \a\t g:i A') }}  
**View Count:** {{ $metadata['initial_views'] ?? 0 }} (first hour)
@endcomponent

@component('mail::button', ['url' => route('content.show', $schedule->content)])
View Published Content
@endcomponent

@slot('footer')
@component('mail::footer')
You're receiving this email because you have publication notifications enabled.  
[Manage notification preferences]({{ $unsubscribeUrl }})
@endcomponent
@endslot
@endcomponent