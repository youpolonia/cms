<template>
  <div class="ai-editor">
    <div class="ai-controls">
      <button @click="generateFromTitle" :disabled="loading">
        {{ loading ? 'Generating...' : 'Generate from Title' }}
      </button>
      <button @click="expandParagraph" :disabled="loading">
        {{ loading ? 'Expanding...' : 'Expand Paragraph' }}
      </button>
      <button @click="rewriteWithTone" :disabled="loading">
        {{ loading ? 'Rewriting...' : 'Rewrite with Tone' }}
      </button>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <div v-if="showModal" class="ai-suggestion-modal">
      <div class="modal-content">
        <h3>AI Suggestions</h3>
        <div v-html="suggestion"></div>
        <button @click="applySuggestion">Apply</button>
        <button @click="closeModal">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AIEditor',
  data() {
    return {
      loading: false,
      error: null,
      showModal: false,
      suggestion: '',
      currentAction: null
    }
  },
  methods: {
    async generateFromTitle() {
      await this.handleAIRequest('generate', { type: 'from_title' })
    },
    async expandParagraph() {
      await this.handleAIRequest('expand', { type: 'paragraph' })
    },
    async rewriteWithTone() {
      await this.handleAIRequest('rewrite', { type: 'tone_change' })
    },
    async handleAIRequest(action, params) {
      this.loading = true
      this.error = null
      this.currentAction = action

      try {
        const response = await fetch('/api/ai/generate', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            action,
            ...params,
            content: this.$parent.getCurrentContent()
          })
        })

        if (!response.ok) throw new Error('AI request failed')
        
        const data = await response.json()
        this.suggestion = data.suggestion
        this.showModal = true
      } catch (err) {
        this.error = 'Failed to get AI suggestions. Please try again.'
        console.error('AI Error:', err)
      } finally {
        this.loading = false
      }
    },
    applySuggestion() {
      this.$parent.applyAIContent(this.suggestion)
      this.closeModal()
    },
    closeModal() {
      this.showModal = false
      this.suggestion = ''
    }
  }
}
</script>

<style scoped>
.ai-editor {
  margin: 1rem 0;
}

.ai-controls {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

button {
  padding: 0.5rem 1rem;
  background: #4a6fa5;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.ai-suggestion-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 600px;
  width: 100%;
}

.error-message {
  color: #d32f2f;
  margin-top: 0.5rem;
}
</style>