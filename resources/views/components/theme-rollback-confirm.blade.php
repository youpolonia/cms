@props([
    'theme',
    'version'
])

<div x-data="{ show: false }">
    <button @click="show = true" 
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-900 focus:ring focus:ring-red-300 disabled:opacity-25 transition">
        Rollback to This Version
    </button>

    <div x-show="show" 
         x-transition
         class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-medium text-gray-900">Confirm Version Rollback</h3>
            
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    You are about to rollback theme <span class="font-semibold">{{ $theme->name }}</span> to version <span class="font-semibold">v{{ $version->getSemanticVersion() }}</span>.
                </p>
                <p class="mt-2 text-sm text-gray-600">
                    This will revert all changes made after this version and cannot be undone.
                </p>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button @click="show = false" 
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                
                <form method="POST" action="{{ route('themes.versions.rollback', [$theme, $version]) }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        Confirm Rollback
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
