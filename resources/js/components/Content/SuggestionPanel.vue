<template>
  <div class="suggestion-panel">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-medium text-gray-900">AI Content Suggestions</h3>
      <button @click="refreshSuggestions" class="text-blue-600 hover:text-blue-800 text-sm">
        Refresh
      </button>
    </div>

    <div v-if="loading" class="text-center py-4">
      <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Loading suggestions...
    </div>

    <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-700">{{ error }}</p>
        </div>
      </div>
    </div>

    <div v-else class="space-y-4">
      <div v-for="(suggestion, index) in suggestions" :key="index" class="border rounded-lg p-4 hover:bg-gray-50">
        <div class="flex justify-between items-start">
          <div>
            <h4 class="font-medium">{{ suggestion.title }}</h4>
            <p class="text-sm text-gray-600 mt-1">{{ suggestion.content }}</p>
            <div class="flex items-center mt-2 text-sm text-gray-500">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                Score: {{ suggestion.score.toFixed(1) }}
              </span>
              <span v-if="suggestion.type" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                {{ suggestion.type }}
              </span>
            </div>
          </div>
          <div class="flex space-x-2">
            <button @click="applySuggestion(suggestion)" class="text-green-600 hover:text-green-800">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
            <button @click="dismissSuggestion(suggestion)" class="text-red-600 hover:text-red-800">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div v-if="!suggestions.length" class="text-center py-4 text-gray-500">
        No suggestions available. Try refreshing or check back later.
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    contentId: {
      type: [Number, String],
      default: null
    }
  },

  data() {
    return {
      loading: false,
      error: null,
      suggestions: []
    }
  },

  mounted() {
    this.fetchSuggestions()
  },

  methods: {
    async fetchSuggestions() {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.get(`/api/content/${this.contentId}/suggestions`)
        this.suggestions = response.data.suggestions
          .sort((a, b) => b.score - a.score)
          .slice(0, 5) // Show top 5 suggestions
      } catch (error) {
        console.error('Error fetching suggestions:', error)
        this.error = 'Failed to load suggestions. Please try again.'
      } finally {
        this.loading = false
      }
    },

    refreshSuggestions() {
      this.fetchSuggestions()
    },

    async applySuggestion(suggestion) {
      try {
        await axios.post('/api/suggestions/interactions', {
          suggestion_id: suggestion.id,
          action: 'applied'
        })
        
        // Emit event to parent to apply the suggestion
        this.$emit('suggestion-applied', suggestion)
        
        // Remove from local list
        this.suggestions = this.suggestions.filter(s => s.id !== suggestion.id)
      } catch (error) {
        console.error('Error applying suggestion:', error)
      }
    },

    async dismissSuggestion(suggestion) {
      try {
        await axios.post('/api/suggestions/interactions', {
          suggestion_id: suggestion.id,
          action: 'dismissed'
        })
        
        // Remove from local list
        this.suggestions = this.suggestions.filter(s => s.id !== suggestion.id)
      } catch (error) {
        console.error('Error dismissing suggestion:', error)
      }
    }
  }
}
</script>

<style scoped>
.suggestion-panel {
  @apply bg-white p-4 rounded-lg border border-gray-200;
}
</style>