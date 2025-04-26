<template>
  <div class="export-analytics">
    <div class="header">
      <h1>Export Performance Analytics</h1>
      <p>Monitor and optimize notification export operations</p>
    </div>

    <div class="time-range-selector">
      <div class="form-group">
        <label>Time Range</label>
        <div class="range-controls">
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
          <button 
            class="btn-apply"
            @click="loadAnalytics"
          >
            Apply
          </button>
        </div>
      </div>
    </div>

    <div class="dashboard">
      <div class="summary-cards">
        <div class="card">
          <div class="card-header">
            <div class="title">Total Exports</div>
            <div class="icon">
              <i class="fas fa-file-export"></i>
            </div>
          </div>
          <div class="card-body">
            <div class="value">{{ summary.totalExports }}</div>
            <div class="trend" :class="getTrendClass(summary.exportsTrend)">
              <span v-if="summary.exportsTrend > 0">+</span>
              {{ summary.exportsTrend }}%
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="title">Avg. Duration</div>
            <div class="icon">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          <div class="card-body">
            <div class="value">{{ formatDuration(summary.avgDuration) }}</div>
            <div class="trend" :class="getTrendClass(-summary.durationTrend)">
              <span v-if="summary.durationTrend > 0">+</span>
              {{ summary.durationTrend }}%
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="title">Success Rate</div>
            <div class="icon">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          <div class="card-body">
            <div class="value">{{ summary.successRate }}%</div>
            <div class="trend" :class="getTrendClass(summary.successTrend)">
              <span v-if="summary.successTrend > 0">+</span>
              {{ summary.successTrend }}%
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="title">Avg. Size</div>
            <div class="icon">
              <i class="fas fa-database"></i>
            </div>
          </div>
          <div class="card-body">
            <div class="value">{{ formatFileSize(summary.avgSize) }}</div>
            <div class="trend" :class="getTrendClass(-summary.sizeTrend)">
              <span v-if="summary.sizeTrend > 0">+</span>
              {{ summary.sizeTrend }}%
            </div>
          </div>
        </div>
      </div>

      <div class="charts">
        <div class="chart-container">
          <h3>Exports Over Time</h3>
          <LineChart 
            :data="exportsOverTime.data"
            :options="exportsOverTime.options"
          />
        </div>

        <div class="chart-container">
          <h3>Export Duration Distribution</h3>
          <BarChart 
            :data="durationDistribution.data"
            :options="durationDistribution.options"
          />
        </div>

        <div class="chart-container">
          <h3>Export Types</h3>
          <DoughnutChart 
            :data="exportTypes.data"
            :options="exportTypes.options"
          />
        </div>

        <div class="chart-container">
          <h3>Resource Usage</h3>
          <RadarChart 
            :data="resourceUsage.data"
            :options="resourceUsage.options"
          />
        </div>
      </div>

      <div class="detailed-stats">
        <div class="performance-table">
          <h3>Export Performance Details</h3>
          <table>
            <thead>
              <tr>
                <th>Export ID</th>
                <th>Type</th>
                <th>Status</th>
                <th>Duration</th>
                <th>Size</th>
                <th>Records</th>
                <th>Initiated By</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="exportItem in exports" :key="exportItem.id">
                <td>{{ exportItem.id }}</td>
                <td>{{ exportItem.type }}</td>
                <td>
                  <span class="status-badge" :class="exportItem.status">
                    {{ exportItem.status }}
                  </span>
                </td>
                <td>{{ formatDuration(exportItem.duration) }}</td>
                <td>{{ formatFileSize(exportItem.size) }}</td>
                <td>{{ exportItem.recordCount }}</td>
                <td>{{ exportItem.initiatedBy }}</td>
                <td>
                  <button
                    class="btn-view"
                    @click="viewExport(exportItem.id)"
                  >
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="optimization-recommendations">
          <h3>Optimization Recommendations</h3>
          <div class="recommendations-list">
            <div 
              class="recommendation" 
              v-for="(rec, index) in recommendations" 
              :key="index"
            >
              <div class="rec-header">
                <div class="severity" :class="rec.severity">
                  {{ rec.severity }}
                </div>
                <div class="title">{{ rec.title }}</div>
              </div>
              <div class="rec-body">
                {{ rec.description }}
              </div>
              <div class="rec-actions">
                <button 
                  class="btn-apply"
                  @click="applyRecommendation(rec)"
                >
                  Apply
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import DatePicker from './components/DatePicker.vue';
import LineChart from './components/charts/LineChart.vue';
import BarChart from './components/charts/BarChart.vue';
import DoughnutChart from './components/charts/DoughnutChart.vue';
import RadarChart from './components/charts/RadarChart.vue';

