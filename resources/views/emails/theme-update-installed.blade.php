@component('mail::message')
# Theme Update Successfully Installed

The **{{ $theme->name }}** theme has been updated to version {{ $version->version }}.

## Update Details
- **New Version**: {{ $version->version }}
- **Author**: {{ $theme->author }}
- **Description**: {{ $theme->description }}

@component('mail::button', ['url' => $actionUrl])
View Updated Theme
@endcomponent

## Next Steps
1. Review the changes in the theme editor
2. Check for any new configuration options
3. Test your site's functionality

@component('mail::subcopy')
You're receiving this email because you have theme update notifications enabled.
[Manage notification preferences]({{ route('profile.notifications') }})
@endcomponent

@endcomponent
