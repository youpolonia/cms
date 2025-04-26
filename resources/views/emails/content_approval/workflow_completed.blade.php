@component('mail::message')
# Approval Workflow Completed

Content "{{ $content->title }}" has completed the approval workflow.

**Final Status:** Approved
**Published At:** {{ $content->published_at->format('M j, Y g:i a') }}
**Published By:** {{ $content->publishedBy->name }}

@component('mail::button', ['url' => route('content.show', $content)])
View Published Content
@endcomponent

The content is now live and available to users.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
