<template>
  <div class="block-templates">
    <h3>Available Blocks</h3>
    <div class="templates-grid">
      <div 
        v-for="template in templates" 
        :key="template.type"
        class="template-item"
        @click="$emit('select', template)"
      >
        <div class="template-icon">
          <component :is="template.icon" />
        </div>
        <div class="template-label">{{ template.label }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const templates = ref([
  {
    type: 'text-block',
    label: 'Text Block',
    icon: 'TextIcon',
    defaultContent: { text: '' },
    defaultSettings: {}
  },
  {
    type: 'image-block',
    label: 'Image Block',
    icon: 'ImageIcon',
    defaultContent: { src: '', alt: '' },
    defaultSettings: {}
  },
  {
    type: 'video-block',
    label: 'Video Block',
    icon: 'VideoIcon',
    defaultContent: { url: '' },
    defaultSettings: {}
  },
  {
    type: 'columns-block',
    label: 'Columns',
    icon: 'ColumnsIcon',
    defaultContent: { columns: 2, content: ['', ''] },
    defaultSettings: {}
  }
])
</script>

<style scoped>
.block-templates {
  width: 250px;
  padding: 1rem;
  background: #f5f5f5;
  border-radius: 4px;
}
.templates-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  margin-top: 1rem;
}
.template-item {
  padding: 0.5rem;
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
  text-align: center;
}
.template-item:hover {
  background: #f0f0f0;
}
.template-icon {
  font-size: 1.5rem;
  margin-bottom: 0.25rem;
}
</style>