<template>
  <div class="analytics-export">
    <div class="header">
      <h1>Analytics Export</h1>
      <p>Export notification analytics data for reporting</p>
    </div>

    <div class="export-container">
      <div class="export-config">
        <div class="section">
          <h3>Export Configuration</h3>
          
          <div class="form-group">
            <label>Export Name</label>
            <input 
              type="text" 
              v-model="exportConfig.name" 
              placeholder="e.g. Weekly Engagement Report"
            >
          </div>

          <div class="form-group">
            <label>Format</label>
            <select v-model="exportConfig.format">
              <option value="csv">CSV</option>
              <option value="json">JSON</option>
              <option value="excel">Excel</option>
              <option value="pdf">PDF</option>
            </select>
          </div>

          <div class="form-group">
            <label>Date Range</label>
            <div class="date-range">
              <DatePicker 
                v-model="exportConfig.dateRange.start" 
                placeholder="Start Date"
              />
              <span class="separator">to</span>
              <DatePicker 
                v-model="exportConfig.dateRange.end" 
                placeholder="End Date"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Notification Types</label>
            <Multiselect
              v-model="exportConfig.notificationTypes"
              :options="notificationTypeOptions"
              :multiple="true"
              :close-on-select="false"
              placeholder="Select types"
            />
          </div>

          <div class="form-group">
            <label>Columns to Include</label>
            <div class="columns-selector">
              <div 
                class="column-option" 
                v-for="column in availableColumns" 
                :key="column.value"
              >
                <input 
                  type="checkbox" 
                  :id="column.value" 
                  v-model="exportConfig.columns" 
                  :value="column.value"
                >
                <label :for="column.value">{{ column.label }}</label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Filters</label>
            <div class="filters">
              <div class="filter" v-for="(filter, index) in exportConfig.filters" :key="index">
                <select v-model="filter.field" class="field-select">
                  <option 
                    v-for="field in filterableFields" 
                    :key="field.value" 
                    :value="field.value"
                  >
                    {{ field.label }}
                  </option>
                </select>
                <select v-model="filter.operator" class="operator-select">
                  <option 
                    v-for="op in getOperatorsForField(filter.field)" 
                    :key="op.value" 
                    :value="op.value"
                  >
                    {{ op.label }}
                  </option>
                </select>
                <component 
                  :is="getInputComponent(filter.field)" 
                  v-model="filter.value"
                  :field="filter.field"
                  :options="getOptionsForField(filter.field)"
                />
                <button 
                  class="btn-remove-filter"
                  @click="removeFilter(index)"
                >
                  Remove
                </button>
              </div>
              <button 
                class="btn-add-filter"
                @click="addFilter"
              >
                + Add Filter
              </button>
            </div>
          </div>

          <div class="form-group">
            <label>
              <input 
                type="checkbox" 
                v-model="exportConfig.scheduled"
              >
              Schedule Recurring Export
            </label>
            <div v-if="exportConfig.scheduled" class="scheduling-options">
              <div class="form-group">
                <label>Frequency</label>
                <select v-model="exportConfig.schedule.frequency">
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                </select>
              </div>
              <div class="form-group" v-if="exportConfig.schedule.frequency === 'weekly'">
                <label>Day of Week</label>
                <select v-model="exportConfig.schedule.dayOfWeek">
                  <option value="monday">Monday</option>
                  <option value="tuesday">Tuesday</option>
                  <option value="wednesday">Wednesday</option>
                  <option value="thursday">Thursday</option>
                  <option value="friday">Friday</option>
                </select>
              </div>
              <div class="form-group" v-if="exportConfig.schedule.frequency === 'monthly'">
                <label>Day of Month</label>
                <input 
                  type="number" 
                  v-model="exportConfig.schedule.dayOfMonth" 
                  min="1" 
                  max="31"
                >
              </div>
              <div class="form-group">
                <label>Time</label>
                <input 
                  type="time" 
                  v-model="exportConfig.schedule.time"
                >
              </div>
              <div class="form-group">
                <label>Recipients</label>
                <Multiselect
                  v-model="exportConfig.schedule.recipients"
                  :options="recipientOptions"
                  :multiple="true"
                  :close-on-select="false"
                  placeholder="Select recipients"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="export-preview">
        <div class="preview-header">
          <h3>Preview</h3>
          <button 
            class="btn-refresh"
            @click="refreshPreview"
            :disabled="previewLoading"
          >
            Refresh Preview
          </button>
        </div>

        <div class="preview-content">
          <div v-if="previewLoading" class="loading">
            Loading preview...
          </div>
          <div v-else-if="previewError" class="error">
            Error loading preview: {{ previewError }}
          </div>
          <div v-else-if="previewData.length === 0" class="empty">
            No data matches the current filters
          </div>
          <div v-else class="preview-table">
            <table>
              <thead>
                <tr>
                  <th v-for="column in visibleColumns" :key="column">
                    {{ getColumnLabel(column) }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, index) in previewData" :key="index">
                  <td v-for="column in visibleColumns" :key="column">
                    {{ formatValue(row[column]) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="pagination" v-if="previewData.length > 0">
          <button 
            class="btn-prev"
            @click="prevPage"
            :disabled="previewPage === 1"
          >
            Previous
          </button>
          <span class="page-info">
            Page {{ previewPage }} of {{ totalPages }}
          </span>
          <button 
            class="btn-next"
            @click="nextPage"
            :disabled="previewPage === totalPages"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <div class="saved-exports">
      <h3>Saved Exports</h3>
      <div class="exports-list">
        <div 
          class="export-item" 
          v-for="savedExport in savedExports" 
          :key="savedExport.id"
        >
          <div class="export-info">
            <div class="export-name">{{ savedExport.name }}</div>
            <div class="export-meta">
              <span class="format">{{ savedExport.format.toUpperCase() }}</span>
              <span class="date-range">
                {{ formatDate(savedExport.dateRange.start) }} - {{ formatDate(savedExport.dateRange.end) }}
              </span>
              <span v-if="savedExport.scheduled" class="scheduled">
                Scheduled: {{ formatSchedule(savedExport.schedule) }}
              </span>
            </div>
            <div class="export-stats">
              <span class="last-run" v-if="savedExport.lastRun">
                Last run: {{ formatDateTime(savedExport.lastRun) }}
              </span>
              <span class="next-run" v-if="savedExport.nextRun">
                Next run: {{ formatDateTime(savedExport.nextRun) }}
              </span>
            </div>
          </div>
          <div class="export-actions">
            <button 
              class="btn-download"
              @click="downloadExport(savedExport.id)"
            >
              Download
            </button>
            <button 
              class="btn-edit"
              @click="editExport(savedExport)"
            >
              Edit
            </button>
            <button 
              class="btn-delete"
              @click="deleteExport(savedExport.id)"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="actions">
      <button 
        class="btn-save"
        @click="saveExport"
        :disabled="!exportConfig.name || !exportConfig.columns.length"
      >
        {{ exportConfig.id ? 'Update Export' : 'Save Export' }}
      </button>
      <button 
        class="btn-run"
        @click="runExport"
        :disabled="!exportConfig.columns.length"
      >
        Run Export Now
      </button>
      <button 
        class="btn-cancel"
        @click="$router.back()"
      >
        Cancel
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import DatePicker from './components/DatePicker.vue';
import Multiselect from './components/Multiselect.vue';
import TextInput from './components/TextInput.vue';
import SelectInput from './components/SelectInput.vue';

const exportConfig = ref({
  id: null,
  name: '',
  format: 'csv',
  dateRange: {
    start: null,
    end: null
  },
  notificationTypes: [],
  columns: [],
  filters: [],
  scheduled: false,
  schedule: {
    frequency: 'weekly',
    dayOfWeek: 'monday',
    dayOfMonth: 1,
    time: '09:00',
    recipients: []
  }
});

const availableColumns = ref([
  { value: 'notification_id', label: 'Notification ID' },
  { value: 'notification_name', label: 'Notification Name' },
  { value: 'notification_type', label: 'Type' },
  { value: 'sent_at', label: 'Sent At' },
  { value: 'recipient_count', label: 'Recipient Count' },
  { value: 'open_rate', label: 'Open Rate' },
  { value: 'click_rate', label: 'Click Rate' },
  { value: 'conversion_rate', label: 'Conversion Rate' },
  { value: 'unsubscribe_count', label: 'Unsubscribes' },
  { value: 'complaint_count', label: 'Complaints' },
  { value: 'segment_name', label: 'Segment' },
  { value: 'campaign_name', label: 'Campaign' }
]);

const notificationTypeOptions = ref([
  { value: 'transactional', label: 'Transactional' },
  { value: 'marketing', label: 'Marketing' },
  { value: 'alert', label: 'Alert' },
  { value: 'reminder', label: 'Reminder' }
]);

const filterableFields = ref([
  { value: 'notification_type', label: 'Notification Type', type: 'string' },
  { value: 'segment_name', label: 'Segment', type: 'string' },
  { value: 'campaign_name', label: 'Campaign', type: 'string' },
  { value: 'open_rate', label: 'Open Rate', type: 'number' },
  { value: 'click_rate', label: 'Click Rate', type: 'number' },
  { value: 'sent_at', label: 'Sent Date', type: 'date' }
]);

const recipientOptions = ref([
  { value: 'user@example.com', label: 'user@example.com' },
  { value: 'admin@example.com', label: 'admin@example.com' },
  { value: 'analytics@example.com', label: 'analytics@example.com' }
]);

const operators = {
  string: [
    { value: 'equals', label: 'equals' },
    { value: 'not_equals', label: 'does not equal' },
    { value: 'contains', label: 'contains' },
    { value: 'not_contains', label: 'does not contain' }
  ],
  number: [
    { value: 'equals', label: 'equals' },
    { value: 'not_equals', label: 'does not equal' },
    { value: 'greater_than', label: 'greater than' },
    { value: 'less_than', label: 'less than' }
  ],
  date: [
    { value: 'equals', label: 'is on' },
    { value: 'not_equals', label: 'is not on' },
    { value: 'before', label: 'is before' },
    { value: 'after', label: 'is after' }
  ]
};

const savedExports = ref([]);
const previewData = ref([]);
const previewPage = ref(1);
const previewLoading = ref(false);
const previewError = ref(null);
const totalPages = ref(1);

const visibleColumns = computed(() => {
  return exportConfig.value.columns.length > 0 
    ? exportConfig.value.columns 
    : availableColumns.value.map(c => c.value);
});

const getOperatorsForField = (field) => {
  const fieldType = filterableFields.value.find(f => f.value === field)?.type || 'string';
  return operators[fieldType] || operators.string;
};

const getInputComponent = (field) => {
  const fieldType = filterableFields.value.find(f => f.value === field)?.type || 'string';
  return fieldType === 'number' ? TextInput : SelectInput;
};

const getOptionsForField = (field) => {
  if (field === 'notification_type') return notificationTypeOptions.value;
  return [];
};

const addFilter = () => {
  exportConfig.value.filters.push({
    field: 'notification_type',
    operator: 'equals',
    value: ''
  });
};

const removeFilter = (index) => {
  exportConfig.value.filters.splice(index, 1);
};

const refreshPreview = async () => {
  previewLoading.value = true;
  previewError.value = null;
  
  try {
    const response = await axios.post('/api/analytics/preview', {
      config: exportConfig.value,
      page: previewPage.value,
      per_page: 10
    });
    
    previewData.value = response.data.items;
    totalPages.value = Math.ceil(response.data.total / 10);
  } catch (error) {
    previewError.value = error.response?.data?.message || error.message;
  } finally {
    previewLoading.value = false;
  }
};

const prevPage = () => {
  if (previewPage.value > 1) {
    previewPage.value--;
    refreshPreview();
  }
};

const nextPage = () => {
  if (previewPage.value < totalPages.value) {
    previewPage.value++;
    refreshPreview();
  }
};

const loadSavedExports = async () => {
  try {
    const response = await axios.get('/api/analytics/exports');
    savedExports.value = response.data;
  } catch (error) {
    console.error('Error loading saved exports:', error);
  }
};

const editExport = (savedExport) => {
  exportConfig.value = JSON.parse(JSON.stringify(savedExport));
  previewPage.value = 1;
  refreshPreview();
};

const saveExport = async () => {
  try {
    const url = exportConfig.value.id 
      ? `/api/analytics/exports/${exportConfig.value.id}`
      : '/api/analytics/exports';
    
    const method = exportConfig.value.id ? 'put' : 'post';
    
    await axios[method](url, exportConfig.value);
    await loadSavedExports();
  } catch (error) {
    console.error('Error saving export:', error);
  }
};

const runExport = async () => {
  try {
    const response = await axios.post('/api/analytics/exports/run', exportConfig.value);
    if (response.data.downloadUrl) {
      window.open(response.data.downloadUrl, '_blank');
    }
  } catch (error) {
    console.error('Error running export:', error);
  }
};

const downloadExport = async (id) => {
  try {
    const response = await axios.get(`/api/analytics/exports/${id}/download`);
    if (response.data.downloadUrl) {
      window.open(response.data.downloadUrl, '_blank');
    }
  } catch (error) {
    console.error('Error downloading export:', error);
  }
};

const deleteExport = async (id) => {
  try {
    await axios.delete(`/api/analytics/exports/${id}`);
    await loadSavedExports();
  } catch (error) {
    console.error('Error deleting export:', error);
  }
};

const formatValue = (value) => {
  if (value === null || value === undefined) return '-';
  if (typeof value === 'number') return value.toLocaleString();
  if (value instanceof Date) return formatDate(value);
  return value;
};

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleDateString();
};

const formatDateTime = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleString();
};

