<template>
  <div class="enhanced-analytics-dashboard">
    <!-- Header with title and date range -->
    <div class="dashboard-header">
      <h1>Enhanced Content Analytics</h1>
      <div class="controls">
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
        <button @click="exportData('csv')" class="export-btn">
          Export CSV
        </button>
        <button @click="exportData('pdf')" class="export-btn">
          Export PDF
        </button>
      </div>
    </div>

    <!-- Metrics grid -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-value">{{ stats.totalViews }}</div>
        <div class="metric-label">Total Views</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.avgTimeOnPage }}s</div>
        <div class="metric-label">Avg. Time</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.bounceRate }}%</div>
        <div class="metric-label">Bounce Rate</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.conversionRate }}%</div>
        <div class="metric-label">Conversion Rate</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.aiSuggestionsUsed }}</div>
        <div class="metric-label">AI Suggestions Used</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.aiImprovedContent }}%</div>
        <div class="metric-label">AI Improved Content</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.aiDailyUsage }}</div>
        <div class="metric-label">AI Tokens Used</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">${{ stats.aiDailyCost.toFixed(2) }}</div>
        <div class="metric-label">AI Daily Cost</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.aiRemainingRequests }}</div>
        <div class="metric-label">Remaining Requests</div>
      </div>
      <div class="metric-card">
        <div class="metric-value">{{ stats.aiRemainingTokens }}</div>
        <div class="metric-label">Remaining Tokens</div>
      </div>
    </div>

    <!-- Content engagement chart -->
    <div class="chart-container">
      <div class="chart-header">
        <h3>Content Engagement</h3>
        <div class="chart-filters">
          <select v-model="chartType">
            <option value="views">Views</option>
            <option value="time">Time on Page</option>
            <option value="scroll">Scroll Depth</option>
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

    <!-- AI Analytics section -->
    <div class="ai-analytics">
      <div class="section-header">
        <h3>AI Content Suggestions</h3>
        <div class="chart-filters">
          <select v-model="aiChartType">
            <option value="usage">Usage</option>
            <option value="types">Suggestion Types</option>
            <option value="impact">Content Impact</option>
          </select>
        </div>
      </div>
      <div class="chart-container">
        <template v-if="aiChartType === 'usage'">
          <bar-chart
            :data="aiUsageChartData"
            :options="aiChartOptions"
          />
        </template>
        <template v-else-if="aiChartType === 'types'">
          <pie-chart
            :data="aiTypesChartData"
            :options="aiChartOptions"
          />
        </template>
        <template v-else>
          <bar-chart
            :data="aiImpactChartData"
            :options="aiChartOptions"
          />
        </template>
      </div>
      <div v-if="aiChartType === 'usage'" class="chart-legend">
        <div class="legend-item">
          <span class="legend-color" style="background: rgba(75, 192, 192, 0.6)"></span>
          <span>Tokens Used</span>
        </div>
        <div class="legend-item">
          <span class="legend-color" style="background: rgba(54, 162, 235, 0.6)"></span>
          <span>Estimated Cost ($)</span>
        </div>
      </div>
    </div>

    <!-- Version comparison section -->
    <div class="version-comparison">
      <div class="section-header">
        <h3>Version Comparison</h3>
        <div class="version-selectors">
          <select v-model="version1">
            <option v-for="version in versions" :value="version.id">
              Version {{ version.number }} ({{ formatDate(version.created_at) }})
            </option>
          </select>
          <span class="vs">vs</span>
          <select v-model="version2">
            <option v-for="version in versions" :value="version.id">
              Version {{ version.number }} ({{ formatDate(version.created_at) }})
            </option>
          </select>
        </div>
      </div>
      <div class="comparison-metrics">
        <div class="metric">
          <div class="value">{{ comparison.similarity }}%</div>
          <div class="label">Similarity</div>
        </div>
        <div class="metric">
          <div class="value">{{ comparison.stats.added }}</div>
          <div class="label">Lines Added</div>
        </div>
        <div class="metric">
          <div class="value">{{ comparison.stats.removed }}</div>
          <div class="label">Lines Removed</div>
        </div>
      </div>
      <div class="comparison-chart">
        <bar-chart 
          :data="comparisonChartData"
          :options="comparisonChartOptions"
        />
      </div>
    </div>

    <!-- Top performing content table -->
    <div class="contents-table">
      <h3>Top Performing Content</h3>
      <table>
        <thead>
          <tr>
            <th>Content</th>
            <th>Views</th>
            <th>Avg. Time</th>
            <th>Bounce Rate</th>
            <th>Conversions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="content in contentStats" :key="content.id">
            <td>{{ content.title }}</td>
            <td>{{ content.views }}</td>
            <td>{{ content.avgTime }}s</td>
            <td>{{ content.bounceRate }}%</td>
            <td>{{ content.conversions }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { Echo } from '@/Services/Echo'
import LineChart from '@/Components/Charts/LineChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'
import PieChart from '@/Components/Charts/PieChart.vue'
import DatePicker from '@/Components/DatePicker.vue'
import { getVersions, getComparison, getFrequentComparisons } from '@/api/versionComparison'

const timeRange = ref('7d')
const customRange = ref([new Date(), new Date()])
const chartType = ref('views')
const groupBy = ref('day')
const version1 = ref(null)
const version2 = ref(null)

const stats = ref({
  totalViews: 0,
  avgTimeOnPage: 0,
  bounceRate: 0,
  conversionRate: 0,
  aiSuggestionsUsed: 0,
  aiImprovedContent: 0,
  aiDailyUsage: 0,
  aiDailyCost: 0,
  aiRemainingRequests: 60,
  aiRemainingTokens: 90000
})

const contentStats = ref([])
const versions = ref([])
const comparison = ref({
  similarity: 0,
  stats: {
    added: 0,
    removed: 0,
    unchanged: 0
  }
})

const chartData = ref({})
const comparisonChartData = ref({})
const aiUsageChartData = ref({})
const aiTypesChartData = ref({})
const aiImpactChartData = ref({})
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false
})
const comparisonChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false
})
const aiChartOptions = ref({
  responsive: true,
  maintainAspectRatio: false
})
const aiChartType = ref('usage')

