<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Builder Demo</title>
    
    <!-- Loading Vite assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased min-h-screen">
    <div class="bg-white">
        @livewire(\App\Http\Livewire\PageBuilder::class, ['content_id' => null])
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Include page builder script -->
    <x-slot:scripts>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('pageBuilderLoaded', () => {
                    console.log('Page Builder initialized');
                });
            });
        </script>
    </x-slot:scripts>
</body>
</html>