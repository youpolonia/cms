@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    # Content Approval Required

    You have been assigned to review the following content:

    **Title:** {{ $content->title }}  
    **Type:** {{ $content->type->name }}  
    **Author:** {{ $content->author->name }}  
    **Submitted:** {{ $content->created_at->format('M j, Y g:i a') }}

    @component('mail::button', ['url' => route('content.review', $content)])
        Review Content
    @endcomponent

    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent