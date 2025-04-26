<template>
  <div class="workflow-history">
    <div class="header">
      <h1>Workflow History</h1>
      <p>Track all changes and activities in the notification system</p>
    </div>

    <div class="controls">
      <div class="filters">
        <div class="form-group">
          <label>Action Type</label>
          <Multiselect
            v-model="filters.actionTypes"
            :options="actionTypeOptions"
            :multiple="true"
            :close-on-select="false"
            placeholder="Select action types"
          />
        </div>

        <div class="form-group">
          <label>User</label>
          <Multiselect
            v-model="filters.users"
            :options="userOptions"
            :multiple="true"
            :close-on-select="false"
            placeholder="Select users"
          />
        </div>

        <div class="form-group">
          <label>Date Range</label>
          <div class="date-range">
            <DatePicker 
              v-model="filters.dateRange.start" 
              placeholder="Start Date"
            />
            <span class="separator">to</span>
            <DatePicker 
              v-model="filters.dateRange.end" 
              placeholder="End Date"
            />
          </div>
        </div>

        <div class="form-group">
          <label>Entity Type</label>
          <Multiselect
            v-model="filters.entityTypes"
            :options="entityTypeOptions"
            :multiple="true"
            :close-on-select="false"
            placeholder="Select entity types"
          />
        </div>

        <div class="form-group">
          <label>Search</label>
          <input 
            type="text" 
            v-model="filters.searchQuery"
            placeholder="Search in details..."
          />
        </div>

        <button 
          class="btn-apply"
          @click="applyFilters"
        >
          Apply Filters
        </button>

        <button 
          class="btn-reset"
          @click="resetFilters"
        >
          Reset
        </button>
      </div>

      <div class="export">
        <button 
          class="btn-export"
          @click="exportHistory"
        >
          Export History
        </button>
      </div>
    </div>

    <div class="history-list">
      <div class="history-item" v-for="item in historyItems" :key="item.id">
        <div class="item-header">
          <div class="action-type" :class="getActionTypeClass(item.actionType)">
            {{ formatActionType(item.actionType) }}
          </div>
          <div class="timestamp">
            {{ formatDateTime(item.timestamp) }}
          </div>
          <div class="user">
            <img :src="item.user.avatar" class="avatar" v-if="item.user.avatar">
            <div class="user-info">
              <div class="name">{{ item.user.name }}</div>
              <div class="role">{{ item.user.role }}</div>
            </div>
          </div>
        </div>

        <div class="item-content">
          <div class="entity">
            <span class="label">Entity:</span>
            <span class="value">{{ item.entityType }} #{{ item.entityId }}</span>
            <span class="name" v-if="item.entityName">{{ item.entityName }}</span>
          </div>

          <div class="details">
            <div class="details-row" v-if="item.details">
              <span class="label">Details:</span>
              <span class="value">{{ item.details }}</span>
            </div>

            <div class="changes" v-if="item.changes && item.changes.length > 0">
              <div class="changes-header">
                <span class="label">Changes:</span>
                <button 
                  class="btn-toggle"
                  @click="toggleChanges(item.id)"
                >
                  {{ expandedItems.includes(item.id) ? 'Hide' : 'Show' }} Changes
                </button>
              </div>

              <div class="changes-list" v-if="expandedItems.includes(item.id)">
                <div class="change" v-for="(change, index) in item.changes" :key="index">
                  <div class="field">
                    <span class="name">{{ change.field }}</span>
                    <span class="type">{{ change.type }}</span>
                  </div>
                  <div class="diff">
                    <div class="old-value" v-if="change.oldValue">
                      <span class="label">Old:</span>
                      <span class="value">{{ formatChangeValue(change.oldValue) }}</span>
                    </div>
                    <div class="new-value">
                      <span class="label">New:</span>
                      <span class="value">{{ formatChangeValue(change.newValue) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="item-actions">
          <button 
            class="btn-view"
            @click="viewEntity(item)"
          >
            View {{ item.entityType }}
          </button>
          <button 
            class="btn-revert"
            @click="revertChanges(item)"
            v-if="item.actionType === 'update'"
          >
            Revert Changes
          </button>
        </div>
      </div>
    </div>

    <div class="pagination">
      <button 
        class="btn-prev"
        @click="prevPage"
        :disabled="currentPage === 1"
      >
        Previous
      </button>
      <span class="page-info">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button 
        class="btn-next"
        @click="nextPage"
        :disabled="currentPage === totalPages"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import DatePicker from './components/DatePicker.vue';
import Multiselect from './components/Multiselect.vue';

const filters = ref({
  actionTypes: [],
  users: [],
  dateRange: {
    start: null,
    end: null
  },
  entityTypes: [],
  searchQuery: ''
});

const actionTypeOptions = ref([
  { value: 'create', label: 'Create' },
  { value: 'update', label: 'Update' },
  { value: 'delete', label: 'Delete' },
  { value: 'publish', label: 'Publish' },
  { value: 'approve', label: 'Approve' },
  { value: 'reject', label: 'Reject' },
  { value: 'schedule', label: 'Schedule' },
  { value: 'send', label: 'Send' }
]);

const entityTypeOptions = ref([
  { value: 'notification', label: 'Notification' },
  { value: 'template', label: 'Template' },
  { value: 'segment', label: 'Segment' },
  { value: 'export', label: 'Export' },
  { value: 'workflow', label: 'Workflow' }
]);

const userOptions = ref([
  { value: 1, label: 'Admin User' },
  { value: 2, label: 'Content Manager' },
  { value: 3, label: 'Marketing User' }
]);

const historyItems = ref([]);
const expandedItems = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const isLoading = ref(false);

const loadHistory = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/workflow/history', {
      params: {
        page: currentPage.value,
        ...filters.value,
        startDate: filters.value.dateRange.start,
        endDate: filters.value.dateRange.end
      }
    });
    
    historyItems.value = response.data.items;
    totalPages.value = response.data.totalPages;
  } catch (error) {
    console.error('Error loading workflow history:', error);
  } finally {
    isLoading.value = false;
  }
};

