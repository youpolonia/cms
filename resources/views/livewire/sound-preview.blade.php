<div>
    <div class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">Sound Preview</h2>
        <p class="mt-1 text-sm text-gray-600">Preview and test notification sounds.</p>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <div class="space-y-4">
                    @foreach($sounds as $sound)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $sound->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $sound->description }}</p>
                            </div>
                            <button 
                                wire:click="playSound('{{ $sound->id }}')"
                                class="px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                Play
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <x-input-label for="volume" value="Volume" />
                    <input 
                        id="volume" 
                        type="range" 
                        min="0" 
                        max="100" 
                        wire:model="volume" 
                        class="mt-2 w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                    >
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>0%</span>
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                </div>

                <div>
                    <x-input-label for="playbackRate" value="Playback Speed" />
                    <input 
                        id="playbackRate" 
                        type="range" 
                        min="0.5" 
                        max="2" 
                        step="0.1" 
                        wire:model="playbackRate" 
                        class="mt-2 w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                    >
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>0.5x</span>
                        <span>1x</span>
                        <span>2x</span>
                    </div>
                </div>

                @if($isPlaying)
                    <div class="p-4 bg-indigo-50 rounded-lg">
                        <div class="flex items-center">
                            <svg class="animate-pulse h-5 w-5 text-indigo-600 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-indigo-600">Playing sound...</span>
                        </div>
                        <button 
                            wire:click="stopSound" 
                            class="mt-2 px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            Stop
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:load', function() {
    let audio = null;

    Livewire.on('stopAllSounds', () => {
        if (audio) {
            audio.pause();
            audio.currentTime = 0;
        }
    });

    Livewire.on('updateVolume', (volume) => {
        if (audio) {
            audio.volume = volume / 100;
        }
    });

    Livewire.on('updatePlaybackRate', (rate) => {
        if (audio) {
            audio.playbackRate = rate;
        }
    });

    Livewire.on('playSound', (soundId) => {
        if (audio) {
            audio.pause();
        }

        audio = new Audio(`/storage/sounds/${soundId}.mp3`);
        audio.volume = Livewire.volatile.volume / 100;
        audio.playbackRate = Livewire.volatile.playbackRate;
        audio.play();

        audio.addEventListener('ended', () => {
            Livewire.emit('stopSound');
        });
    });
});
</script>
@endpush