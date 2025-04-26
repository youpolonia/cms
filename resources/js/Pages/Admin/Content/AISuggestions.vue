<template>
  <div class="ai-suggestions">
    <div class="suggestion-header">
      <h4>AI Suggestions</h4>
      <select v-model="suggestionType">
        <option value="rewrite">Rewrite</option>
        <option value="expand">Expand</option>
        <option value="summarize">Summarize</option>
        <option value="tone">Change Tone</option>
      </select>
      <select v-if="suggestionType === 'tone'" v-model="tone">
        <option value="professional">Professional</option>
        <option value="casual">Casual</option>
        <option value="friendly">Friendly</option>
        <option value="authoritative">Authoritative</option>
      </select>
      <button @click="generateSuggestions" :disabled="loading">
        {{ loading ? 'Generating...' : 'Generate' }}
      </button>
    </div>

    <div v-if="suggestions.length > 0" class="suggestions-list">
      <div v-for="(suggestion, index) in suggestions" 
           :key="index"
           class="suggestion-item">
        <div class="suggestion-content" v-html="suggestion.text"></div>
        <div class="suggestion-actions">
          <button @click="applySuggestion(suggestion)">Apply</button>
          <button @click="regenerateSuggestion(index)">Regenerate</button>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    block: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      suggestionType: 'rewrite',
      tone: 'professional',
      suggestions: [],
      loading: false,
      error: null
    }
  },
  methods: {
    async generateSuggestions() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/ai/suggestions', {
          blockId: this.block.id,
          blockType: this.block.type,
          content: this.block.content,
          suggestionType: this.suggestionType,
          tone: this.suggestionType === 'tone' ? this.tone : null
        });

        this.suggestions = response.data.suggestions.map(s => ({
          ...s,
          text: this.highlightChanges(s.text)
        }));
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to generate suggestions';
      } finally {
        this.loading = false;
      }
    },
    highlightChanges(text) {
      // Simple highlighting of changes between original and suggestion
      if (!this.block.content) return text;
      
      const originalWords = this.block.content.split(/\s+/);
      const suggestedWords = text.split(/\s+/);
      
      return suggestedWords.map((word, i) => {
        if (i >= originalWords.length || word !== originalWords[i]) {
          return `<span class="highlight">${word}</span>`;
        }
        return word;
      }).join(' ');
    },
    applySuggestion(suggestion) {
      this.$emit('suggestion-applied', {
        ...this.block,
        content: suggestion.rawText || suggestion.text
      });
    },
    async regenerateSuggestion(index) {
      try {
        const response = await axios.post('/api/ai/suggestions/regenerate', {
          suggestionId: this.suggestions[index].id
        });

        this.suggestions.splice(index, 1, {
          ...response.data.suggestion,
          text: this.highlightChanges(response.data.suggestion.text)
        });
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to regenerate suggestion';
      }
    }
  }
}
</script>

<style scoped>
.ai-suggestions {
  border-top: 1px solid #eee;
  margin-top: 20px;
  padding-top: 20px;
}
.suggestion-header {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
  align-items: center;
}
.suggestions-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.suggestion-item {
  border: 1px solid #ddd;
  padding: 15px;
  border-radius: 4px;
}
.suggestion-content {
  margin-bottom: 10px;
}
.suggestion-actions {
  display: flex;
  gap: 10px;
}
.highlight {
  background-color: #fffacd;
  padding: 0 2px;
}
.error-message {
  color: #dc3545;
  margin-top: 10px;
}
</style>