<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="import-modal">
      <div class="modal-header">
        <h2>Import Template</h2>
        <button @click="$emit('close')" class="close-btn">
          &times;
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Template File *</label>
          <div class="file-upload">
            <input 
              type="file" 
              ref="fileInput"
              accept=".json,.zip"
              @change="handleFileChange"
            >
            <div class="file-info" v-if="file">
              {{ file.name }} ({{ formatFileSize(file.size) }})
              <button @click="removeFile" class="remove-btn">
                Remove
              </button>
            </div>
            <div v-else class="file-placeholder">
              <button class="btn-select">
                Select File
              </button>
              <span>or drag and drop here</span>
            </div>
          </div>
          <div class="error" v-if="errors.file">{{ errors.file }}</div>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="overwriteExisting">
            Overwrite existing template if name matches
          </label>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="includeVersions">
            Import version history
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button 
          @click="importTemplate" 
          class="btn btn-primary"
          :disabled="!file || isImporting"
        >
          <span v-if="isImporting">Importing...</span>
          <span v-else>Import Template</span>
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
  }
});

const emit = defineEmits(['close', 'imported']);

const fileInput = ref(null);
const file = ref(null);
const overwriteExisting = ref(false);
const includeVersions = ref(true);
const isImporting = ref(false);
const errors = ref({});

const handleFileChange = (e) => {
  const selectedFile = e.target.files[0];
  if (selectedFile) {
    file.value = selectedFile;
  }
};

const removeFile = () => {
  file.value = null;
  fileInput.value.value = '';
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const importTemplate = async () => {
  if (!file.value) {
    errors.value = { file: 'Please select a file' };
    return;
  }

  const formData = new FormData();
  formData.append('file', file.value);
  formData.append('overwrite', overwriteExisting.value);
  formData.append('include_versions', includeVersions.value);

  try {
    isImporting.value = true;
    const response = await axios.post('/api/notification-templates/import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    emit('imported', response.data);
    emit('close');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
    console.error('Error importing template:', error);
  } finally {
    isImporting.value = false;
  }
};
</script>

<style scoped>
.import-modal {
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

.file-upload {
  border: 2px dashed #ddd;
  border-radius: 4px;
  padding: 20px;
  text-align: center;
}

.file-upload input[type="file"] {
  display: none;
}

.file-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f5f5f5;
  padding: 10px;
  border-radius: 4px;
}

.file-placeholder {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-select {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.remove-btn {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 13px;
}

.error {
  color: #ef4444;
  font-size: 13px;
  margin-top: 5px;
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