const formatSchedule = (schedule) => {
  if (!schedule) return '';
  
  let str = `${schedule.frequency} at ${schedule.time}`;
  if (schedule.frequency === 'weekly') {
    str += ` on ${schedule.dayOfWeek}`;
  } else if (schedule.frequency === 'monthly') {
    str += ` on day ${schedule.dayOfMonth}`;
  }
  
  return str;
};

const getColumnLabel = (column) => {
  const found = availableColumns.value.find(c => c.value === column);
  return found ? found.label : column;
};

onMounted(() => {
  // Set default date range to last 30 days
  const end = new Date();
  const start = new Date();
  start.setDate(start.getDate() - 30);
  
  exportConfig.value.dateRange = {
    start: start.toISOString().split('T')[0],
    end: end.toISOString().split('T')[0]
  };
  
  // Select all columns by default
  exportConfig.value.columns = availableColumns.value.map(c => c.value);
  
  loadSavedExports();
  refreshPreview();
});
</script>

<style scoped>
.analytics-export {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  margin-bottom: 30px;
}

.header h1 {
  margin-bottom: 5px;
}

.export-container {
  display: flex;
  gap: 20px;
}

.export-config {
  flex: 0 0 500px;
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.export-preview {
  flex: 1;
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section {
  margin-bottom: 25px;
}

.section h3 {
  margin-bottom: 15px;
  padding-bottom: 5px;
  border-bottom: 1px solid #eee;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="time"],
.form-group select {
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
}

.columns-selector {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
  max-height: 200px;
  overflow-y: auto;
  padding: 5px;
}

.column-option {
  display: flex;
  align-items: center;
  gap: 5px;
}

.column-option input {
  margin: 0;
}

.filters {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.filter {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: #f9f9f9;
  border-radius: 4px;
}

.field-select,
.operator-select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
}

.btn-remove-filter {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 13px;
  margin-left: auto;
}

.btn-add-filter {
  background: #f5f5f5;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.scheduling-options {
  margin-top: 15px;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 6px;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.btn-refresh {
  background: #f5f5f5;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.preview-content {
  min-height: 400px;
  border: 1px solid #eee;
  border-radius: 6px;
  padding: 10px;
}

.loading,
.error,
.empty {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 400px;
  color: #666;
}

.error {
  color: #ef4444;
}

.preview-table {
  overflow-x: auto;
}

.preview-table table {
  width: 100%;
  border-collapse: collapse;
}

.preview-table th,
.preview-table td {
  padding: 8px 12px;
  border: 1px solid #eee;
  text-align: left;
}

.preview-table th {
  background: #f5f5f5;
  font-weight: 500;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  margin-top: 15px;
}

.btn-prev,
.btn-next {
  background: #f5f5f5;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.page-info {
  font-size: 13px;
  color: #666;
}

.saved-exports {
  margin-top: 30px;
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.exports-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 15px;
}

.export-item {
  display: flex;
  justify-content: space-between;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 6px;
}

.export-info {
  flex: 1;
}

.export-name {
  font-weight: 500;
  margin-bottom: 5px;
}

.export-meta {
  display: flex;
  gap: 10px;
  font-size: 13px;
  color: #666;
  margin-bottom: 5px;
}

.export-meta .format {
  font-weight: 500;
}

.export-stats {
  font-size: 13px;
  color: #666;
}

.export-actions {
  display: flex;
  gap: 10px;
}

.btn-download {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.btn-edit {
  background: #f59e0b;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.btn-delete {
  background: #ef4444;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 30px;
}

.btn-save {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-run {
  background: #10b981;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-cancel {
  background: #f5f5f5;
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}
</style>