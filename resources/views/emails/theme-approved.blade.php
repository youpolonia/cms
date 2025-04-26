@component('mail::message')
# Theme Version Approved 🎉

Your theme **{{ $themeName }}** (v{{ $version }}) has been approved by {{ $actorName }}.

@if($includeComments && $comments)
### Reviewer Comments
{{ $comments }}
@endif

@if($includeNextSteps && $nextSteps)
### Next Steps
{{ $nextSteps }}
@endif

@component('mail::button', ['url' => route('themes.show', $themeId)])
View Theme
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
