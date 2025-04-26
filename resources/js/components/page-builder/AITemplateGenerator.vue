<script setup>
import { ref } from 'vue'
import { useGptSuggestion } from '@/Services/AIService'
import PresetRenderer from './PresetRenderer.vue'

const props = defineProps({
  currentContent: String,
  context: Object
})

const emit = defineEmits(['apply-template'])

const prompt = ref('')
const isLoading = ref(false)
const suggestions = ref([])
const features = [
  'header', 
  'stacked cards',
  'image grid',
  'text block with CTA',
  'testimonial'
]

async function generateSuggestions() {
  isLoading.value = true
  try {
    const gptPrompt = `Suggest 3 CMS block templates that would work well with: ${props.currentContent}. Features should include ${features.join(', ')}. Return suggested HTML/SCSS blocks in array.`
    suggestions.value = await useGptSuggestion(gptPrompt)
  } catch (error) {
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

function applySuggestion(template) {
  emit('apply-template', template)
}
</script>

<template>
  <div class="ai-templates">
    <h3 class="mb-4">AI Template Suggestions</h3>
    <div class="mb-4">
      <button 
        @click="generateSuggestions" 
        class="btn-primary"
        :disabled="isLoading"
      >
        {{ isLoading ? 'Generating...' : 'Generate Suggestions' }}
      </button>
    </div>

    <div v-if="suggestions.length > 0" class="suggestions-grid">
      <PresetRenderer
        v-for="(template, i) in suggestions"
        :key="i"
        :template="template"
        @use="applySuggestion"
      />
    </div>
  </div>
</template>

<style scoped>
.ai-templates {
  @apply p-4 border rounded-lg bg-gray-50;
}

.suggestions-grid {
  @apply grid gap-4;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}
</style>