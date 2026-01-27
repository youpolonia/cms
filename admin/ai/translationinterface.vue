<template>
  <div class="translation-interface">
    <div class="form-group">
      <label>Source Text</label>
      <textarea v-model="sourceText" class="form-control" rows="5"></textarea>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label>Source Language</label>
          <select v-model="sourceLanguage" class="form-control">
            <option value="auto">Auto Detect</option>
            <option v-for="lang in languages" :value="lang">{{ lang }}</option>
          </select>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>Target Language</label>
          <select v-model="targetLanguage" class="form-control">
            <option v-for="lang in languages" :value="lang">{{ lang }}</option>
          </select>
        </div>
      </div>
    </div>

    <button @click="translateText" class="btn btn-primary" :disabled="!canTranslate">
      Translate
    </button>

    <div v-if="translationResult" class="translation-result mt-3">
      <h5>Translation Result</h5>
      <div class="result-box">
        {{ translationResult.translated_text }}
      </div>
      <div class="meta-info">
        <small>
          Translated from {{ translationResult.source_language }} to {{ translationResult.target_language }}
          using {{ translationResult.model_used }}
        </small>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      sourceText: '',
      sourceLanguage: 'auto',
      targetLanguage: 'es',
      languages: [],
      translationResult: null,
      isLoading: false
    }
  },
  computed: {
    canTranslate() {
      return this.sourceText.trim().length > 0 && this.targetLanguage
    }
  },
  async created() {
    await this.fetchSupportedLanguages();
  },
  methods: {
    async fetchSupportedLanguages() {
      try {
        const response = await fetch('/api/ai/translation.php?action=languages');
        const data = await response.json();
        this.languages = data.languages;
      } catch (error) {
        console.error('Failed to fetch languages:', error);
      }
    },
    async translateText() {
      if (!this.canTranslate) return;

      this.isLoading = true;
      try {
        const response = await fetch('/api/ai/translation.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            text: this.sourceText,
            target_language: this.targetLanguage,
            source_language: this.sourceLanguage === 'auto' ? null : this.sourceLanguage
          })
        });
        this.translationResult = await response.json();
      } catch (error) {
        console.error('Translation failed:', error);
        alert('Translation failed. Please try again.');
      } finally {
        this.isLoading = false;
      }
    }
  }
}
</script>

<style scoped>
.translation-interface {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
.result-box {
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: #f9f9f9;
  min-height: 100px;
}
.meta-info {
  margin-top: 10px;
  color: #666;
}
</style>