const timeRange = ref({
  start: null,
  end: null
});

const summary = ref({
  totalExports: 0,
  exportsTrend: 0,
  avgDuration: 0,
  durationTrend: 0,
  successRate: 0,
  successTrend: 0,
  avgSize: 0,
  sizeTrend: 0
});

const exportsOverTime = ref({
  data: {},
  options: {}
});

const durationDistribution = ref({
  data: {},
  options: {}
});

const exportTypes = ref({
  data: {},
  options: {}
});

const resourceUsage = ref({
  data: {},
  options: {}
});

const exports = ref([]);
const recommendations = ref([]);

const loadAnalytics = async () => {
  try {
    const response = await axios.get('/api/notifications/exports/analytics', {
      params: {
        start: timeRange.value.start,
        end: timeRange.value.end
      }
    });

    summary.value = response.data.summary;
    exportsOverTime.value = response.data.exportsOverTime;
    durationDistribution.value = response.data.durationDistribution;
    exportTypes.value = response.data.exportTypes;
    resourceUsage.value = response.data.resourceUsage;
    exports.value = response.data.exports;
    recommendations.value = response.data.recommendations;
  } catch (error) {
    console.error('Error loading export analytics:', error);
  }
};

const formatDuration = (seconds) => {
  if (seconds < 60) return `${seconds}s`;
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  return `${minutes}m ${remainingSeconds}s`;
};

const formatFileSize = (bytes) => {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  if (bytes < 1024 * 1024 * 1024) return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  return `${(bytes / (1024 * 1024 * 1024)).toFixed(1)} GB`;
};

const getTrendClass = (trend) => {
  return trend > 0 ? 'negative' : 'positive';
};

const viewExport = (id) => {
  // Navigate to export detail view
  this.$router.push(`/exports/${id}`);
};

const applyRecommendation = async (recommendation) => {
  try {
    await axios.post('/api/notifications/exports/optimize', {
      recommendationId: recommendation.id
    });
    loadAnalytics();
  } catch (error) {
    console.error('Error applying recommendation:', error);
  }
};

// Set default time range to last 30 days
const now = new Date();
const lastMonth = new Date();
lastMonth.setDate(lastMonth.getDate() - 30);

timeRange.value = {
  start: lastMonth.toISOString(),
  end: now.toISOString()
};

onMounted(() => {
  loadAnalytics();
});
</script>

<style scoped>
.export-analytics {
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

.time-range-selector {
  margin-bottom: 20px;
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
  font-size: 13px;
}

.range-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.separator {
  color: #666;
  font-size: 13px;
}

.btn-apply {
  padding: 8px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  background: #3b82f6;
  color: white;
}

.dashboard {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
}

.card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.card-header .title {
  font-weight: 500;
}

.card-header .icon {
  color: #3b82f6;
  font-size: 20px;
}

.card-body {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.card-body .value {
  font-size: 24px;
  font-weight: 600;
}

.card-body .trend {
  font-size: 14px;
  font-weight: 500;
}

.trend.positive {
  color: #10b981;
}

.trend.negative {
  color: #ef4444;
}

.charts {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.chart-container {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.chart-container h3 {
  margin-top: 0;
  margin-bottom: 15px;
  font-size: 16px;
}

.detailed-stats {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.performance-table {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.performance-table h3 {
  margin-top: 0;
  margin-bottom: 15px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

th {
  font-weight: 500;
  font-size: 13px;
  color: #64748b;
}

.status-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-badge.success {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.failed {
  background: #fee2e2;
  color: #b91c1c;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.btn-view {
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  background: #3b82f6;
  color: white;
}

.optimization-recommendations {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.recommendations-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.recommendation {
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 15px;
}

.rec-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.severity {
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.severity.high {
  background: #fee2e2;
  color: #b91c1c;
}

.severity.medium {
  background: #fef3c7;
  color: #92400e;
}

.severity.low {
  background: #ecfccb;
  color: #365314;
}

.rec-header .title {
  font-weight: 500;
}

.rec-body {
  font-size: 13px;
  margin-bottom: 10px;
}

.rec-actions {
  display: flex;
  justify-content: flex-end;
}

.btn-apply {
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  background: #10b981;
  color: white;
}
</style>