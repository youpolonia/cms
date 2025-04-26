@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    @if ($notification->type === 'requested')
        <h1 style="color: #3d4852; font-size: 18px; margin-bottom: 20px;">
            {{ $notification->getSubject() }}
        </h1>
        
        <p style="margin-bottom: 20px;">
            {{ $notification->getMessage() }}
        </p>
    @elseif ($notification->type === 'approved')
        <h1 style="color: #3d4852; font-size: 18px; margin-bottom: 20px;">
            {{ $notification->getSubject() }}
        </h1>
        
        <p style="margin-bottom: 20px;">
            {{ $notification->getMessage() }}
        </p>
    @else
        <h1 style="color: #3d4852; font-size: 18px; margin-bottom: 20px;">
            {{ $notification->getSubject() }}
        </h1>
        
        <p style="margin-bottom: 20px;">
            {{ $notification->getMessage() }}
        </p>
        
        @if ($notification->approval->rejection_reason)
            <div style="background: #f8fafc; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <strong>Reason:</strong>
                <p>{{ $notification->approval->rejection_reason }}</p>
            </div>
        @endif
    @endif

    @component('mail::button', ['url' => $notification->getActionUrl()])
        View Theme Version
    @endcomponent

    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent
