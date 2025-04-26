@component('mail::message')
# Theme Successfully Installed

The **{{ $theme->name }}** theme (version {{ $version->version }}) has been successfully installed.

## Theme Details
- **Version**: {{ $version->version }}
- **Author**: {{ $theme->author }}
- **Description**: {{ $theme->description }}

@component('mail::button', ['url' => $actionUrl])
View Theme
@endcomponent

## Next Steps
1. You can now activate this theme in your site settings
2. Customize the theme using the theme editor
3. Configure any theme-specific options

@component('mail::subcopy')
You're receiving this email because you have theme installation notifications enabled.
[Manage notification preferences]({{ route('profile.notifications') }})
@endcomponent

@endcomponent
