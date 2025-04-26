<template>
  <AdminLayout>
    <div class="notification-templates">
      <div class="header">
        <h1>Notification Templates</h1>
        <router-link 
          to="/admin/notifications/templates/create" 
          class="btn btn-primary"
        >
          Create Template
        </router-link>
      </div>

      <div class="filters">
        <div class="filter-group">
          <label>Type</label>
          <select v-model="filters.type" @change="fetchTemplates">
            <option value="">All Types</option>
            <option 
              v-for="type in templateTypes" 
              :value="type"
              :key="type"
            >
              {{ type }}
            </option>
          </select>
        </div>

        <div class="filter-group">
          <label>Status</label>
          <select v-model="filters.active" @change="fetchTemplates">
            <option :value="null">All</option>
            <option :value="true">Active</option>
            <option :value="false">Inactive</option>
          </select>
        </div>
      </div>

      <div class="template-list">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Type</th>
              <th>Status</th>
              <th>Last Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="template in templates" :key="template.id">
              <td>{{ template.name }}</td>
              <td>{{ template.notification_type }}</td>
              <td>
                <span :class="['status', template.is_active ? 'active' : 'inactive']">
                  {{ template.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>{{ formatDate(template.updated_at) }}</td>
              <td class="actions">
                <router-link 
                  :to="`/admin/notifications/templates/${template.id}/edit`"
                  class="btn btn-sm btn-edit"
                >
                  Edit
                </router-link>
                <button 
                  v-if="!template.is_system"
                  @click="confirmDelete(template)"
                  class="btn btn-sm btn-danger"
                >
                  Delete
                </button>
                <button 
                  @click="previewTemplate(template)"
                  class="btn btn-sm btn-preview"
                >
                  Preview
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <Pagination 
          :current-page="currentPage"
          :last-page="lastPage"
          @page-changed="handlePageChange"
        />
      </div>

      <PreviewModal 
        v-if="showPreview"
        :template="previewTemplateData"
        @close="showPreview = false"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PreviewModal from './PreviewModal.vue';
import axios from 'axios';
import { formatDate } from '@/utils/date';

const router = useRouter();
const templates = ref([]);
const templateTypes = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const showPreview = ref(false);
const previewTemplateData = ref(null);

const filters = ref({
  type: '',
  active: null
});

const fetchTemplates = async () => {
  try {
    const params = {
      page: currentPage.value,
      ...filters.value
    };

    const response = await axios.get('/api/notification-templates', { params });
    templates.value = response.data.data;
    lastPage.value = response.data.last_page;

    // Extract unique template types
    if (response.data.data.length > 0) {
      const types = new Set(response.data.data.map(t => t.notification_type));
      templateTypes.value = Array.from(types);
    }
  } catch (error) {
    console.error('Error fetching templates:', error);
  }
};

const confirmDelete = (template) => {
  if (confirm(`Are you sure you want to delete "${template.name}"?`)) {
    deleteTemplate(template.id);
  }
};

const deleteTemplate = async (id) => {
  try {
    await axios.delete(`/api/notification-templates/${id}`);
    fetchTemplates();
  } catch (error) {
    console.error('Error deleting template:', error);
  }
};

const previewTemplate = (template) => {
  previewTemplateData.value = template;
  showPreview.value = true;
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchTemplates();
};

onMounted(() => {
  fetchTemplates();
});
</script>

<style scoped>
.notification-templates {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.template-list {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  font-weight: 600;
  color: #555;
}

.status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.status.active {
  background: #e6f7ee;
  color: #10b981;
}

.status.inactive {
  background: #fef2f2;
  color: #ef4444;
}

.actions {
  display: flex;
  gap: 8px;
}

.btn {
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 13px;
  cursor: pointer;
  border: none;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-edit {
  background: #e9d5ff;
  color: #7e22ce;
}

.btn-danger {
  background: #fee2e2;
  color: #dc2626;
}

.btn-preview {
  background: #dbeafe;
  color: #1d4ed8;
}
</style>