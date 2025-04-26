<template>
    <div class="suggestion-panel">
        <h3 class="text-lg font-medium mb-4">Content Suggestions</h3>
        
        <div v-if="loading" class="text-center py-4">
            <svg class="animate-spin h-5 w-5 text-blue-500 mx-auto" xmlns="http://www3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <div v-else>
            <div v-if="suggestions.length === 0" class="text-gray-500 text-sm">
                No suggestions available
            </div>

            <div v-else class="space-y-3">
                <div v-for="suggestion in suggestions" :key="suggestion.id" 
                     class="border rounded p-3 hover:bg-gray-50 cursor-pointer"
                     @click="handleSuggestionClick(suggestion)">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-medium">{{ suggestion.title }}</h4>
                            <p class="text-xs text-gray-500">{{ suggestion.type }}</p>
                        </div>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                            {{ suggestion.score }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        contentId: {
            type: Number,
            default: null
        }
    },

    data() {
        return {
            loading: false,
            suggestions: []
        }
    },

    watch: {
        contentId: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.fetchSuggestions();
                }
            }
        }
    },

    methods: {
        async fetchSuggestions() {
            this.loading = true;
            try {
                const response = await axios.get(`/api/content/${this.contentId}/suggestions`);
                this.suggestions = response.data.data;
            } catch (error) {
                console.error('Error fetching suggestions:', error);
            } finally {
                this.loading = false;
            }
        },

        handleSuggestionClick(suggestion) {
            this.$emit('suggestion-selected', suggestion);
        }
    }
}
</script>

<style scoped>
.suggestion-panel {
    position: sticky;
    top: 1rem;
}
</style>