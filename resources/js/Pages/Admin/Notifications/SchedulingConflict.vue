<template>
  <div class="scheduling-conflict">
    <div class="header">
      <h1>Scheduling Conflict Detection</h1>
      <p>Identify and resolve notification scheduling conflicts</p>
    </div>

    <div class="controls">
      <div class="time-range">
        <div class="form-group">
          <label>Time Range</label>
          <div class="range-selector">
            <DatePicker 
              v-model="timeRange.start" 
              placeholder="Start Date"
              type="datetime"
            />
            <span class="separator">to</span>
            <DatePicker 
              v-model="timeRange.end" 
              placeholder="End Date"
              type="datetime"
            />
          </div>
        </div>

        <button 
          class="btn-detect"
          @click="detectConflicts"
          :disabled="isDetecting"
        >
          <span v-if="isDetecting">Detecting...</span>
          <span v-else>Detect Conflicts</span>
        </button>
      </div>

      <div class="resolution-options">
        <div class="form-group">
          <label>Resolution Strategy</label>
          <select v-model="resolutionStrategy">
            <option value="auto">Auto-resolve (Recommended)</option>
            <option value="manual">Manual Resolution</option>
            <option value="ignore">Ignore Conflicts</option>
          </select>
        </div>

        <button 
          class="btn-resolve"
          @click="resolveConflicts"
          :disabled="conflicts.length === 0 || isResolving"
        >
          <span v-if="isResolving">Resolving...</span>
          <span v-else>Resolve Selected</span>
        </button>
      </div>
    </div>

    <div class="conflict-stats" v-if="stats">
      <div class="stat">
        <div class="value">{{ stats.totalConflicts }}</div>
        <div class="label">Total Conflicts</div>
      </div>
      <div class="stat">
        <div class="value">{{ stats.highPriority }}</div>
        <div class="label">High Priority</div>
      </div>
      <div class="stat">
        <div class="value">{{ stats.mediumPriority }}</div>
        <div class="label">Medium Priority</div>
      </div>
      <div class="stat">
        <div class="value">{{ stats.lowPriority }}</div>
        <div class="label">Low Priority</div>
      </div>
      <div class="stat">
        <div class="value">{{ stats.autoResolvable }}</div>
        <div class="label">Auto-resolvable</div>
      </div>
    </div>

    <div class="conflict-list">
      <div class="list-header">
        <div class="header-select">
          <input 
            type="checkbox" 
            v-model="selectAll"
            @change="toggleSelectAll"
          />
        </div>
        <div class="header-priority">Priority</div>
        <div class="header-notification">Notification</div>
        <div class="header-conflict">Conflict Type</div>
        <div class="header-scheduled">Scheduled Time</div>
        <div class="header-conflicts-with">Conflicts With</div>
        <div class="header-suggestions">Suggested Resolution</div>
      </div>

      <div class="list-body">
        <div 
          class="conflict-item" 
          v-for="conflict in conflicts" 
          :key="conflict.id"
          :class="{
            'high-priority': conflict.priority === 'high',
            'medium-priority': conflict.priority === 'medium',
            'low-priority': conflict.priority === 'low',
            'selected': selectedConflicts.includes(conflict.id)
          }"
        >
          <div class="item-select">
            <input 
              type="checkbox" 
              v-model="selectedConflicts"
              :value="conflict.id"
            />
          </div>
          <div class="item-priority">
            <span class="priority-badge" :class="conflict.priority">
              {{ conflict.priority }}
            </span>
          </div>
          <div class="item-notification">
            <div class="name">{{ conflict.notification.name }}</div>
            <div class="id">ID: {{ conflict.notification.id }}</div>
          </div>
          <div class="item-conflict">
            {{ conflict.type }}
          </div>
          <div class="item-scheduled">
            {{ formatDateTime(conflict.scheduledTime) }}
          </div>
          <div class="item-conflicts-with">
            <div 
              class="conflict-target" 
              v-for="target in conflict.conflictsWith" 
              :key="target.id"
            >
              <div class="name">{{ target.name }}</div>
              <div class="type">{{ target.type }}</div>
            </div>
          </div>
          <div class="item-suggestions">
            <div class="suggestion" v-for="suggestion in conflict.resolutionSuggestions" :key="suggestion">
              {{ suggestion }}
            </div>
          </div>
          <div class="item-actions">
            <button 
              class="btn-view"
              @click="viewNotification(conflict.notification.id)"
            >
              View
            </button>
            <button 
              class="btn-resolve-single"
              @click="resolveSingleConflict(conflict.id)"
            >
              Resolve
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="resolution-modal" v-if="showResolutionModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Conflict Resolution</h3>
          <button class="btn-close" @click="showResolutionModal = false">
            &times;
          </button>
        </div>
        <div class="modal-body">
          <div class="resolution-option" v-for="option in resolutionOptions" :key="option.value">
            <input 
              type="radio" 
              :id="option.value" 
              :value="option.value" 
              v-model="selectedResolution"
            />
            <label :for="option.value">{{ option.label }}</label>
            <div class="option-details">{{ option.details }}</div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="showResolutionModal = false">
            Cancel
          </button>
          <button class="btn-confirm" @click="confirmResolution">
            Confirm Resolution
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import DatePicker from './components/DatePicker.vue';

