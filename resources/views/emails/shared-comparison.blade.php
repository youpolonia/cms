@component('mail::message')
# Version Comparison Shared

A version comparison has been shared with you:

**Title:** {{ $comparison['title'] }}  
**Shared by:** {{ $comparison['shared_by'] }}  
**Shared at:** {{ $comparison['shared_at']->format('Y-m-d H:i') }}

@component('mail::button', ['url' => $comparison['url']])
View Comparison
@endcomponent

@component('mail::panel')
**Stats:**
- Views: {{ $comparison['views'] ?? 0 }}
- Devices: {{ $comparison['devices'] ?? 0 }}
- Locations: {{ $comparison['locations'] ?? 0 }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