const applyFilters = () => {
  currentPage.value = 1;
  loadHistory();
};

const resetFilters = () => {
  filters.value = {
    actionTypes: [],
    users: [],
    dateRange: {
      start: null,
      end: null
    },
    entityTypes: [],
    searchQuery: ''
  };
  currentPage.value = 1;
  loadHistory();
};

const exportHistory = async () => {
  try {
    const response = await axios.get('/api/workflow/history/export', {
      params: filters.value,
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'workflow_history.csv');
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Error exporting workflow history:', error);
  }
};

const toggleChanges = (id) => {
  const index = expandedItems.value.indexOf(id);
  if (index === -1) {
    expandedItems.value.push(id);
  } else {
    expandedItems.value.splice(index, 1);
  }
};

const viewEntity = (item) => {
  // Navigate to the entity view based on type
  switch(item.entityType) {
    case 'notification':
      this.$router.push(`/notifications/${item.entityId}`);
      break;
    case 'template':
      this.$router.push(`/templates/${item.entityId}`);
      break;
    case 'segment':
      this.$router.push(`/segments/${item.entityId}`);
      break;
    // Add other entity types as needed
  }
};

const revertChanges = async (item) => {
  if (!confirm('Are you sure you want to revert these changes?')) return;
  
  try {
    await axios.post(`/api/workflow/history/${item.id}/revert`);
    loadHistory();
  } catch (error) {
    console.error('Error reverting changes:', error);
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    loadHistory();
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    loadHistory();
  }
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleString();
};

const formatActionType = (type) => {
  const option = actionTypeOptions.value.find(opt => opt.value === type);
  return option ? option.label : type;
};

const getActionTypeClass = (type) => {
  return `action-${type}`;
};

const formatChangeValue = (value) => {
  if (value === null) return 'null';
  if (typeof value === 'object') return JSON.stringify(value);
  return value;
};

onMounted(() => {
  // Set default date range to last 7 days
  const end = new Date();
  const start = new Date();
  start.setDate(start.getDate() - 7);
  
  filters.value.dateRange = {
    start: start.toISOString().split('T')[0],
    end: end.toISOString().split('T')[0]
  };
  
  loadHistory();
});
</script>

<style scoped>
.workflow-history {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  margin-bottom: 30px;
}

.header h1 {
  margin-bottom: 5px;
}

.controls {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  align-items: flex-end;
}

.form-group {
  min-width: 180px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  font-size: 13px;
}

.form-group input[type="text"] {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.date-range {
  display: flex;
  align-items: center;
  gap: 10px;
}

.separator {
  color: #666;
  font-size: 13px;
}

.btn-apply,
.btn-reset,
.btn-export {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  height: 36px;
}

.btn-apply {
  background: #3b82f6;
  color: white;
}

.btn-reset {
  background: #f5f5f5;
  color: #333;
}

.btn-export {
  background: #10b981;
  color: white;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.history-item {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.item-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f0f0;
}

.action-type {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.action-create {
  background: #d1fae5;
  color: #065f46;
}

.action-update {
  background: #bfdbfe;
  color: #1e40af;
}

.action-delete {
  background: #fee2e2;
  color: #b91c1c;
}

.action-publish {
  background: #ddd6fe;
  color: #5b21b6;
}

.action-approve {
  background: #a7f3d0;
  color: #047857;
}

.action-reject {
  background: #fecaca;
  color: #dc2626;
}

.action-schedule {
  background: #c7d2fe;
  color: #3730a3;
}

.action-send {
  background: #bae6fd;
  color: #0369a1;
}

.timestamp {
  font-size: 13px;
  color: #666;
}

.user {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
}

.avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.name {
  font-size: 13px;
  font-weight: 500;
}

.role {
  font-size: 11px;
  color: #666;
}

.item-content {
  padding: 0 10px;
}

.entity {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 10px;
  font-size: 14px;
}

.entity .label {
  font-weight: 500;
}

.entity .name {
  color: #3b82f6;
}

.details-row {
  display: flex;
  gap: 5px;
  margin-bottom: 10px;
  font-size: 14px;
}

.details-row .label {
  font-weight: 500;
}

.changes-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 5px;
}

.btn-toggle {
  background: none;
  border: none;
  color: #3b82f6;
  cursor: pointer;
  font-size: 13px;
  padding: 0;
}

.changes-list {
  border: 1px solid #eee;
  border-radius: 6px;
  padding: 10px;
  background: #f9f9f9;
}

.change {
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}

.change:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: none;
}

.field {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 5px;
}

.field .name {
  font-weight: 500;
  font-size: 13px;
}

.field .type {
  font-size: 11px;
  color: #666;
  background: #f0f0f0;
  padding: 2px 5px;
  border-radius: 3px;
}

.diff {
  display: flex;
  gap: 15px;
}

.old-value,
.new-value {
  flex: 1;
  font-size: 13px;
}

.old-value .label,
.new-value .label {
  display: block;
  margin-bottom: 3px;
  font-weight: 500;
  font-size: 12px;
}

.old-value {
  color: #ef4444;
}

.new-value {
  color: #10b981;
}

.item-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #f0f0f0;
}

.btn-view,
.btn-revert {
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.btn-view {
  background: #3b82f6;
  color: white;
}

.btn-revert {
  background: #f59e0b;
  color: white;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  margin-top: 30px;
}

.btn-prev,
.btn-next {
  background: #f5f5f5;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.page-info {
  font-size: 14px;
  color: #666;
}
</style>