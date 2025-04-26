@component('mail::message')
# Content Moderation Decision

Your content "{{ $contentTitle }}" has been {{ $decision }}.

@if($reason)
**Reason:**  
{{ $reason }}
@endif

@component('mail::button', ['url' => url('/content')])
View Content
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
