<template>
  <div class="ai-block">
    <h2>AI Content Generator</h2>
    <div class="prompt-section">
      <textarea 
        v-model="prompt" 
        placeholder="Enter your content prompt..."
        class="prompt-input"
      ></textarea>
      <button @click="generateContent" class="generate-btn">
        Generate
      </button>
    </div>

    <div v-if="loading" class="loading">Generating content...</div>
    
    <div v-if="generatedContent" class="result-section">
      <h3>Generated Content</h3>
      <div class="content-preview" v-html="generatedContent"></div>
      
      <div class="suggestions">
        <h4>Content Suggestions</h4>
        <button 
          v-for="suggestion in suggestions" 
          :key="suggestion"
          @click="applySuggestion(suggestion)"
          class="suggestion-btn"
        >
          {{ suggestion }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const prompt = ref('');
    const generatedContent = ref('');
    const loading = ref(false);
    const suggestions = ref([]);

    const generateContent = async () => {
      loading.value = true;
      try {
        const response = await axios.post('/api/ai/generate-block', {
          prompt: prompt.value
        });
        generatedContent.value = response.data.content;
        suggestions.value = response.data.suggestions || [];
      } catch (error) {
        console.error('Error generating content:', error);
        alert('Failed to generate content');
      } finally {
        loading.value = false;
      }
    };

    const applySuggestion = (suggestion) => {
      prompt.value = suggestion;
      generateContent();
    };

    return {
      prompt,
      generatedContent,
      loading,
      suggestions,
      generateContent,
      applySuggestion
    };
  }
};
</script>

<style scoped>
.ai-block {
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin: 20px 0;
}

.prompt-section {
  margin-bottom: 20px;
}

.prompt-input {
  width: 100%;
  min-height: 100px;
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.generate-btn {
  background-color: #4CAF50;
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.generate-btn:hover {
  background-color: #45a049;
}

.loading {
  padding: 10px;
  background-color: #f8f8f8;
  border-radius: 4px;
  margin: 10px 0;
}

.result-section {
  margin-top: 20px;
}

.content-preview {
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 4px;
  margin: 10px 0;
}

.suggestions {
  margin-top: 15px;
}

.suggestion-btn {
  display: block;
  width: 100%;
  padding: 8px;
  margin: 5px 0;
  background-color: #e7f3fe;
  border: 1px solid #d0e3ff;
  border-radius: 4px;
  cursor: pointer;
  text-align: left;
}

.suggestion-btn:hover {
  background-color: #d0e3ff;
}
</style>