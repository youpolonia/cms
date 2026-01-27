<template>
  <div class="ai-block">
    <div class="prompt-form">
      <h3>AI Layout Generator</h3>
      <textarea 
        v-model="prompt" 
        placeholder="Describe the layout you want to create..."
        class="prompt-input"
      ></textarea>
      <button @click="generateLayout" class="generate-btn">
        Generate Layout
      </button>
    </div>

    <div class="preview" v-if="loading">
      <div class="loading">Generating layout...</div>
    </div>

    <div class="preview" v-if="layout">
      <h4>Preview</h4>
      <div class="layout-preview" v-html="layout"></div>
      <button @click="saveLayout" class="save-btn">
        Use This Layout
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AIBlock',
  props: ['config'],
  data() {
    return {
      prompt: '',
      layout: null,
      loading: false
    }
  },
  methods: {
    async generateLayout() {
      this.loading = true;
      this.layout = null;
      
      try {
        const response = await fetch('/api/ai/generate-layout', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            prompt: this.prompt
          })
        });
        
        const result = await response.json();
        this.layout = result.layout;
      } catch (error) {
        console.error('Failed to generate layout:', error);
      } finally {
        this.loading = false;
      }
    },
    saveLayout() {
      this.$emit('save', {
        type: 'ai-generated',
        layout: this.layout
      });
    }
  }
}
</script>

<style scoped>
.ai-block {
  padding: 1rem;
}

.prompt-form {
  margin-bottom: 1rem;
}

.prompt-input {
  width: 100%;
  min-height: 100px;
  padding: 0.5rem;
  margin-bottom: 0.5rem;
}

.generate-btn, .save-btn {
  padding: 0.5rem 1rem;
  background: #4CAF50;
  color: white;
  border: none;
  cursor: pointer;
}

.preview {
  margin-top: 1rem;
  padding: 1rem;
  border: 1px solid #eee;
}

.loading {
  padding: 1rem;
  text-align: center;
  color: #666;
}

.layout-preview {
  border: 1px dashed #ccc;
  padding: 1rem;
  margin: 1rem 0;
}
</style>