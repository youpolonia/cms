<template>
  <div class="block-templates">
    <div class="template-actions">
      <button @click="saveAsTemplate">Save Current as Template</button>
      <input v-model="templateName" placeholder="Template name">
    </div>

    <div class="template-list">
      <div v-for="template in templates" :key="template.id" class="template-item">
        <div class="template-preview" @click="applyTemplate(template)">
          <component 
            :is="getBlockComponent(template.type)"
            :block="template"
            readonly />
        </div>
        <div class="template-meta">
          <span>{{ template.name }}</span>
          <button @click="deleteTemplate(template.id)">Delete</button>
        </div>
      </div>
    </div>

    <div v-if="showSaveModal" class="template-modal">
      <div class="modal-content">
        <h3>Save Template</h3>
        <label>
          Template Name:
          <input v-model="newTemplateName" />
        </label>
        <label>
          Category:
          <select v-model="newTemplateCategory">
            <option v-for="category in categories" :value="category">
              {{ category }}
            </option>
          </select>
        </label>
        <div class="modal-buttons">
          <button @click="confirmSaveTemplate">Save</button>
          <button @click="showSaveModal = false">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TextBlockEditor from './Blocks/TextBlockEditor.vue';
import ImageBlockEditor from './Blocks/ImageBlockEditor.vue';
import CtaBlockEditor from './Blocks/CtaBlockEditor.vue';

export default {
  components: {
    TextBlockEditor,
    ImageBlockEditor,
    CtaBlockEditor
  },
  props: {
    currentBlocks: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      templates: [],
      templateName: '',
      showSaveModal: false,
      newTemplateName: '',
      newTemplateCategory: 'general',
      categories: ['general', 'header', 'footer', 'cta', 'content']
    }
  },
  async mounted() {
    await this.loadTemplates();
  },
  methods: {
    getBlockComponent(type) {
      return {
        'text-block': TextBlockEditor,
        'image-block': ImageBlockEditor,
        'cta-block': CtaBlockEditor
      }[type];
    },
    async loadTemplates() {
      const response = await axios.get('/api/block-templates');
      this.templates = response.data.templates;
    },
    saveAsTemplate() {
      if (this.currentBlocks.length === 0) return;
      this.showSaveModal = true;
    },
    async confirmSaveTemplate() {
      await axios.post('/api/block-templates', {
        name: this.newTemplateName,
        category: this.newTemplateCategory,
        blocks: this.currentBlocks
      });
      this.showSaveModal = false;
      this.loadTemplates();
    },
    async deleteTemplate(id) {
      await axios.delete(`/api/block-templates/${id}`);
      this.loadTemplates();
    },
    applyTemplate(template) {
      this.$emit('template-applied', template.blocks);
    }
  }
}
</script>

<style scoped>
.block-templates {
  border-top: 1px solid #eee;
  margin-top: 20px;
  padding-top: 20px;
}
.template-actions {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}
.template-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 15px;
}
.template-item {
  border: 1px solid #ddd;
  padding: 10px;
  cursor: pointer;
}
.template-preview {
  pointer-events: none;
}
.template-meta {
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
}
.template-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  padding: 20px;
  width: 400px;
}
.modal-content label {
  display: block;
  margin-bottom: 10px;
}
.modal-content input,
.modal-content select {
  width: 100%;
  padding: 5px;
  margin-top: 5px;
}
.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 15px;
}
</style>