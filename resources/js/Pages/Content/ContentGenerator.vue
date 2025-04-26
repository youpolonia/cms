<template>
  <div class="content-generator">
    <h2>AI Content Generator</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="md:col-span-2">
        <div class="mb-4">
          <label for="content-prompt" class="block mb-2">Enter your content prompt:</label>
          <textarea
            id="content-prompt"
            v-model="prompt"
            class="w-full p-2 border rounded"
            rows="4"
            placeholder="Describe the content you want to generate..."
          ></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
          <div>
            <label class="block mb-2">Output Format:</label>
            <select v-model="outputFormat" class="w-full p-2 border rounded">
              <option value="text">Plain Text</option>
              <option value="html">HTML</option>
              <option value="json">JSON</option>
            </select>
          </div>

          <div>
            <label class="block mb-2">Template:</label>
            <select v-model="template" class="w-full p-2 border rounded">
              <option value="content_suggestion">Content Suggestion</option>
              <option value="seo_optimization">SEO Optimization</option>
              <option value="content_enhancement">Content Enhancement</option>
              <option value="content_summary">Content Summary</option>
            </select>
          </div>

          <div class="flex items-end">
            <label class="flex items-center">
              <input type="checkbox" v-model="includeImages" class="mr-2">
              Include Images
            </label>
          </div>
        </div>

        <div class="mb-4">
          <button
            @click="generateContent"
            :disabled="isLoading"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            {{ isLoading ? 'Generating...' : 'Generate Content' }}
          </button>
        </div>

        <div v-if="error" class="p-4 mb-4 text-red-700 bg-red-100 rounded">
          {{ error }}
        </div>
      </div>

      <div class="md:col-span-1">
        <div v-if="suggestion" class="p-4 bg-gray-50 rounded">
          <h3 class="mb-2 font-semibold">Generated Content:</h3>
          
          <div v-if="outputFormat === 'html'" class="prose max-w-none" v-html="suggestion.html"></div>
          <div v-else-if="outputFormat === 'json'" class="text-sm font-mono">
            <pre>{{ JSON.stringify(suggestion, null, 2) }}</pre>
          </div>
          <div v-else class="prose max-w-none" v-html="formattedSuggestion"></div>

          <div v-if="images.length > 0" class="mt-4">
            <h4 class="mb-2 font-semibold">Generated Images:</h4>
            <div class="grid grid-cols-1 gap-2">
              <img
                v-for="(image, index) in images"
                :key="index"
                :src="image.url"
                class="w-full rounded border"
              >
            </div>
          </div>

          <div class="mt-4 text-sm text-gray-500">
            <p>Tokens used: {{ usage.tokens }}</p>
            <p>Estimated cost: ${{ usage.cost.toFixed(5) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      prompt: '',
      suggestion: '',
      images: [],
      isLoading: false,
      error: '',
      outputFormat: 'text',
      template: 'content_suggestion',
      includeImages: false,
      usage: {
        tokens: 0,
        cost: 0
      }
    };
  },
  computed: {
    formattedSuggestion() {
      return this.suggestion.replace(/\n/g, '<br>');
    }
  },
  methods: {
    async generateContent() {
      if (!this.prompt.trim()) {
        this.error = 'Please enter a prompt';
        return;
      }

      this.isLoading = true;
      this.error = '';
      this.suggestion = '';
      this.images = [];

      try {
        const response = await axios.post('/api/content/suggestions', {
          prompt: this.prompt,
          outputFormat: this.outputFormat,
          template: this.template,
          includeImages: this.includeImages
        });

        this.suggestion = response.data.content;
        this.images = response.data.images || [];
        this.usage = {
          tokens: response.data.usage.tokens,
          cost: response.data.usage.cost
        };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to generate content';
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.content-generator {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

pre {
  background: #f5f5f5;
  padding: 1rem;
  border-radius: 0.25rem;
  overflow-x: auto;
}

.prose img {
  max-width: 100%;
  height: auto;
}
</style>