<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="export-modal">
      <div class="modal-header">
        <h2>Export Template</h2>
        <button @click="$emit('close')" class="close-btn">
          &times;
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Export Format *</label>
          <select v-model="exportFormat" class="format-select">
            <option value="json">JSON</option>
            <option value="zip">ZIP (with attachments)</option>
          </select>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="includeVersions">
            Include Version History
          </label>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="includeAssets">
            Include Media Assets
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button 
          @click="exportTemplate" 
          class="btn btn-primary"
          :disabled="isExporting"
        >
          <span v-if="isExporting">Exporting...</span>
          <span v-else>Export Template</span>
        </button>
        <button 
          @click="$emit('close')" 
          class="btn btn-cancel"
        >
          Cancel
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  template: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close']);

const exportFormat = ref('json');
const includeVersions = ref(true);
const includeAssets = ref(true);
const isExporting = ref(false);

const exportTemplate = async () => {
  try {
    isExporting.value = true;
    const response = await axios.post(
      `/api/notification-templates/${props.template.id}/export`,
      {
        format: exportFormat.value,
        include_versions: includeVersions.value,
        include_assets: includeAssets.value
      },
      { responseType: 'blob' }
    );

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute(
      'download',
      `${props.template.name.replace(/\s+/g, '_')}_export.${exportFormat.value}`
    );
    document.body.appendChild(link);
    link.click();
    link.remove();

    emit('close');
  } catch (error) {
    console.error('Error exporting template:', error);
  } finally {
    isExporting.value = false;
  }
};
</script>

<style scoped>
.export-modal {
  background: white;
  border-radius: 8px;
  width: 500px;
  max-width: 90vw;
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

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.format-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 20px;
  border-top: 1px solid #eee;
}

.btn {
  padding: 10px 20px;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  border: none;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-cancel {
  background: #f5f5f5;
  color: #333;
}
</style>