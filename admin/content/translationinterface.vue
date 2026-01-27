<template>
  <div class="translation-interface">
    <button @click="showModal = true" class="btn btn-secondary">
      <i class="fas fa-language"></i> Translate
    </button>

    <modal v-if="showModal" @close="showModal = false">
      <h3>Translate Content</h3>
      
      <div class="form-group">
        <label>Target Language</label>
        <select v-model="targetLang" class="form-control">
          <option v-for="lang in languages" :value="lang.code">
            {{ lang.name }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label>Action</label>
        <select v-model="action" class="form-control">
          <option value="replace">Replace original</option>
          <option value="append">Append as new block</option>
        </select>
      </div>

      <button 
        @click="translateContent" 
        :disabled="isTranslating"
        class="btn btn-primary"
      >
        <span v-if="isTranslating">
          <i class="fas fa-spinner fa-spin"></i> Translating...
        </span>
        <span v-else>Translate</span>
      </button>
    </modal>
  </div>
</template>

<script>
export default {
  props: ['content'],
  data() {
    return {
      showModal: false,
      targetLang: 'en',
      action: 'replace',
      isTranslating: false,
      languages: [
        { code: 'en', name: 'English' },
        { code: 'de', name: 'German' },
        { code: 'fr', name: 'French' },
        { code: 'es', name: 'Spanish' },
        { code: 'pl', name: 'Polish' }
      ]
    }
  },
  methods: {
    async translateContent() {
      this.isTranslating = true;
      try {
        const response = await this.$http.post('/api/translate.php', {
          text: this.content,
          target_lang: this.targetLang
        });

        this.$emit(this.action, response.data.translated_text);
        this.showModal = false;
      } catch (error) {
        console.error('Translation failed:', error);
        alert('Translation failed. Please try again.');
      } finally {
        this.isTranslating = false;
      }
    }
  }
}
</script>

<style scoped>
.translation-interface {
  display: inline-block;
  margin-left: 10px;
}
</style>