<template>
  <div class="schedule-notification">
    <div class="header">
      <h1>Schedule Notification</h1>
      <p>Configure when and how often this notification should be sent</p>
    </div>

    <div class="schedule-form">
      <div class="form-section">
        <h3>Delivery Time</h3>
        
        <div class="time-selection">
          <div class="form-group">
            <label>Date</label>
            <input 
              type="date" 
              v-model="schedule.date"
              :min="minDate"
            >
          </div>
          
          <div class="form-group">
            <label>Time</label>
            <input 
              type="time" 
              v-model="schedule.time"
            >
          </div>
          
          <div class="form-group">
            <label>Timezone</label>
            <select v-model="schedule.timezone">
              <option 
                v-for="tz in timezones" 
                :key="tz" 
                :value="tz"
              >
                {{ tz }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3>Recurrence</h3>
        
        <div class="recurrence-options">
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="schedule.recurrence.type" 
                value="none"
              >
              Send once
            </label>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="schedule.recurrence.type" 
                value="daily"
              >
              Daily
            </label>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="schedule.recurrence.type" 
                value="weekly"
              >
              Weekly
            </label>
            
            <div 
              class="weekdays" 
              v-if="schedule.recurrence.type === 'weekly'"
            >
              <label 
                v-for="day in weekdays" 
                :key="day.value"
                class="day-option"
              >
                <input 
                  type="checkbox" 
                  v-model="schedule.recurrence.days" 
                  :value="day.value"
                >
                {{ day.label }}
              </label>
            </div>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="schedule.recurrence.type" 
                value="monthly"
              >
              Monthly
            </label>
            
            <div 
              class="monthly-options" 
              v-if="schedule.recurrence.type === 'monthly'"
            >
              <div class="option">
                <label>
                  <input 
                    type="radio" 
                    v-model="schedule.recurrence.monthlyType" 
                    value="day"
                  >
                  On day {{ schedule.date ? new Date(schedule.date).getDate() : '' }}
                </label>
              </div>
              
              <div class="option">
                <label>
                  <input 
                    type="radio" 
                    v-model="schedule.recurrence.monthlyType" 
                    value="weekday"
                  >
                  On the 
                  <select v-model="schedule.recurrence.weekdayOccurrence">
                    <option value="first">First</option>
                    <option value="second">Second</option>
                    <option value="third">Third</option>
                    <option value="fourth">Fourth</option>
                    <option value="last">Last</option>
                  </select>
                  <select v-model="schedule.recurrence.weekday">
                    <option 
                      v-for="day in weekdays" 
                      :key="day.value" 
                      :value="day.value"
                    >
                      {{ day.label }}
                    </option>
                  </select>
                </label>
              </div>
            </div>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="schedule.recurrence.type" 
                value="custom"
              >
              Custom
            </label>
            
            <div 
              class="custom-cron" 
              v-if="schedule.recurrence.type === 'custom'"
            >
              <input 
                type="text" 
                v-model="schedule.recurrence.cron" 
                placeholder="Enter cron expression"
              >
              <button 
                class="btn-help"
                @click="showCronHelp = true"
              >
                Help
              </button>
            </div>
          </div>
        </div>
        
        <div 
          class="end-options" 
          v-if="schedule.recurrence.type !== 'none'"
        >
          <h4>End Options</h4>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="schedule.recurrence.endType" 
                value="never"
              >
              Never end
            </label>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="schedule.recurrence.endType" 
                value="after"
              >
              After
              <input 
                type="number" 
                v-model="schedule.recurrence.occurrences" 
                min="1"
              >
              occurrences
            </label>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="schedule.recurrence.endType" 
                value="on"
              >
              On
              <input 
                type="date" 
                v-model="schedule.recurrence.endDate"
                :min="schedule.date"
              >
            </label>
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3>Preview</h3>
        
        <div class="preview">
          <div class="next-runs">
            <h4>Next 5 Scheduled Runs</h4>
            <ul>
              <li v-for="(run, index) in nextRuns" :key="index">
                {{ formatDateTime(run) }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="actions">
      <button class="btn-save" @click="saveSchedule">
        Save Schedule
      </button>
      <button class="btn-cancel" @click="$router.back()">
        Cancel
      </button>
    </div>

    <modal v-model="showCronHelp" @close="showCronHelp = false">
      <template #header>
        <h2>Cron Expression Help</h2>
      </template>
      <template #body>
        <div class="cron-help">
          <p>Cron expressions have five time fields separated by spaces:</p>
          <pre>┌───────────── minute (0 - 59)
│ ┌───────────── hour (0 - 23)
│ │ ┌───────────── day of month (1 - 31)
│ │ │ ┌───────────── month (1 - 12)
│ │ │ │ ┌───────────── day of week (0 - 6) (Sunday to Saturday)
│ │ │ │ │
│ │ │ │ │
* * * * *</pre>
          <p>Examples:</p>
          <ul>
            <li><code>0 9 * * 1-5</code> - Weekdays at 9:00 AM</li>
            <li><code>0 0 1 * *</code> - 1st of every month at midnight</li>
            <li><code>0 12 */2 * *</code> - Every 2 days at noon</li>
          </ul>
        </div>
      </template>
      <template #footer>
        <button class="btn-close" @click="showCronHelp = false">
          Close
        </button>
      </template>
    </modal>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  notificationId: {
    type: [String, Number],
    required: true
  }
});

