<template>
  <Modal :show="true" @close="$emit('close')">
    <div class="preview-modal">
      <div class="modal-header">
        <h2>Preview: {{ template.name }}</h2>
        <button @click="$emit('close')" class="close-btn">
          &times;
        </button>
      </div>

      <div class="modal-body">
        <div class="preview-section">
          <h3>Subject</h3>
          <div class="preview-content subject">
            {{ template.subject }}
          </div>
        </div>

        <div class="preview-section">
          <h3>Content</h3>
          <div class="preview-content" v-html="renderedContent"></div>
        </div>

        <div class="variables-section" v-if="template.variables && template.variables.length">
          <h3>Available Variables</h3>
          <table>
            <thead>
              <tr>
                <th>Variable</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(desc, varName) in template.variables" :key="varName">
                <td>{{ `{{${varName}}}` }}</td>
                <td>{{ desc }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  template: {
    type: Object,
    required: true
  }
});

const renderedContent = computed(() => {
  // Simple markdown to html conversion for preview
  return props.template.content
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br>');
});
</script>

<style scoped>
.preview-modal {
  background: white;
  border-radius: 8px;
  width: 800px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #777;
}

.modal-body {
  padding: 20px;
}

.preview-section {
  margin-bottom: 25px;
}

.preview-section h3 {
  margin-bottom: 10px;
  color: #555;
}

.preview-content {
  padding: 15px;
  background: #f9f9f9;
  border-radius: 4px;
  border: 1px solid #eee;
}

.subject {
  font-weight: 500;
  font-size: 16px;
}

.variables-section {
  margin-top: 30px;
}

.variables-section h3 {
  margin-bottom: 15px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 10px 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  font-weight: 600;
  color: #555;
  background: #f5f5f5;
}
</style>