<template>
  <div class="translation-editor">
    <div class="language-selector">
      <label for="target-language">Translate to:</label>
      <select 
        id="target-language" 
        v-model="targetLanguage"
        @change="loadTranslation"
      >
        <option 
          v-for="lang in availableLanguages" 
          :key="lang.code"
          :value="lang.code"
        >
          {{ lang.name }}
        </option>
      </select>
    </div>

    <div class="editor-container">
      <div class="source-content">
        <h4>Source ({{ sourceLanguage }})</h4>
        <pre>{{ sourceContent }}</pre>
      </div>
      
      <div class="translation-content">
        <h4>Translation ({{ targetLanguage }})</h4>
        <textarea
          v-model="translationContent"
          @input="autoSave"
        ></textarea>
      </div>
    </div>

    <div class="actions">
      <button @click="saveTranslation">Save Translation</button>
      <button @click="autoTranslate">Auto Translate</button>
    </div>
  </div>
</template>

<script lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

interface Language {
  code: string
  name: string
}

export default {
  name: 'TranslationEditor',
  props: {
    contentId: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const route = useRoute()
    const sourceLanguage = ref('en')
    const targetLanguage = ref('')
    const sourceContent = ref({})
    const translationContent = ref({})
    const availableLanguages = ref<Language[]>([])
    const autoSaveTimer = ref<NodeJS.Timeout | null>(null)

    const loadContent = async () => {
      try {
        const response = await axios.get(`/api/contents/${props.contentId}`)
        sourceContent.value = response.data.content
        sourceLanguage.value = response.data.language
      } catch (error) {
        console.error('Error loading content:', error)
      }
    }

    const loadLanguages = async () => {
      try {
        const response = await axios.get('/api/languages')
        availableLanguages.value = response.data
        if (availableLanguages.value.length > 0) {
          targetLanguage.value = availableLanguages.value[0].code
        }
      } catch (error) {
        console.error('Error loading languages:', error)
      }
    }

    const loadTranslation = async () => {
      try {
        const response = await axios.get(`/api/translations/${props.contentId}?language=${targetLanguage.value}`)
        translationContent.value = response.data.translation || {}
      } catch (error) {
        console.error('Error loading translation:', error)
      }
    }

    const autoSave = () => {
      if (autoSaveTimer.value) {
        clearTimeout(autoSaveTimer.value)
      }
      autoSaveTimer.value = setTimeout(saveTranslation, 3000)
    }

    const saveTranslation = async () => {
      try {
        await axios.post(`/api/translations/${props.contentId}`, {
          language: targetLanguage.value,
          content: translationContent.value
        })
      } catch (error) {
        console.error('Error saving translation:', error)
      }
    }

    const autoTranslate = async () => {
      try {
        const response = await axios.post('/api/translations/translate', {
          source_language: sourceLanguage.value,
          target_language: targetLanguage.value,
          content: sourceContent.value
        })
        translationContent.value = response.data.translation
      } catch (error) {
        console.error('Error auto-translating:', error)
      }
    }

    onMounted(async () => {
      await loadContent()
      await loadLanguages()
      await loadTranslation()
    })

    return {
      sourceLanguage,
      targetLanguage,
      sourceContent,
      translationContent,
      availableLanguages,
      loadTranslation,
      autoSave,
      saveTranslation,
      autoTranslate
    }
  }
}
</script>

<style scoped>
.translation-editor {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.editor-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.source-content,
.translation-content {
  border: 1px solid #ddd;
  padding: 1rem;
  border-radius: 4px;
}

textarea {
  width: 100%;
  min-height: 300px;
  padding: 0.5rem;
}

.actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}
</style>