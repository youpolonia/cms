@component('mail::message')
# Theme Installation Failed

The installation of theme **{{ $theme->name }}** (version {{ $version->version }}) has failed.

**Error Details:**  
{{ $error }}

@component('mail::button', ['url' => $actionUrl])
View Available Updates
@endcomponent

If you need assistance, please contact your system administrator.

Thanks,  
{{ config('app.name') }}
@endcomponent
