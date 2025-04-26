@props([
    'export',
    'label' => 'Download',
    'icon' => 'download',
    'expired' => false,
])

@if($expired)
    <div class="export-download expired" title="This export has expired">
        <x-icon name="{{ $icon }}" class="text-gray-400" />
        <span class="text-gray-500">{{ $label }}</span>
    </div>
@else
    <a 
        href="{{ route('analytics.exports.download', $export) }}" 
        class="export-download"
        wire:click.prevent="downloadExport('{{ $export->id }}')"
    >
        <x-icon name="{{ $icon }}" />
        <span>{{ $label }}</span>
    </a>
@endif

@once
    @push('styles')
        <style>
            .export-download {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                border-radius: 0.25rem;
                background-color: var(--primary);
                color: white;
                text-decoration: none;
                transition: all 0.2s;
            }
            .export-download:hover {
                background-color: var(--primary-dark);
            }
            .export-download.expired {
                background-color: var(--gray-100);
                cursor: not-allowed;
            }
        </style>
    @endpush
@endonce