// Load initial data
const loadData = async () => {
  await Promise.all([
    loadAnalytics(),
    loadVersions(),
    loadAIAnalytics()
  ])
  
  if (versions.value.length >= 2) {
    version1.value = versions.value[0].id
    version2.value = versions.value[1].id
    loadComparison()
  }
}

const loadAnalytics = async () => {
  try {
    const params = {
      time_range: timeRange.value,
      chart_type: chartType.value,
      group_by: groupBy.value,
      ai_chart_type: aiChartType.value
    }

    if (timeRange.value === 'custom') {
      params.start_date = customRange.value[0].toISOString().split('T')[0]
      params.end_date = customRange.value[1].toISOString().split('T')[0]
    }

    const response = await axios.get('/api/content-analytics', { params })
    stats.value = response.data.stats
    contentStats.value = response.data.contents
    chartData.value = response.data.chart
    aiChartData.value = response.data.ai_chart
  } catch (error) {
    console.error('Error loading analytics:', error)
  }
}

const loadAIAnalytics = async () => {
  try {
    const response = await axios.get('/api/ai/analytics')
    stats.value.aiDailyUsage = response.data.daily_usage.tokens
    stats.value.aiDailyCost = response.data.daily_usage.cost
    stats.value.aiRemainingRequests = response.data.rate_limits.remaining_requests
    stats.value.aiRemainingTokens = response.data.rate_limits.remaining_tokens
     
    // Update AI usage chart data
    aiUsageChartData.value = {
      labels: Object.keys(response.data.weekly_usage),
      datasets: [
        {
          label: 'Tokens Used',
          data: Object.values(response.data.weekly_usage).map(u => u.tokens),
          backgroundColor: 'rgba(75, 192, 192, 0.6)'
        },
        {
          label: 'Estimated Cost ($)',
          data: Object.values(response.data.weekly_usage).map(u => u.cost),
          backgroundColor: 'rgba(54, 162, 235, 0.6)'
        }
      ]
    }

    // Update AI types chart data
    aiTypesChartData.value = {
      labels: Object.keys(response.data.suggestion_types),
      datasets: [{
        data: Object.values(response.data.suggestion_types),
        backgroundColor: [
          '#FF6384',
          '#36A2EB',
          '#FFCE56',
          '#4BC0C0',
          '#9966FF',
          '#FF9F40'
        ]
      }]
    }

    // Update AI impact chart data
    aiImpactChartData.value = {
      labels: Object.keys(response.data.content_impact),
      datasets: [{
        label: 'Impact Score',
        data: Object.values(response.data.content_impact),
        backgroundColor: 'rgba(153, 102, 255, 0.6)'
      }]
    }
  } catch (error) {
    console.error('Error loading AI analytics:', error)
  }
}

