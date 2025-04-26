@props(['status', 'class'])

<span class="px-2 py-1 rounded-full text-xs font-medium {{ $class }}">
    {{ ucfirst($status) }}
</span>