const schedule = ref({
  date: '',
  time: '09:00',
  timezone: 'UTC',
  recurrence: {
    type: 'none',
    days: [],
    monthlyType: 'day',
    weekdayOccurrence: 'first',
    weekday: '1',
    cron: '',
    endType: 'never',
    occurrences: 10,
    endDate: ''
  }
});

const timezones = ref([
  'UTC',
  'America/New_York',
  'America/Chicago',
  'America/Denver',
  'America/Los_Angeles',
  'Europe/London',
  'Europe/Paris',
  'Asia/Tokyo',
  'Australia/Sydney'
]);

const weekdays = ref([
  { value: '0', label: 'Sunday' },
  { value: '1', label: 'Monday' },
  { value: '2', label: 'Tuesday' },
  { value: '3', label: 'Wednesday' },
  { value: '4', label: 'Thursday' },
  { value: '5', label: 'Friday' },
  { value: '6', label: 'Saturday' }
]);

const showCronHelp = ref(false);
const nextRuns = ref([]);

const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    timeZoneName: 'short'
  });
};

const calculateNextRuns = async () => {
  try {
    const response = await axios.post(
      `/api/notifications/${props.notificationId}/schedule/preview`,
      schedule.value
    );
    nextRuns.value = response.data;
  } catch (error) {
    console.error('Error calculating next runs:', error);
  }
};

const saveSchedule = async () => {
  try {
    await axios.post(
      `/api/notifications/${props.notificationId}/schedule`,
      schedule.value
    );
    // Redirect or show success message
  } catch (error) {
    console.error('Error saving schedule:', error);
  }
};

// Watch for changes to recalculate next runs
watch(
  () => schedule.value,
  () => {
    calculateNextRuns();
  },
  { deep: true, immediate: true }
);

// Set initial date to today if empty
if (!schedule.value.date) {
  schedule.value.date = minDate.value;
}
</script>

<style scoped>
.schedule-notification {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.header {
  margin-bottom: 30px;
}

.header h1 {
  margin-bottom: 5px;
}

.schedule-form {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-section {
  margin-bottom: 30px;
}

.form-section h3 {
  margin-bottom: 15px;
  font-size: 18px;
}

.time-selection {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.form-group label {
  font-size: 14px;
  color: #555;
}

.form-group input,
.form-group select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.recurrence-options {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.option {
  display: flex;
  align-items: center;
  gap: 10px;
}

.option label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.weekdays {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 10px;
  margin-left: 25px;
}

.day-option {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  background: #f5f5f5;
  border-radius: 4px;
}

.monthly-options {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;
  margin-left: 25px;
}

.custom-cron {
  display: flex;
  gap: 10px;
  margin-top: 10px;
  margin-left: 25px;
}

.custom-cron input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.btn-help {
  background: #f5f5f5;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
}

.end-options {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.end-options h4 {
  margin-bottom: 15px;
}

.end-options .option {
  margin-bottom: 10px;
}

.end-options input[type="number"] {
  width: 60px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.preview {
  background: #f9f9f9;
  padding: 15px;
  border-radius: 6px;
}

.next-runs ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.next-runs li {
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.next-runs li:last-child {
  border-bottom: none;
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

.btn-cancel {
  background: #f5f5f5;
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.cron-help pre {
  background: #f5f5f5;
  padding: 10px;
  border-radius: 4px;
  font-family: monospace;
  overflow-x: auto;
}

.cron-help code {
  background: #f5f5f5;
  padding: 2px 4px;
  border-radius: 2px;
  font-family: monospace;
}

.btn-close {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}
</style>