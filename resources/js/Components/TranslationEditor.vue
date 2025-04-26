<template>
  <div class="translation-editor">
    <div class="language-tabs">
      <button
        v-for="lang in enabledLanguages"
        :key="lang.code"
        :class="{ active: lang.code === activeLanguage }"
        @click="activeLanguage = lang.code"
      >
        {{ lang.name }}
        <span v-if="hasUnsavedChanges(lang.code)" class="unsaved-dot"></span>
      </button>
    </div>

    <div class="translation-content">
      <template v-if="activeLanguage === sourceLanguage">
        <div class="source-content">
          <slot name="source" :language="sourceLanguage"></slot>
        </div>
      </template>
      <template v-else>
        <div class="translation-actions">
          <button @click="copyFromSource" class="action-btn">
            Copy from {{ sourceLanguageName }}
          </button>
          <button @click="autoTranslate" class="action-btn" :disabled="autoTranslating">
            {{ autoTranslating ? 'Translating...' : 'Auto-translate' }}
          </button>
          <div v-if="translationError" class="error-message">
            {{ translationError }}
          </div>
        </div>

        <div class="translation-input">
          <slot 
            name="translation" 
            :language="activeLanguage"
            :value="translations[activeLanguage]"
            :update="updateTranslation"
          ></slot>
        </div>
      </template>
    </div>

    <div class="translation-footer">
      <button 
        @click="saveTranslations" 
        :disabled="!hasChanges"
        class="save-btn"
      >
        Save Translations
      </button>
      <button @click="cancelChanges" class="cancel-btn">
        Cancel
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    sourceLanguage: {
      type: String,
      default: 'en'
    },
    enabledLanguages: {
      type: Array,
      required: true,
      validator: (value) => {
        return value.every(lang => lang.code && lang.name);
      }
    },
    initialTranslations: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      activeLanguage: this.enabledLanguages[0]?.code || this.sourceLanguage,
      translations: JSON.parse(JSON.stringify(this.initialTranslations)),
      originalTranslations: JSON.parse(JSON.stringify(this.initialTranslations)),
      autoTranslating: false,
      translationError: null
    }
  },
  computed: {
    sourceLanguageName() {
      const lang = this.enabledLanguages.find(l => l.code === this.sourceLanguage);
      return lang?.name || this.sourceLanguage;
    },
    hasChanges() {
      return this.enabledLanguages.some(lang => {
        return this.hasUnsavedChanges(lang.code);
      });
    }
  },
  methods: {
    updateTranslation(value) {
      this.translations[this.activeLanguage] = value;
    },
    hasUnsavedChanges(languageCode) {
      const current = this.translations[languageCode];
      const original = this.originalTranslations[languageCode];
      
      if (current === undefined && original === undefined) return false;
      if (current === undefined || original === undefined) return true;
      
      return JSON.stringify(current) !== JSON.stringify(original);
    },
    copyFromSource() {
      this.$emit('copy-source', this.activeLanguage);
    },
    async autoTranslate() {
      this.autoTranslating = true;
      this.translationError = null;
      
      try {
        const response = await axios.post('/api/translate', {
          source_language: this.sourceLanguage,
          target_language: this.activeLanguage,
          content: this.translations[this.sourceLanguage]
        });
        
        this.translations[this.activeLanguage] = response.data.translation;
      } catch (error) {
        this.translationError = error.response?.data?.message || 'Translation failed';
      } finally {
        this.autoTranslating = false;
      }
    },
    saveTranslations() {
      this.$emit('save', this.translations);
      this.originalTranslations = JSON.parse(JSON.stringify(this.translations));
    },
    cancelChanges() {
      this.translations = JSON.parse(JSON.stringify(this.originalTranslations));
      this.$emit('cancel');
    }
  }
}
</script>

<style scoped>
.translation-editor {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}
.language-tabs {
  display: flex;
  border-bottom: 1px solid #ddd;
  background: #f5f5f5;
}
.language-tabs button {
  padding: 10px 15px;
  border: none;
  background: none;
  cursor: pointer;
  position: relative;
  border-right: 1px solid #ddd;
}
.language-tabs button.active {
  background: white;
  font-weight: 600;
}
.unsaved-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  background: #f59e0b;
  border-radius: 50%;
  margin-left: 5px;
}
.translation-content {
  padding: 15px;
  min-height: 200px;
}
.source-content {
  padding: 10px;
  background: #f8fafc;
  border-radius: 4px;
}
.translation-actions {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}
.action-btn {
  padding: 6px 12px;
  background: #e2e8f0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.action-btn:hover {
  background: #cbd5e1;
}
.action-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.error-message {
  color: #ef4444;
  margin-left: 10px;
}
.translation-input {
  margin-top: 10px;
}
.translation-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 15px;
  border-top: 1px solid #ddd;
  background: #f5f5f5;
}
.save-btn {
  padding: 8px 16px;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.save-btn:hover {
  background: #4338ca;
}
.save-btn:disabled {
  background: #a5b4fc;
  cursor: not-allowed;
}
.cancel-btn {
  padding: 8px 16px;
  background: #e2e8f0;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.cancel-btn:hover {
  background: #cbd5e1;
}
</style>