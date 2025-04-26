<div class="relative inline-flex group">
    {{ $slot }}
    <div class="absolute hidden group-hover:flex flex-col items-center 
        {{ $position === 'top' ? 'bottom-full mb-2' : 'top-full mt-2' }}">
        <div class="w-3 h-3 -mb-2 rotate-45 bg-gray-700"></div>
        <span class="relative z-10 p-2 text-xs leading-none text-white whitespace-nowrap bg-gray-700 rounded shadow-lg">
            {{ $text }}
        </span>
    </div>
</div>