const loadVersions = async () => {
  try {
    const response = await getVersions()
    versions.value = response.data
  } catch (error) {
    console.error('Error loading versions:', error)
  }
}

const loadComparison = async () => {
  if (!version1.value || !version2.value) return
  
  try {
    const response = await getComparison(version1.value, version2.value)
    comparison.value = response.data
    
    // Prepare comparison chart data
    comparisonChartData.value = {
      labels: ['Added', 'Removed', 'Unchanged'],
      datasets: [{
        label: 'Line Changes',
        data: [
          comparison.value.stats.added,
          comparison.value.stats.removed,
          comparison.value.stats.unchanged
        ],
        backgroundColor: [
          'rgba(75, 192, 192, 0.6)',
          'rgba(255, 99, 132, 0.6)',
          'rgba(54, 162, 235, 0.6)'
        ]
      }]
    }
  } catch (error) {
    console.error('Error loading comparison:', error)
  }
}

const exportData = async (format) => {
  try {
    const params = {
      format,
      time_range: timeRange.value
    }

    if (timeRange.value === 'custom') {
      params.start_date = customRange.value[0].toISOString().split('T')[0]
      params.end_date = customRange.value[1].toISOString().split('T')[0]
    }

    const response = await axios.post('/api/content-analytics/export', params)
    window.open(response.data.download_url, '_blank')
  } catch (error) {
    console.error('Error exporting data:', error)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

// Setup real-time updates
const setupRealtime = () => {
  Echo.channel('content-analytics')
    .listen('ContentAnalyticsUpdated', (data) => {
      if (data.contentId === contentId.value) {
        loadAnalytics()
      }
    })
}

onMounted(() => {
  loadData()
  setupRealtime()
})

watch([timeRange, customRange, chartType, groupBy, aiChartType], () => {
  loadAnalytics()
  loadAIAnalytics()
})

watch([version1, version2], () => {
  loadComparison()
})
</script>

<style scoped>
.enhanced-analytics-dashboard {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.controls {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.time-range-selector {
  display: flex;
  gap: 10px;
  align-items: center;
}

.export-btn {
  background: #4f46e5;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.export-btn:hover {
  background: #4338ca;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
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

.chart-container, .version-comparison, .contents-table {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.chart-header, .section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.chart-filters {
  display: flex;
  gap: 10px;
}

.chart-legend {
  display: flex;
  gap: 20px;
  margin-top: 15px;
  justify-content: center;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.legend-color {
  display: inline-block;
  width: 16px;
  height: 16px;
  border-radius: 3px;
}

.version-selectors {
  display: flex;
  gap: 10px;
  align-items: center;
}

.vs {
  color: #666;
  font-weight: bold;
}

.comparison-metrics {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.comparison-metrics .metric {
  text-align: center;
  padding: 15px;
  background: #f8fafc;
  border-radius: 8px;
}

.comparison-metrics .value {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 5px;
}

.comparison-metrics .label {
  color: #666;
  font-size: 14px;
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

@media (max-width: 768px) {
  .dashboard-header, .chart-header, .section-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .controls, .chart-filters, .version-selectors {
    width: 100%;
    flex-direction: column;
  }
  
  select, .export-btn {
    width: 100%;
  }
}
</style>