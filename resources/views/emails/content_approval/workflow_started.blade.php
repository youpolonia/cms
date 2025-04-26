@component('mail::message')
# Content Approval Workflow Started

Your content "{{ $content->title }}" has entered the approval workflow.

**Workflow:** {{ $workflow->name }}
**Current Step:** {{ $step->name }}
**Assigned Approvers:** {{ $step->approvers->pluck('name')->join(', ') }}

@component('mail::button', ['url' => route('content.approval.show', $content)])
View Content
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