const timeRange = ref({
  start: null,
  end: null
});

const resolutionStrategy = ref('auto');
const conflicts = ref([]);
const selectedConflicts = ref([]);
const selectAll = ref(false);
const isDetecting = ref(false);
const isResolving = ref(false);
const stats = ref(null);
const showResolutionModal = ref(false);
const selectedResolution = ref(null);
const resolutionOptions = ref([]);
const currentConflictId = ref(null);

const detectConflicts = async () => {
  isDetecting.value = true;
  try {
    const response = await axios.post('/api/notifications/scheduling/conflicts/detect', {
      start: timeRange.value.start,
      end: timeRange.value.end
    });
    
    conflicts.value = response.data.conflicts;
    stats.value = response.data.stats;
  } catch (error) {
    console.error('Error detecting conflicts:', error);
  } finally {
    isDetecting.value = false;
  }
};

const resolveConflicts = async () => {
  if (resolutionStrategy.value === 'manual') {
    showResolutionModal.value = true;
    return;
  }

  isResolving.value = true;
  try {
    const response = await axios.post('/api/notifications/scheduling/conflicts/resolve', {
      conflictIds: selectedConflicts.value,
      strategy: resolutionStrategy.value
    });
    
    conflicts.value = conflicts.value.filter(
      conflict => !selectedConflicts.value.includes(conflict.id)
    );
    selectedConflicts.value = [];
    selectAll.value = false;
    updateStats();
  } catch (error) {
    console.error('Error resolving conflicts:', error);
  } finally {
    isResolving.value = false;
  }
};

const resolveSingleConflict = (conflictId) => {
  currentConflictId.value = conflictId;
  const conflict = conflicts.value.find(c => c.id === conflictId);
  
  resolutionOptions.value = conflict.resolutionOptions.map(option => ({
    value: option.type,
    label: option.label,
    details: option.details
  }));
  
  selectedResolution.value = resolutionOptions.value[0]?.value || null;
  showResolutionModal.value = true;
};

const confirmResolution = async () => {
  if (!selectedResolution.value) return;
  
  isResolving.value = true;
  try {
    await axios.post('/api/notifications/scheduling/conflicts/resolve', {
      conflictIds: [currentConflictId.value],
      strategy: 'manual',
      resolutionType: selectedResolution.value
    });
    
    conflicts.value = conflicts.value.filter(
      conflict => conflict.id !== currentConflictId.value
    );
    selectedConflicts.value = selectedConflicts.value.filter(
      id => id !== currentConflictId.value
    );
    updateStats();
  } catch (error) {
    console.error('Error confirming resolution:', error);
  } finally {
    isResolving.value = false;
    showResolutionModal.value = false;
  }
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedConflicts.value = conflicts.value.map(conflict => conflict.id);
  } else {
    selectedConflicts.value = [];
  }
};

const viewNotification = (id) => {
  // Navigate to notification detail view
  this.$router.push(`/notifications/${id}`);
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleString();
};

