<template>
  <div class="workflow-templates">
    <div class="header">
      <h2>Workflow Templates</h2>
      <div class="controls">
        <input v-model="searchQuery" placeholder="Search templates..." class="search-input" />
        <select v-model="selectedCategory" class="category-select">
          <option value="">All Categories</option>
          <option v-for="category in categories" :value="category">{{ category }}</option>
        </select>
      </div>
    </div>

    <div class="template-list">
      <div v-for="template in filteredTemplates" :key="template.id" class="template-card">
        <div class="template-info">
          <h3>{{ template.name }}</h3>
          <span class="category-badge">{{ template.category }}</span>
          <p>{{ template.description }}</p>
        </div>
        <div class="template-actions">
          <button @click="previewTemplate(template)" class="btn-preview">Preview</button>
          <button @click="useTemplate(template)" class="btn-use">Use Template</button>
        </div>
      </div>
    </div>

    <div v-if="showPreview" class="preview-modal">
      <div class="modal-content">
        <h3>{{ previewTemplate.name }}</h3>
        <pre>{{ JSON.stringify(previewTemplate.steps, null, 2) }}</pre>
        <button @click="showPreview = false" class="btn-close">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      templates: [],
      searchQuery: '',
      selectedCategory: '',
      showPreview: false,
      previewTemplate: {},
      categories: []
    }
  },
  computed: {
    filteredTemplates() {
      return this.templates.filter(template => {
        const matchesSearch = template.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             template.description.toLowerCase().includes(this.searchQuery.toLowerCase());
        const matchesCategory = !this.selectedCategory || template.category === this.selectedCategory;
        return matchesSearch && matchesCategory;
      });
    }
  },
  methods: {
    async loadTemplates() {
      try {
        const response = await fetch('/data/workflow_templates/');
        const files = await response.json();
        
        const loadedTemplates = [];
        for (const file of files) {
          const templateResponse = await fetch(`/data/workflow_templates/${file}`);
          const template = await templateResponse.json();
          template.id = file.replace('.json', '');
          loadedTemplates.push(template);
        }
        
        this.templates = loadedTemplates;
        this.categories = [...new Set(loadedTemplates.map(t => t.category))];
      } catch (error) {
        console.error('Error loading templates:', error);
      }
    },
    previewTemplate(template) {
      this.previewTemplate = template;
      this.showPreview = true;
    },
    useTemplate(template) {
      // Emit event to parent or handle template usage
      this.$emit('use-template', template);
    }
  },
  mounted() {
    this.loadTemplates();
  }
}
</script>

<style scoped>
.workflow-templates {
  padding: 20px;
}
.header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}
.controls {
  display: flex;
  gap: 10px;
}
.search-input, .category-select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
.template-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}
.template-card {
  border: 1px solid #ddd;
  border-radius: 5px;
  padding: 15px;
  display: flex;
  flex-direction: column;
}
.template-info {
  flex-grow: 1;
}
.template-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}
.btn-preview, .btn-use, .btn-close {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn-preview {
  background: #f0f0f0;
}
.btn-use {
  background: #007bff;
  color: white;
}
.btn-close {
  background: #dc3545;
  color: white;
}
.preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-content {
  background: white;
  padding: 20px;
  border-radius: 5px;
  max-width: 80%;
  max-height: 80%;
  overflow: auto;
}
.category-badge {
  display: inline-block;
  background: #e9ecef;
  padding: 3px 8px;
  border-radius: 10px;
  font-size: 0.8em;
  margin-left: 10px;
}
</style>