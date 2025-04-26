@component('mail::message')
# Theme Export Ready

Your theme export for **{{ $version->theme->name }} v{{ $version->version }}** has been completed and is ready for download.

**Export Details:**
- Theme: {{ $version->theme->name }}
- Version: v{{ $version->version }}
- Export Date: {{ now()->format('F j, Y g:i a') }}
- File Type: ZIP Archive
- Expires: {{ now()->addHours(24)->format('F j, Y g:i a') }}

**Includes:**
- Theme templates and views
- Assets (CSS/JS/Images)
- Configuration files
- Version metadata

@component('mail::button', ['url' => $url])
Download Theme Export
@endcomponent

The download link will expire in 24 hours. If you have any questions, please contact support.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
