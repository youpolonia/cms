@component('mail::message')
# Analytics Export Failed

Your requested analytics export failed to generate.

@if($error)
**Error Details:**  
{{ $error }}
@endif

You can try exporting again from the analytics dashboard.

@component('mail::button', ['url' => route('analytics.exports.index')])
Go to Analytics Dashboard
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent