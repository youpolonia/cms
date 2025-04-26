@component('mail::message')
# {{ $subject }}

@switch($type)
    @case('frequent_change')
        A version you frequently compare (**{{ $version1->title }}**) has been updated.
        @break

    @case('cache_update')
        A cached comparison between **{{ $version1->title }}** and **{{ $version2->title }}** has been updated.
        @break

    @case('new_version')
        A new version (**{{ $version2->title }}**) is available for comparison with frequently compared version **{{ $version1->title }}**.
        @break
@endswitch

@if(!empty($stats))
**Comparison Stats:**
- Similarity: {{ $stats['similarity'] ?? 'N/A' }}%
- Changes: {{ $stats['changes'] ?? 'N/A' }}
- Lines Added: {{ $stats['lines_added'] ?? 'N/A' }}
- Lines Removed: {{ $stats['lines_removed'] ?? 'N/A' }}
@endif

@component('mail::button', ['url' => route('content.versions.compare', [$version1->id, $version2->id])])
View Comparison
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
