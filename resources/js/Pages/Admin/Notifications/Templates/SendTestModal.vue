<template>
  <Modal :show="show" @close="$emit('close')">
    <div class="send-modal">
      <div class="modal-header">
        <h2>Send Test Notification</h2>
        <button @click="$emit('close')" class="close-btn">
          &times;
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Recipient Email *</label>
          <input 
            v-model="recipientEmail" 
            type="email" 
            required
            placeholder="Enter recipient email"
          >
          <div class="error" v-if="errors.recipient">{{ errors.recipient }}</div>
        </div>

        <div class="form-group">
          <label>Test Variables</label>
          <div 
            class="variable-item" 
            v-for="(desc, varName) in template.variables" 
            :key="varName"
          >
            <span class="var-name">{{ varName }}</span>
            <input 
              v-model="testVariables[varName]" 
              type="text" 
              :placeholder="desc"
            >
          </div>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" v-model="saveAsDraft">
            Save as draft (don't send immediately)
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button 
          @click="sendTest" 
          class="btn btn-primary"
          :disabled="isSending"
        >
          <span v-if="isSending">Sending...</span>
          <span v-else>Send Test</span>
        </button>
        <button 
          @click="previewTest" 
          class="btn btn-preview"
        >
          Preview
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

const emit = defineEmits(['close', 'preview']);

const recipientEmail = ref('');
const testVariables = ref({});
const saveAsDraft = ref(false);
const isSending = ref(false);
const errors = ref({});

const sendTest = async () => {
  if (!recipientEmail.value.trim()) {
    errors.value = { recipient: 'Recipient email is required' };
    return;
  }

  try {
    isSending.value = true;
    await axios.post(`/api/notification-templates/${props.template.id}/send-test`, {
      recipient: recipientEmail.value,
      variables: testVariables.value,
      save_as_draft: saveAsDraft.value
    });
    emit('close');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
    console.error('Error sending test:', error);
  } finally {
    isSending.value = false;
  }
};

const previewTest = () => {
  emit('preview', {
    variables: testVariables.value
  });
};
</script>

<style scoped>
.send-modal {
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

.form-group input[type="email"],
.form-group input[type="text"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.variable-item {
  margin-bottom: 15px;
}

.var-name {
  display: block;
  font-weight: 500;
  margin-bottom: 5px;
  color: #333;
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

.btn-preview {
  background: #f59e0b;
  color: white;
}

.btn-cancel {
  background: #f5f5f5;
  color: #333;
}
</style>