const updateStats = () => {
  if (!conflicts.value.length) {
    stats.value = null;
    return;
  }

  stats.value = {
    totalConflicts: conflicts.value.length,
    highPriority: conflicts.value.filter(c => c.priority === 'high').length,
    mediumPriority: conflicts.value.filter(c => c.priority === 'medium').length,
    lowPriority: conflicts.value.filter(c => c.priority === 'low').length,
    autoResolvable: conflicts.value.filter(c => c.autoResolvable).length
  };
};

// Set default time range to next 7 days
const now = new Date();
const nextWeek = new Date();
nextWeek.setDate(nextWeek.getDate() + 7);

timeRange.value = {
  start: now.toISOString(),
  end: nextWeek.toISOString()
};
</script>

<style scoped>
.scheduling-conflict {
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

.controls {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.time-range,
.resolution-options {
  display: flex;
  gap: 15px;
  align-items: flex-end;
}

.form-group {
  min-width: 200px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  font-size: 13px;
}

.range-selector {
  display: flex;
  align-items: center;
  gap: 10px;
}

.separator {
  color: #666;
  font-size: 13px;
}

.btn-detect,
.btn-resolve {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  height: 36px;
}

.btn-detect {
  background: #3b82f6;
  color: white;
}

.btn-resolve {
  background: #10b981;
  color: white;
}

.btn-detect:disabled,
.btn-resolve:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.conflict-stats {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  padding: 15px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat {
  text-align: center;
  padding: 10px 15px;
  border-radius: 6px;
  background: #f8fafc;
}

.stat .value {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 5px;
}

.stat .label {
  font-size: 12px;
  color: #64748b;
}

.conflict-list {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
}

.list-header {
  display: grid;
  grid-template-columns: 50px 100px 200px 200px 180px 200px 1fr;
  gap: 15px;
  padding: 12px 15px;
  background: #f1f5f9;
  font-weight: 500;
  font-size: 13px;
  border-bottom: 1px solid #e2e8f0;
}

.list-body {
  max-height: 600px;
  overflow-y: auto;
}

.conflict-item {
  display: grid;
  grid-template-columns: 50px 100px 200px 200px 180px 200px 1fr;
  gap: 15px;
  padding: 15px;
  border-bottom: 1px solid #f1f5f9;
  align-items: center;
}

.conflict-item:last-child {
  border-bottom: none;
}

.conflict-item.selected {
  background: #f0f9ff;
}

.item-select input[type="checkbox"] {
  width: 16px;
  height: 16px;
}

.priority-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.priority-badge.high {
  background: #fee2e2;
  color: #b91c1c;
}

.priority-badge.medium {
  background: #fef3c7;
  color: #92400e;
}

.priority-badge.low {
  background: #ecfccb;
  color: #365314;
}

.item-notification .name {
  font-weight: 500;
  margin-bottom: 3px;
}

.item-notification .id {
  font-size: 11px;
  color: #64748b;
}

.item-conflict {
  font-size: 13px;
}

.item-scheduled {
  font-size: 13px;
}

.conflict-target {
  margin-bottom: 5px;
}

.conflict-target .name {
  font-weight: 500;
  font-size: 13px;
}

.conflict-target .type {
  font-size: 11px;
  color: #64748b;
}

.suggestion {
  margin-bottom: 5px;
  font-size: 13px;
  padding: 5px;
  background: #f8fafc;
  border-radius: 4px;
}

.item-actions {
  display: flex;
  gap: 8px;
}

.btn-view,
.btn-resolve-single {
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.btn-view {
  background: #3b82f6;
  color: white;
}

.btn-resolve-single {
  background: #10b981;
  color: white;
}

.resolution-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 600px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #64748b;
}

.modal-body {
  padding: 20px;
}

.resolution-option {
  margin-bottom: 15px;
  padding: 15px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
}

.resolution-option input[type="radio"] {
  margin-right: 10px;
}

.option-details {
  margin-top: 8px;
  font-size: 13px;
  color: #64748b;
  padding-left: 24px;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn-cancel,
.btn-confirm {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.btn-cancel {
  background: #f1f5f9;
  color: #334155;
}

.btn-confirm {
  background: #10b981;
  color: white;
}

.high-priority {
  border-left: 4px solid #ef4444;
}

.medium-priority {
  border-left: 4px solid #f59e0b;
}

.low-priority {
  border-left: 4px solid #84cc16;
}
</style>