<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="clone-modal">
      <div class="modal-header">
        <h2>Clone Template</h2>
        <button @click="$emit('close')" class="close-btn">
          &times;
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>New Template Name *</label>
          <input 
            v-model="newName" 
            type="text" 
            required
            placeholder="Enter new template name"
          >
          <div class="error" v-if="errors.name">{{ errors.name }}</div>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="includeVersions">
            Include Version History
          </label>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="isActive">
            Set as Active
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button 
          @click="cloneTemplate" 
          class="btn btn-primary"
          :disabled="isCloning"
        >
          <span v-if="isCloning">Cloning...</span>
          <span v-else>Clone Template</span>
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

const emit = defineEmits(['close', 'cloned']);

const newName = ref(`${props.template.name} (Copy)`);
const includeVersions = ref(true);
const isActive = ref(false);
const isCloning = ref(false);
const errors = ref({});

const cloneTemplate = async () => {
  if (!newName.value.trim()) {
    errors.value = { name: 'Template name is required' };
    return;
  }

  try {
    isCloning.value = true;
    const response = await axios.post(`/api/notification-templates/${props.template.id}/clone`, {
      name: newName.value,
      include_versions: includeVersions.value,
      is_active: isActive.value
    });
    emit('cloned', response.data);
    emit('close');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
    console.error('Error cloning template:', error);
  } finally {
    isCloning.value = false;
  }
};
</script>

<style scoped>
.clone-modal {
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

.form-group input[type="text"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-group input[type="checkbox"] {
  margin-right: 8px;
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