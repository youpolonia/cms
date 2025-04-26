<template>
  <div class="language-selector">
    <button 
      v-for="lang in availableLanguages"
      :key="lang.code"
      :class="{ active: lang.code === currentLanguage }"
      @click="changeLanguage(lang.code)"
    >
      {{ lang.name }}
      <span v-if="lang.code === currentLanguage" class="checkmark">✓</span>
    </button>

    <div v-if="showManagement" class="language-management">
      <button @click="openLanguageModal">Manage Languages</button>
    </div>

    <modal v-if="showLanguageModal" @close="showLanguageModal = false">
      <template #header>
        <h3>Language Management</h3>
      </template>
      <template #body>
        <div class="language-list">
          <div v-for="lang in allLanguages" :key="lang.code" class="language-item">
            <input 
              type="checkbox" 
              :id="`lang-${lang.code}`" 
              v-model="lang.enabled"
            >
            <label :for="`lang-${lang.code}`">{{ lang.name }} ({{ lang.code }})</label>
            <button 
              v-if="lang.code !== defaultLanguage"
              @click="removeLanguage(lang.code)"
              class="remove-btn"
            >
              Remove
            </button>
          </div>
        </div>
        <div class="add-language">
          <select v-model="newLanguageCode">
            <option value="">Select language to add</option>
            <option 
              v-for="lang in supportedLanguages" 
              :value="lang.code"
              :disabled="allLanguages.some(l => l.code === lang.code)"
            >
              {{ lang.name }} ({{ lang.code }})
            </option>
          </select>
          <button @click="addLanguage" :disabled="!newLanguageCode">
            Add Language
          </button>
        </div>
      </template>
      <template #footer>
        <button @click="saveLanguageSettings">Save Changes</button>
        <button @click="showLanguageModal = false">Cancel</button>
      </template>
    </modal>
  </div>
</template>

<script>
import Modal from '@/Components/Modal.vue';

export default {
  components: {
    Modal
  },
  props: {
    currentLanguage: {
      type: String,
      required: true
    },
    showManagement: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      defaultLanguage: 'en',
      availableLanguages: [],
      allLanguages: [],
      supportedLanguages: [
        { code: 'en', name: 'English' },
        { code: 'es', name: 'Spanish' },
        { code: 'fr', name: 'French' },
        { code: 'de', name: 'German' },
        { code: 'it', name: 'Italian' },
        { code: 'pt', name: 'Portuguese' },
        { code: 'ru', name: 'Russian' },
        { code: 'zh', name: 'Chinese' },
        { code: 'ja', name: 'Japanese' },
        { code: 'ar', name: 'Arabic' }
      ],
      showLanguageModal: false,
      newLanguageCode: ''
    }
  },
  async mounted() {
    await this.loadLanguages();
  },
  methods: {
    async loadLanguages() {
      try {
        const response = await axios.get('/api/languages');
        this.allLanguages = response.data.languages;
        this.availableLanguages = this.allLanguages.filter(l => l.enabled);
        this.defaultLanguage = response.data.default_language;
      } catch (error) {
        console.error('Failed to load languages:', error);
      }
    },
    changeLanguage(langCode) {
      this.$emit('language-changed', langCode);
    },
    openLanguageModal() {
      this.showLanguageModal = true;
    },
    async addLanguage() {
      const lang = this.supportedLanguages.find(l => l.code === this.newLanguageCode);
      if (lang) {
        this.allLanguages.push({
          ...lang,
          enabled: true
        });
        this.newLanguageCode = '';
      }
    },
    removeLanguage(langCode) {
      this.allLanguages = this.allLanguages.filter(l => l.code !== langCode);
    },
    async saveLanguageSettings() {
      try {
        await axios.post('/api/languages', {
          languages: this.allLanguages,
          default_language: this.defaultLanguage
        });
        await this.loadLanguages();
        this.showLanguageModal = false;
      } catch (error) {
        console.error('Failed to save language settings:', error);
      }
    }
  }
}
</script>

<style scoped>
.language-selector {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.language-selector button {
  padding: 6px 12px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}
.language-selector button:hover {
  background: #f5f5f5;
}
.language-selector button.active {
  background: #4f46e5;
  color: white;
  border-color: #4f46e5;
}
.checkmark {
  margin-left: 5px;
}
.language-management {
  margin-left: auto;
}
.language-list {
  margin-bottom: 20px;
}
.language-item {
  display: flex;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}
.language-item label {
  margin-left: 8px;
  flex-grow: 1;
}
.remove-btn {
  margin-left: 10px;
  color: #ef4444;
  background: none;
  border: none;
  cursor: pointer;
}
.add-language {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}
.add-language select {
  flex-grow: 1;
  padding: 8px;
  border-radius: 4px;
  border: 1px solid #ddd;
}
</style>