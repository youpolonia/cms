@component('mail::message')
# Theme Update Available: {{ $theme->name }} v{{ $version }}

A new version of the **{{ $theme->name }}** theme is available for installation.

**Current Version:** {{ $theme->currentVersion->version }}  
**Available Version:** {{ $version }}

@if($changelog)
## What's New
@component('mail::panel')
{!! $changelog !!}
@endcomponent
@endif

@if(!empty($dependencyIssues))
## Dependency Requirements
@component('mail::panel')
<ul>
@foreach($dependencyIssues as $issue)
<li>{{ $issue }}</li>
@endforeach
</ul>
@endcomponent
@endif

@component('mail::button', ['url' => route('themes.updates.index')])
View All Updates
@endcomponent

@component('mail::subcopy')
If you no longer wish to receive these notifications, you can [update your notification preferences]({{ route('profile.edit') }}).
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
