<template>
  <div class="seo-toolkit">
    <h1>SEO Toolkit</h1>
    
    <div class="tool-section">
      <h2>Meta Tags</h2>
      <div class="form-group">
        <label>Title</label>
        <input v-model="meta.title" type="text" class="form-control">
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea v-model="meta.description" class="form-control" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label>Keywords</label>
        <input v-model="meta.keywords" type="text" class="form-control">
      </div>
      <button @click="generateWithAI" class="btn btn-primary">Generate with AI</button>
    </div>

    <div class="tool-section">
      <h2>Content Analysis</h2>
      <textarea v-model="content" class="form-control" rows="5" placeholder="Paste content to analyze"></textarea>
      <button @click="analyzeContent" class="btn btn-primary">Analyze</button>
      <div v-if="analysisResults" class="analysis-results">
        <h3>Results</h3>
        <p>Keyword Density: {{ analysisResults.keywordDensity }}%</p>
        <p>Readability Score: {{ analysisResults.readabilityScore }}/100</p>
        <div v-if="analysisResults.suggestions">
          <h4>AI Suggestions</h4>
          <p>{{ analysisResults.suggestions }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const meta = ref({
      title: '',
      description: '',
      keywords: ''
    });

    const content = ref('');
    const analysisResults = ref(null);

    const generateWithAI = async () => {
      try {
        const response = await axios.post('/api/seo/generate-meta', {
          keywords: meta.value.keywords
        });
        meta.value = response.data;
      } catch (error) {
        console.error('Error generating meta tags:', error);
      }
    };

    const analyzeContent = async () => {
      try {
        const response = await axios.post('/api/seo/analyze', {
          content: content.value
        });
        analysisResults.value = response.data;
      } catch (error) {
        console.error('Error analyzing content:', error);
      }
    };

    return {
      meta,
      content,
      analysisResults,
      generateWithAI,
      analyzeContent
    };
  }
};
</script>

<style scoped>
.seo-toolkit {
  padding: 20px;
}

.tool-section {
  margin-bottom: 30px;
  padding: 15px;
  border: 1px solid #eee;
  border-radius: 5px;
}

.form-group {
  margin-bottom: 15px;
}

.form-control {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.btn {
  padding: 8px 15px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.analysis-results {
  margin-top: 15px;
  padding: 10px;
  background: #f8f9fa;
  border-radius: 4px;
}
</style>