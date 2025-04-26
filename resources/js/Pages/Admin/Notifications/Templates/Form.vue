<template>
  <AdminLayout>
    <div class="template-form">
      <h1>{{ formTitle }}</h1>
      
      <form @submit.prevent="submitForm">
        <div class="form-section">
          <h2>Basic Information</h2>
          
          <div class="form-group">
            <label>Template Name *</label>
            <input 
              v-model="form.name" 
              type="text" 
              required
              placeholder="e.g. New User Welcome Email"
            >
            <span class="error" v-if="errors.name">{{ errors.name }}</span>
          </div>

          <div class="form-group">
            <label>Slug *</label>
            <input 
              v-model="form.slug" 
              type="text" 
              required
              placeholder="e.g. new-user-welcome"
            >
            <span class="error" v-if="errors.slug">{{ errors.slug }}</span>
          </div>

          <div class="form-group">
            <label>Notification Type *</label>
            <select v-model="form.notification_type" required>
              <option value="">Select Type</option>
              <option value="email">Email</option>
              <option value="sms">SMS</option>
              <option value="push">Push Notification</option>
              <option value="in_app">In-App</option>
            </select>
            <span class="error" v-if="errors.notification_type">{{ errors.notification_type }}</span>
          </div>

          <div class="form-group">
            <label>
              <input type="checkbox" v-model="form.is_active">
              Active Template
            </label>
          </div>
        </div>

        <div class="form-section">
          <h2>Content</h2>

          <div class="form-group">
            <label>Subject *</label>
            <input 
              v-model="form.subject" 
              type="text" 
              required
              placeholder="Notification subject/title"
            >
            <span class="error" v-if="errors.subject">{{ errors.subject }}</span>
          </div>

          <div class="form-group">
            <label>Content *</label>
            <textarea
              v-model="form.content"
              required
              placeholder="Notification content (supports markdown)"
              rows="10"
            ></textarea>
            <span class="error" v-if="errors.content">{{ errors.content }}</span>
          </div>

          <div class="form-group">
            <label>Variables</label>
            <VariableEditor 
              :variables="form.variables"
              @update="updateVariables"
            />
          </div>
        </div>

        <div class="form-actions">
          <button type="button" @click="previewTemplate" class="btn btn-preview">
            Preview
          </button>
          <button type="submit" class="btn btn-primary">
            {{ isEditing ? 'Update' : 'Create' }} Template
          </button>
        </div>
      </form>

      <PreviewModal 
        v-if="showPreview"
        :template="previewData"
        @close="showPreview = false"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PreviewModal from './PreviewModal.vue';
import VariableEditor from '@/Components/Forms/VariableEditor.vue';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => route.params.id !== undefined);

const formTitle = computed(() => 
  isEditing.value ? 'Edit Template' : 'Create New Template'
);

const form = ref({
  name: '',
  slug: '',
  subject: '',
  content: '',
  variables: {},
  notification_type: '',
  is_active: true,
  is_system: false
});

const errors = ref({});
const showPreview = ref(false);
const previewData = ref(null);

const loadTemplate = async (id) => {
  try {
    const response = await axios.get(`/api/notification-templates/${id}`);
    form.value = response.data;
  } catch (error) {
    console.error('Error loading template:', error);
  }
};

const submitForm = async () => {
  try {
    errors.value = {};
    
    const url = isEditing.value 
      ? `/api/notification-templates/${route.params.id}`
      : '/api/notification-templates';

    const method = isEditing.value ? 'put' : 'post';

    await axios[method](url, form.value);
    router.push('/admin/notifications/templates');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
    console.error('Error submitting form:', error);
  }
};

const previewTemplate = () => {
  previewData.value = {
    ...form.value,
    variables: form.value.variables || {}
  };
  showPreview.value = true;
};

const updateVariables = (variables) => {
  form.value.variables = variables;
};

onMounted(() => {
  if (isEditing.value) {
    loadTemplate(route.params.id);
  }
});
</script>

<style scoped>
.template-form {
  padding: 20px;
  max-width: 1000px;
  margin: 0 auto;
}

.form-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-section h2 {
  margin-bottom: 20px;
  color: #555;
  font-size: 18px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-group input[type="text"],
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-group textarea {
  font-family: inherit;
}

.form-group input[type="checkbox"] {
  margin-right: 8px;
}

.error {
  color: #ef4444;
  font-size: 13px;
  margin-top: 5px;
  display: block;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
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

.btn-preview {
  background: #dbeafe;
  color: #1d4ed8;
}
</style>