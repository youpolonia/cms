<template>
  <div class="content-analytics-dashboard">
    <ErrorBoundary>
      <div class="dashboard-header">
      <h1>Content Analytics</h1>
      <div class="time-range-selector">
        <select v-model="timeRange">
          <option value="7d">Last 7 days</option>
          <option value="30d">Last 30 days</option>
          <option value="90d">Last 90 days</option>
          <option value="custom">Custom range</option>
        </select>
        <date-picker 
          v-if="timeRange === 'custom'"
          v-model="customRange"
          type="daterange"
          range-separator="to"
        />
      </div>
    </div>

    <ErrorBoundary>
      <div v-if="error" class="error-message">
        Error loading analytics: {{ error.message }}
      </div>

      <div class="metrics-grid" v-if="!isLoading">
      <div class="metric-card">
        <div class="metric-value">{{ stats.totalViews }}</div>
        <div class="metric-label">Total Views</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.uniqueVisitors }}</div>
        <div class="metric-label">Unique Visitors</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.avgTimeOnPage }}s</div>
        <div class="metric-label">Avg. Time</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.avgScrollDepth }}%</div>
        <div class="metric-label">Avg. Scroll Depth</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.bounceRate }}%</div>
        <div class="metric-label">Bounce Rate</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.conversionRate }}%</div>
        <div class="metric-label">Conversion Rate</div>
      </div>
    </div>

    </ErrorBoundary>

    <ErrorBoundary>
      <div class="chart-container" v-if="!isLoading">
        <div v-if="isLoading" class="loading-overlay">
          Loading chart data...
        </div>
      <div class="chart-header">
        <h3>Content Engagement</h3>
        <div class="chart-filters">
          <select v-model="chartType">
              <option value="views">Views</option>
              <option value="unique_visitors">Unique Visitors</option>
              <option value="time">Time on Page</option>
              <option value="scroll">Scroll Depth</option>
              <option value="engagement">Engagement Score</option>
            </select>
          <select v-model="groupBy">
            <option value="day">By Day</option>
            <option value="week">By Week</option>
            <option value="month">By Month</option>
          </select>
        </div>
      </div>
      <line-chart 
        :data="chartData"
        :options="chartOptions"
      />
    </div>

    </ErrorBoundary>

    <ErrorBoundary>
      <div class="contents-table" v-if="!isLoading">
        <div v-if="isLoading" class="loading-overlay">
          Loading content stats...
        </div>
      <h3>Top Performing Content</h3>
      <table>
        <thead>
          <tr>
            <th>Content</th>
            <th>Views</th>
            <th>Unique</th>
            <th>Avg. Time</th>
            <th>Scroll Depth</th>
            <th>Bounce Rate</th>
            <th>Conversions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="content in contentStats" :key="content.id">
            <td>{{ content.title }}</td>
            <td>{{ content.views }}</td>
            <td>{{ content.uniqueVisitors }}</td>
            <td>{{ content.avgTime }}s</td>
            <td>{{ content.avgScrollDepth }}%</td>
            <td>{{ content.bounceRate }}%</td>
            <td>{{ content.conversions }}</td>
          </tr>
        </tbody>
      </table>
    </div>
      </div>
    </ErrorBoundary>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import ErrorBoundary from '@/Components/ErrorBoundary.vue';
import axios from 'axios';
import LineChart from '@/Components/Charts/LineChart.vue';
import DatePicker from '@/Components/DatePicker.vue';

const timeRange = ref('7d');
const isLoading = ref(false);
const error = ref(null);
const customRange = ref([new Date(), new Date()]);
const chartType = ref('views');
const groupBy = ref('day');

const stats = ref({
  totalViews: 0,
  uniqueVisitors: 0,
  avgTimeOnPage: 0,
  avgScrollDepth: 0,
  bounceRate: 0,
  conversionRate: 0
});

const contentStats = ref([]);
const chartData = ref({});
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false
});

const loadAnalytics = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    // Clear any existing errors in boundaries
    document.querySelectorAll('error-boundary').forEach(el => {
      el.__vue__.errorHandler(null);
    });
    const params = {
      time_range: timeRange.value,
      chart_type: chartType.value,
      group_by: groupBy.value
    };

    if (timeRange.value === 'custom') {
      params.start_date = customRange.value[0].toISOString().split('T')[0];
      params.end_date = customRange.value[1].toISOString().split('T')[0];
    }

    const response = await axios.get('/api/content-analytics', { params });
    stats.value = response.data.stats;
    contentStats.value = response.data.contents;
    chartData.value = response.data.chart;
  } catch (err) {
    // Propagate error to the nearest boundary
    const boundary = event.target.closest('error-boundary')?.__vue__;
    if (boundary) {
      boundary.errorHandler(err);
    } else {
      error.value = err;
    }
    console.error('Error loading analytics:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(loadAnalytics);

watch([timeRange, customRange, chartType, groupBy], () => {
  loadAnalytics();
});
</script>

<style scoped>
.error-message {
  color: #dc3545;
  padding: 15px;
  background: #f8d7da;
  border-radius: 4px;
  margin-bottom: 20px;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255,255,255,0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.content-analytics-dashboard {
  padding: 20px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.time-range-selector {
  display: flex;
  gap: 10px;
  align-items: center;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.metric-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  text-align: center;
}

.metric-value {
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 5px;
}

.metric-label {
  color: #666;
  font-size: 14px;
}

.chart-container {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chart-filters {
  display: flex;
  gap: 10px;
}

.contents-table {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
  font-weight: 500;
  color: #555;
}
</style>