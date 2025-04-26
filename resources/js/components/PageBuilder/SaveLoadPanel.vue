<template>
  <div class="save-load-panel">
    <div class="controls">
      <input v-model="pageName" placeholder="Page name" />
      <button @click="savePage">Save Page</button>
      <button @click="loadPage">Load Page</button>
      <button @click="saveAsTemplate">Save as Template</button>
    </div>

    <div class="templates" v-if="templates.length > 0">
      <h4>Templates</h4>
      <ul>
        <li v-for="template in templates" :key="template">
          <button @click="loadTemplate(template)">{{ template }}</button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  content: {
    type: Object,
    required: true
  }
});

const emits = defineEmits(['load']);

const pageName = ref('');
const templates = ref([]);

async function fetchTemplates() {
  try {
    const response = await axios.get('/api/page-builder/templates');
    templates.value = response.data.templates;
  } catch (error) {
    console.error('Failed to fetch templates:', error);
  }
}

async function savePage() {
  try {
    await axios.post('/api/page-builder/save', {
      name: pageName.value,
      content: props.content,
      is_template: false
    });
    alert('Page saved successfully!');
  } catch (error) {
    console.error('Failed to save page:', error);
    alert('Failed to save page');
  }
}

async function loadPage() {
  try {
    const response = await axios.post('/api/page-builder/load', {
      name: pageName.value,
      is_template: false
    });
    emits('load', response.data.content);
  } catch (error) {
    console.error('Failed to load page:', error);
    alert('Page not found');
  }
}

async function saveAsTemplate() {
  try {
    await axios.post('/api/page-builder/save', {
      name: pageName.value,
      content: props.content,
      is_template: true
    });
    alert('Template saved successfully!');
    await fetchTemplates();
  } catch (error) {
    console.error('Failed to save template:', error);
    alert('Failed to save template');
  }
}

async function loadTemplate(templateName) {
  try {
    const response = await axios.post('/api/page-builder/load', {
      name: templateName,
      is_template: true
    });
    emits('load', response.data.content);
  } catch (error) {
    console.error('Failed to load template:', error);
    alert('Template not found');
  }
}

onMounted(fetchTemplates);
</script>

<style scoped>
.save-load-panel {
  padding: 1rem;
  border: 1px solid #ddd;
  margin-bottom: 1rem;
}

.controls {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.templates ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.templates li {
  margin-bottom: 0.5rem;
}
</style>