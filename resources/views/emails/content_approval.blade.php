@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    <h1>Content {{ ucfirst($action) }}</h1>
    <p>Your content <strong>"{{ $content->title }}"</strong> has been <strong>{{ $action }}</strong>.</p>

    @if($reason)
        <div class="reason">
            <h3>Reason:</h3>
            <p>{{ $reason }}</p>
        </div>
    @endif

    @component('mail::button', ['url' => $url])
        View Content
    @endcomponent

    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent
