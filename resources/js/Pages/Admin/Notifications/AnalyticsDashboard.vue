<template>
  <div class="analytics-dashboard">
    <div class="dashboard-header">
      <h1>Notification Analytics</h1>
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

    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-value">{{ stats.totalSent }}</div>
        <div class="metric-label">Total Sent</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.deliveryRate }}%</div>
        <div class="metric-label">Delivery Rate</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.openRate }}%</div>
        <div class="metric-label">Open Rate</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.clickRate }}%</div>
        <div class="metric-label">Click Rate</div>
      </div>
    </div>

    <div class="chart-container">
      <div class="chart-header">
        <h3>Notification Activity</h3>
        <div class="chart-filters">
          <select v-model="chartType">
            <option value="sent">Sent</option>
            <option value="opens">Opens</option>
            <option value="clicks">Clicks</option>
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

    <div class="templates-table">
      <h3>Template Performance</h3>
      <table>
        <thead>
          <tr>
            <th>Template</th>
            <th>Sent</th>
            <th>Opens</th>
            <th>Clicks</th>
            <th>Open Rate</th>
            <th>Click Rate</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="template in templateStats" :key="template.id">
            <td>{{ template.name }}</td>
            <td>{{ template.sent }}</td>
            <td>{{ template.opens }}</td>
            <td>{{ template.clicks }}</td>
            <td>{{ template.openRate }}%</td>
            <td>{{ template.clickRate }}%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import LineChart from '@/Components/Charts/LineChart.vue';
import DatePicker from '@/Components/DatePicker.vue';

const timeRange = ref('7d');
const customRange = ref([new Date(), new Date()]);
const chartType = ref('sent');
const groupBy = ref('day');

const stats = ref({
  totalSent: 0,
  deliveryRate: 0,
  openRate: 0,
  clickRate: 0
});

const templateStats = ref([]);
const chartData = ref({});
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false
});

const loadAnalytics = async () => {
  try {
    const params = {
      time_range: timeRange.value,
      chart_type: chartType.value,
      group_by: groupBy.value
    };

    if (timeRange.value === 'custom') {
      params.start_date = customRange.value[0].toISOString().split('T')[0];
      params.end_date = customRange.value[1].toISOString().split('T')[0];
    }

    const response = await axios.get('/api/notification-analytics', { params });
    stats.value = response.data.stats;
    templateStats.value = response.data.templates;
    chartData.value = response.data.chart;
  } catch (error) {
    console.error('Error loading analytics:', error);
  }
};

onMounted(loadAnalytics);
</script>

<style scoped>
.analytics-dashboard {
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
  grid-template-columns: repeat(4, 1fr);
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

.templates-table {
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