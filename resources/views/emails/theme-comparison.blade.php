@component('mail::message')
# {{ $notification->getSubject() }}

{{ $notification->getMessage() }}

**Theme:** {{ $notification->comparison->themeVersion->theme->name }}  
**Version:** {{ $notification->comparison->themeVersion->version_number }}  
**Compared With:** Version {{ $notification->comparison->baseVersion->version_number }}  
**Comparison Date:** {{ $notification->comparison->created_at->format('Y-m-d H:i') }}

### Comparison Summary
**Files Changed:**  
- Added: {{ $notification->comparison->files_added }}  
- Removed: {{ $notification->comparison->files_removed }}  
- Modified: {{ $notification->comparison->files_modified }}

**Size Changes:**  
- Total: {{ $notification->comparison->size_difference }} KB  
- CSS: {{ $notification->comparison->css_size_difference }} KB  
- JS: {{ $notification->comparison->js_size_difference }} KB

**Quality Metrics:**  
- Accessibility: {{ $notification->comparison->accessibility_score }}%  
- Performance: {{ $notification->comparison->performance_score }}%  
- Compatibility: {{ $notification->comparison->compatibility_score }}%

@component('mail::button', ['url' => $notification->getActionUrl()])
View Full Comparison
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent
