@component('mail::message')
# @switch($status)
    @case('requested')
        Approval Requested: {{ $themeName }} (v{{ $version }})
        @break
    @case('approval_required')
        Approval Required: {{ $themeName }} (v{{ $version }})
        @break
    @case('completed')
        Approval Process Completed: {{ $themeName }} (v{{ $version }})
        @break
@endswitch

@if($status === 'requested')
Your theme approval request has been submitted and is awaiting review.

@elseif($status === 'approval_required')
A theme version requires your approval.

@elseif($status === 'completed')
The approval process for this theme version has been completed.

@if($includeMetrics)
## Approval Metrics
- Total Steps: {{ $metrics['totalSteps'] }}
- Completed In: {{ $metrics['completionTime'] }}
- Average Step Time: {{ $metrics['avgStepTime'] }}

@if($metrics['chartData'])
@component('mail::panel')
Workflow Progress:
<div style="width: 100%; height: 20px; background: #e0e0e0; border-radius: 10px; margin-top: 5px;">
    <div style="width: {{ ($metrics['completedSteps'] / $metrics['totalSteps']) * 100 }}%; height: 100%; background: #4CAF50; border-radius: 10px;"></div>
</div>
@endcomponent
@endif
@endif
@endif

@if($includeComments && $comments)
## Comments
{{ $comments }}
@endif

@if($includeNextSteps && $nextSteps)
## Next Steps
{{ $nextSteps }}
@endif

@component('mail::button', ['url' => route('themes.versions.show', [
    'theme' => $themeName,
    'version' => $version
])])
View Theme Version
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
