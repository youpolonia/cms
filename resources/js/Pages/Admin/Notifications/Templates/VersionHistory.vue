<template>
  <AdminLayout>
    <div class="version-history">
      <div class="header">
        <h1>Version History: {{ template.name }}</h1>
        <router-link 
          :to="`/admin/notifications/templates/${template.id}/edit`"
          class="btn btn-primary"
        >
          Back to Template
        </router-link>
      </div>

      <div class="version-list">
        <table>
          <thead>
            <tr>
              <th>Version</th>
              <th>Date</th>
              <th>Modified By</th>
              <th>Changes</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="version in versions" :key="version.id">
              <td>
                <span class="version-number">v{{ version.version_number }}</span>
                <span v-if="version.is_current" class="current-badge">Current</span>
              </td>
              <td>{{ formatDate(version.created_at) }}</td>
              <td>{{ version.user?.name || 'System' }}</td>
              <td>
                <div class="changes">
                  <div v-if="version.changes.includes('content')" class="change-tag">Content</div>
                  <div v-if="version.changes.includes('subject')" class="change-tag">Subject</div>
                  <div v-if="version.changes.includes('variables')" class="change-tag">Variables</div>
                </div>
                <div class="comment" v-if="version.comment">{{ version.comment }}</div>
              </td>
              <td class="actions">
                <button 
                  @click="compareWithCurrent(version)"
                  class="btn btn-sm btn-compare"
                >
                  Compare
                </button>
                <button 
                  v-if="!version.is_current"
                  @click="restoreVersion(version)"
                  class="btn btn-sm btn-restore"
                >
                  Restore
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

      <ComparisonModal 
        v-if="showComparison"
        :current="currentVersion"
        :version="selectedVersion"
        @close="showComparison = false"
        @restore="restoreVersion(selectedVersion)"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ComparisonModal from './ComparisonModal.vue';
import axios from 'axios';
import { formatDate } from '@/utils/date';

const route = useRoute();

const template = ref({});
const versions = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const showComparison = ref(false);
const currentVersion = ref(null);
const selectedVersion = ref(null);

const fetchTemplate = async () => {
  try {
    const response = await axios.get(`/api/notification-templates/${route.params.id}`);
    template.value = response.data;
  } catch (error) {
    console.error('Error fetching template:', error);
  }
};

const fetchVersions = async () => {
  try {
    const response = await axios.get(`/api/notification-templates/${route.params.id}/versions`, {
      params: { page: currentPage.value }
    });
    versions.value = response.data.data;
    lastPage.value = response.data.last_page;
  } catch (error) {
    console.error('Error fetching versions:', error);
  }
};

const compareWithCurrent = async (version) => {
  try {
    const response = await axios.get(`/api/notification-templates/${route.params.id}/current`);
    currentVersion.value = response.data;
    selectedVersion.value = version;
    showComparison.value = true;
  } catch (error) {
    console.error('Error comparing versions:', error);
  }
};

const restoreVersion = async (version) => {
  if (confirm(`Are you sure you want to restore version v${version.version_number}?`)) {
    try {
      await axios.post(`/api/notification-templates/${route.params.id}/restore`, {
        version_id: version.id
      });
      fetchVersions();
    } catch (error) {
      console.error('Error restoring version:', error);
    }
  }
};

const handlePageChange = (page) => {
  currentPage.value = page;
  fetchVersions();
};

onMounted(() => {
  fetchTemplate();
  fetchVersions();
});
</script>

<style scoped>
.version-history {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.version-list {
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

.version-number {
  font-weight: 500;
}

.current-badge {
  background: #10b981;
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 12px;
  margin-left: 8px;
}

.changes {
  display: flex;
  gap: 6px;
  margin-bottom: 5px;
}

.change-tag {
  background: #e0f2fe;
  color: #0369a1;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 12px;
}

.comment {
  color: #666;
  font-size: 13px;
  font-style: italic;
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

.btn-compare {
  background: #dbeafe;
  color: #1d4ed8;
}

.btn-restore {
  background: #e6f7ee;
  color: #10b981;
}
</style>