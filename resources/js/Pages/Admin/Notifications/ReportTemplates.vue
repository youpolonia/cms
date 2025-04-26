<template>
  <div class="report-templates">
    <div class="header">
      <h1>Custom Report Templates</h1>
      <p>Create and manage custom report templates for notifications</p>
    </div>

    <div class="template-actions">
      <button class="btn-new" @click="createNewTemplate">
        <i class="fas fa-plus"></i> New Template
      </button>
      <div class="search-filter">
        <input 
          type="text" 
          v-model="searchQuery"
          placeholder="Search templates..."
        />
        <select v-model="filterCategory">
          <option value="">All Categories</option>
          <option 
            v-for="category in categories" 
            :value="category"
            :key="category"
          >
            {{ category }}
          </option>
        </select>
      </div>
    </div>

    <div class="template-list">
      <div 
        class="template-card" 
        v-for="template in filteredTemplates" 
        :key="template.id"
        @click="editTemplate(template.id)"
      >
        <div class="card-header">
          <div class="template-name">{{ template.name }}</div>
          <div class="template-category">{{ template.category }}</div>
        </div>
        <div class="card-body">
          <div class="template-description">{{ template.description }}</div>
          <div class="template-stats">
            <div class="stat">
              <i class="fas fa-file-alt"></i>
              {{ template.fieldCount }} fields
            </div>
            <div class="stat">
              <i class="fas fa-clock"></i>
              Last used: {{ formatDate(template.lastUsed) }}
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button 
            class="btn-use"
            @click.stop="useTemplate(template)"
          >
            Use Template
          </button>
        </div>
      </div>
    </div>

    <div class="template-editor" v-if="showEditor">
      <div class="editor-header">
        <h2>{{ editingTemplate.id ? 'Edit Template' : 'New Template' }}</h2>
        <div class="editor-actions">
          <button class="btn-save" @click="saveTemplate">
            Save Template
          </button>
          <button class="btn-cancel" @click="closeEditor">
            Cancel
          </button>
        </div>
      </div>

      <div class="editor-body">
        <div class="form-group">
          <label>Template Name</label>
          <input 
            type="text" 
            v-model="editingTemplate.name"
            placeholder="Enter template name"
          />
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea 
            v-model="editingTemplate.description"
            placeholder="Enter template description"
          ></textarea>
        </div>

        <div class="form-group">
          <label>Category</label>
          <select v-model="editingTemplate.category">
            <option 
              v-for="category in categories" 
              :value="category"
              :key="category"
            >
              {{ category }}
            </option>
          </select>
        </div>

        <div class="available-fields">
          <h3>Available Fields</h3>
          <div class="fields-list">
            <div 
              class="field-item" 
              v-for="field in availableFields" 
              :key="field.id"
              @click="toggleField(field)"
              :class="{ selected: isFieldSelected(field) }"
            >
              <div class="field-name">{{ field.name }}</div>
              <div class="field-type">{{ field.type }}</div>
            </div>
          </div>
        </div>

        <div class="selected-fields">
          <h3>Selected Fields</h3>
          <div 
            class="fields-list"
            v-if="editingTemplate.fields.length > 0"
          >
            <div 
              class="field-item" 
              v-for="field in editingTemplate.fields" 
              :key="field.id"
            >
              <div class="field-name">{{ field.name }}</div>
              <div class="field-type">{{ field.type }}</div>
              <button 
                class="btn-remove"
                @click="removeField(field)"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="empty-state" v-else>
            No fields selected. Drag and drop fields from the available fields list.
          </div>
        </div>

        <div class="preview-section">
          <h3>Template Preview</h3>
          <div class="preview-container">
            <div class="preview-header">
              <div class="preview-title">{{ previewTitle }}</div>
              <div class="preview-date">{{ previewDate }}</div>
            </div>
            <table class="preview-table">
              <thead>
                <tr>
                  <th v-for="field in editingTemplate.fields" :key="field.id">
                    {{ field.name }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td v-for="field in editingTemplate.fields" :key="field.id">
                    {{ field.sampleData }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const templates = ref([]);
const searchQuery = ref('');
const filterCategory = ref('');
const showEditor = ref(false);
const editingTemplate = ref({
  id: null,
  name: '',
  description: '',
  category: '',
  fields: []
});

const availableFields = ref([
  { id: 1, name: 'Notification ID', type: 'text', sampleData: 'NTF-12345' },
  { id: 2, name: 'Title', type: 'text', sampleData: 'System Update' },
  { id: 3, name: 'Content', type: 'text', sampleData: 'Lorem ipsum...' },
  { id: 4, name: 'Created At', type: 'date', sampleData: '2025-04-20' },
  { id: 5, name: 'Status', type: 'text', sampleData: 'Delivered' },
  { id: 6, name: 'Recipient Count', type: 'number', sampleData: '125' },
  { id: 7, name: 'Open Rate', type: 'percentage', sampleData: '78%' },
  { id: 8, name: 'Click Rate', type: 'percentage', sampleData: '32%' },
  { id: 9, name: 'Priority', type: 'text', sampleData: 'High' },
  { id: 10, name: 'Category', type: 'text', sampleData: 'System' }
]);

const categories = ref([
  'System',
  'Marketing',
  'User Engagement',
  'Analytics',
  'Administrative'
]);

const filteredTemplates = computed(() => {
  return templates.value.filter(template => {
    const matchesSearch = template.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         template.description.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesCategory = filterCategory.value === '' || 
                           template.category === filterCategory.value;
    return matchesSearch && matchesCategory;
  });
});

const previewTitle = computed(() => {
  return editingTemplate.value.name || 'Untitled Report';
});

const previewDate = computed(() => {
  return new Date().toLocaleDateString();
});

const loadTemplates = async () => {
  try {
    const response = await axios.get('/api/notifications/reports/templates');
    templates.value = response.data;
  } catch (error) {
    console.error('Error loading templates:', error);
  }
};

const createNewTemplate = () => {
  editingTemplate.value = {
    id: null,
    name: '',
    description: '',
    category: categories.value[0],
    fields: []
  };
  showEditor.value = true;
};

const editTemplate = (id) => {
  const template = templates.value.find(t => t.id === id);
  if (template) {
    editingTemplate.value = { ...template };
    showEditor.value = true;
  }
};

const closeEditor = () => {
  showEditor.value = false;
};

const saveTemplate = async () => {
  try {
    if (editingTemplate.value.id) {
      await axios.put(`/api/notifications/reports/templates/${editingTemplate.value.id}`, editingTemplate.value);
    } else {
      await axios.post('/api/notifications/reports/templates', editingTemplate.value);
    }
    await loadTemplates();
    showEditor.value = false;
  } catch (error) {
    console.error('Error saving template:', error);
  }
};

const useTemplate = (template) => {
  // Navigate to report generation with this template
  this.$router.push(`/reports/generate?template=${template.id}`);
};

const toggleField = (field) => {
  if (isFieldSelected(field)) {
    removeField(field);
  } else {
    addField(field);
  }
};

const isFieldSelected = (field) => {
  return editingTemplate.value.fields.some(f => f.id === field.id);
};

const addField = (field) => {
  if (!isFieldSelected(field)) {
    editingTemplate.value.fields.push(field);
  }
};

const removeField = (field) => {
  editingTemplate.value.fields = editingTemplate.value.fields.filter(f => f.id !== field.id);
};

const formatDate = (dateString) => {
  if (!dateString) return 'Never';
  return new Date(dateString).toLocaleDateString();
};

onMounted(() => {
  loadTemplates();
});
</script>

<style scoped>
.report-templates {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  margin-bottom: 30px;
}

.header h1 {
  margin-bottom: 5px;
}

.template-actions {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.btn-new {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  background: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  gap: 8px;
}

.search-filter {
  display: flex;
  gap: 10px;
}

.search-filter input {
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  min-width: 200px;
}

.search-filter select {
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  min-width: 150px;
}

.template-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.template-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: transform 0.2s;
}

.template-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.card-header {
  padding: 15px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.template-name {
  font-weight: 500;
  font-size: 16px;
  margin-bottom: 5px;
}

.template-category {
  font-size: 12px;
  color: #64748b;
}

.card-body {
  padding: 15px;
}

.template-description {
  margin-bottom: 15px;
  font-size: 14px;
  color: #475569;
}

.template-stats {
  display: flex;
  gap: 15px;
  font-size: 12px;
  color: #64748b;
}

.template-stats .stat {
  display: flex;
  align-items: center;
  gap: 5px;
}

.card-footer {
  padding: 10px 15px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: flex-end;
}

.btn-use {
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  background: #10b981;
  color: white;
}

.template-editor {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  flex-direction: column;
  z-index: 1000;
}

.editor-header {
  background: white;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
}

.editor-actions {
  display: flex;
  gap: 10px;
}

.btn-save {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  background: #10b981;
  color: white;
}

.btn-cancel {
  padding: 8px 15px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  background: white;
  color: #475569;
}

.editor-body {
  flex: 1;
  background: white;
  overflow-y: auto;
  padding: 20px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: auto auto 1fr;
  gap: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  font-size: 14px;
}

.form-group textarea {
  min-height: 80px;
}

.available-fields,
.selected-fields {
  grid-column: span 1;
}

.fields-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 10px;
  margin-top: 10px;
}

.field-item {
  padding: 10px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.field-item.selected {
  background: #f0f9ff;
  border-color: #3b82f6;
}

.field-name {
  font-weight: 500;
  margin-bottom: 3px;
}

.field-type {
  font-size: 12px;
  color: #64748b;
}

.btn-remove {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  float: right;
}

.empty-state {
  padding: 20px;
  text-align: center;
  color: #64748b;
  font-size: 14px;
  border: 1px dashed #e2e8f0;
  border-radius: 4px;
  margin-top: 10px;
}

.preview-section {
  grid-column: span 2;
}

.preview-container {
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  padding: 20px;
  margin-top: 10px;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}

.preview-title {
  font-weight: 500;
  font-size: 18px;
}

.preview-date {
  color: #64748b;
  font-size: 14px;
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
}

.preview-table th,
.preview-table td {
  padding: 10px;
  border: 1px solid #e2e8f0;
  text-align: left;
}

.preview-table th {
  background: #f8fafc;
  font-weight: 500;
}
</style>