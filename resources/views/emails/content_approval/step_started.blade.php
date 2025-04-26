@component('mail::message')
# Approval Step Started

Content "{{ $content->title }}" has reached a new approval step.

**Current Step:** {{ $step->name }}
**Step Instructions:** {{ $step->instructions }}
**Due Date:** {{ $step->due_date->format('M j, Y') }}

@component('mail::button', ['url' => route('content.approval.show', $content)])
Review Content
@endcomponent

@if($step->requires_comment)
Please include comments with your approval decision.
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent
