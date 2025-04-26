@props(['recentExports'])

<div 
    x-data="{}"
    class="analytics-widget"
>
    <x-analytics.export-status-widget-vue
        :recent-exports="$recentExports"
    />
</div>