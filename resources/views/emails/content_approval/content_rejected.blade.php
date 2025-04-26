@component('mail::message')
# Content Rejected

Your content "{{ $content->title }}" has been rejected during the approval process.

**Rejected At:** {{ $content->updated_at->format('M j, Y g:i a') }}
**Rejected By:** {{ $rejecter->name }}
**Current Step:** {{ $step->name }}

**Rejection Reason:**
{{ $rejectionReason }}

@component('mail::button', ['url' => route('content.edit', $content)])
Edit Content
@endcomponent

Please address the feedback and resubmit for approval.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
