@component('mail::message')
# Theme Version Rejected ❌

Your theme **{{ $themeName }}** (v{{ $version }}) has been rejected by {{ $actorName }}.

@if($rejectionReason)
### Reason for Rejection
{{ $rejectionReason }}
@endif

@if($includeComments && $comments)
### Reviewer Comments
{{ $comments }}
@endif

@if($includeNextSteps && $nextSteps)
### Next Steps
{{ $nextSteps }}
@endif

@component('mail::button', ['url' => route('themes.versions.show', [$themeId, $versionId])])
View Version Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
