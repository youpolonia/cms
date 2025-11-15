<template>
  <div class="ai-preview-publish">
    <div class="state-indicator" :class="currentState">
      <span>{{ currentState }}</span>
    </div>

    <div class="preview-toggle">
      <button 
        @click="togglePreviewMode"
        :class="{ active: isPreviewMode }"
      >
        {{ isPreviewMode ? 'Exit Preview' : 'Preview Mode' }}
      </button>
    </div>

    <div v-if="isPreviewMode" class="ai-suggestions">
      <h3>AI Suggestions</h3>
      <div v-if="suggestionsLoading" class="loading">Loading suggestions...</div>
      <ul v-else>
        <li v-for="(suggestion, index) in suggestions" :key="index">
          {{ suggestion.text }}
          <button @click="applySuggestion(suggestion)">Apply</button>
        </li>
      </ul>
    </div>

    <div class="action-buttons">
      <button 
        v-if="currentState === 'draft'" 
        @click="transitionState('review')"
        class="review-btn"
      >
        Submit for Review
      </button>
      
      <button 
        v-if="currentState === 'review'" 
        @click="transitionState('published')"
        class="publish-btn"
      >
        Publish
      </button>
      
      <button 
        v-if="currentState === 'published'" 
        @click="transitionState('draft')"
        class="revert-btn"
      >
        Revert to Draft
      </button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  props: {
    contentId: {
      type: String,
      required: true
    }
  },

  setup(props) {
    const currentState = ref('draft');
    const isPreviewMode = ref(false);
    const suggestions = ref([]);
    const suggestionsLoading = ref(false);

    const fetchCurrentState = async () => {
      try {
        const response = await axios.get(`/api/content/${props.contentId}/state`);
        currentState.value = response.data.state;
      } catch (error) {
        console.error('Error fetching content state:', error);
      }
    };

    const togglePreviewMode = async () => {
      isPreviewMode.value = !isPreviewMode.value;
      if (isPreviewMode.value) {
        await fetchAISuggestions();
      }
    };

    const fetchAISuggestions = async () => {
      suggestionsLoading.value = true;
      try {
        const response = await axios.get(`/api/content/${props.contentId}/suggestions`);
        suggestions.value = response.data.suggestions;
      } catch (error) {
        console.error('Error fetching AI suggestions:', error);
      } finally {
        suggestionsLoading.value = false;
      }
    };

    const applySuggestion = (suggestion) => {
      // Emit event to parent to apply suggestion
      emit('apply-suggestion', suggestion);
    };

    const transitionState = async (newState) => {
      try {
        await axios.post(`/api/content/${props.contentId}/transition`, {
          newState
        });
        currentState.value = newState;
      } catch (error) {
        console.error('Error transitioning state:', error);
      }
    };

    onMounted(fetchCurrentState);

    return {
      currentState,
      isPreviewMode,
      suggestions,
      suggestionsLoading,
      togglePreviewMode,
      applySuggestion,
      transitionState
    };
  }
};
</script>

<style scoped>
.ai-preview-publish {
  border: 1px solid #ddd;
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 4px;
}

.state-indicator {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: bold;
  margin-bottom: 1rem;
}

.state-indicator.draft {
  background-color: #fff3cd;
  color: #856404;
}

.state-indicator.review {
  background-color: #cce5ff;
  color: #004085;
}

.state-indicator.published {
  background-color: #d4edda;
  color: #155724;
}

.preview-toggle {
  margin-bottom: 1rem;
}

.preview-toggle button {
  padding: 0.5rem 1rem;
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
}

.preview-toggle button.active {
  background-color: #007bff;
  color: white;
}

.ai-suggestions {
  margin-bottom: 1rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.ai-suggestions ul {
  list-style: none;
  padding: 0;
}

.ai-suggestions li {
  padding: 0.5rem;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.action-buttons button {
  padding: 0.5rem 1rem;
  margin-right: 0.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.review-btn {
  background-color: #17a2b8;
  color: white;
}

.publish-btn {
  background-color: #28a745;
  color: white;
}

.revert-btn {
  background-color: #dc3545;
  color: white;
}
</style>