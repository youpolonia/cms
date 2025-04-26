<template>
  <div class="analytics-dashboard">
    <div class="dashboard-header">
      <h2>Version Comparison Analytics</h2>
      <div class="time-range-selector">
        <select v-model="timeRange">
          <option value="7d">Last 7 days</option>
          <option value="30d">Last 30 days</option>
          <option value="90d">Last 90 days</option>
          <option value="all">All time</option>
        </select>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Comparisons</h3>
        <div class="stat-value">{{ stats.totalComparisons }}</div>
        <div class="stat-trend">
          <span :class="['trend-arrow', stats.comparisonTrend > 0 ? 'up' : 'down']">
            {{ stats.comparisonTrend > 0 ? '↑' : '↓' }}
          </span>
          {{ Math.abs(stats.comparisonTrend) }}%
        </div>
      </div>

      <div class="stat-card">
        <h3>Average Changes</h3>
        <div class="stat-value">{{ stats.avgChanges }}</div>
        <div class="stat-description">lines per comparison</div>
      </div>

      <div class="stat-card">
        <h3>Rollback Rate</h3>
        <div class="stat-value">{{ stats.rollbackRate }}%</div>
        <div class="stat-trend">
          <span :class="['trend-arrow', stats.rollbackTrend > 0 ? 'up' : 'down']">
            {{ stats.rollbackTrend > 0 ? '↑' : '↓' }}
          </span>
          {{ Math.abs(stats.rollbackTrend) }}%
        </div>
      </div>
    </div>

    <div class="charts-container">
      <div class="chart-card">
        <h3>Comparison Activity</h3>
        <LineChart :data="activityData" />
      </div>

      <div class="chart-card">
        <h3>Change Types</h3>
        <PieChart :data="changeTypeData" />
      </div>
    </div>

    <div class="recent-activity">
      <h3>Recent Comparisons</h3>
      <table>
        <thead>
          <tr>
            <th>Content</th>
            <th>Versions</th>
            <th>Changes</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="activity in recentActivity" :key="activity.id">
            <td>{{ activity.contentTitle }}</td>
            <td>v{{ activity.fromVersion }} → v{{ activity.toVersion }}</td>
            <td>{{ activity.changes }} changes</td>
            <td>{{ formatDate(activity.date) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import LineChart from './charts/LineChart.vue'
import PieChart from './charts/PieChart.vue'

export default {
  components: {
    LineChart,
    PieChart
  },
  data() {
    return {
      timeRange: '30d',
      stats: {
        totalComparisons: 0,
        comparisonTrend: 0,
        avgChanges: 0,
        rollbackRate: 0,
        rollbackTrend: 0
      },
      activityData: [],
      changeTypeData: [],
      recentActivity: []
    }
  },
  watch: {
    timeRange() {
      this.fetchAnalytics()
    }
  },
  mounted() {
    this.fetchAnalytics()
  },
  methods: {
    async fetchAnalytics() {
      try {
        const response = await this.$axios.get('/api/analytics/version-comparisons', {
          params: {
            range: this.timeRange
          }
        })
        this.stats = response.data.stats
        this.activityData = response.data.activity
        this.changeTypeData = response.data.changeTypes
        this.recentActivity = response.data.recentActivity
      } catch (error) {
        console.error('Error fetching analytics:', error)
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    }
  }
}
</script>

<style scoped>
.analytics-dashboard {
  @apply p-6 bg-white rounded-lg shadow;
}

.dashboard-header {
  @apply flex justify-between items-center mb-6;
}

.dashboard-header h2 {
  @apply text-2xl font-semibold;
}

.time-range-selector select {
  @apply border rounded px-3 py-1;
}

.stats-grid {
  @apply grid grid-cols-1 md:grid-cols-3 gap-4 mb-6;
}

.stat-card {
  @apply bg-gray-50 p-4 rounded-lg;
}

.stat-card h3 {
  @apply text-sm font-medium text-gray-500 mb-1;
}

.stat-value {
  @apply text-2xl font-bold mb-1;
}

.stat-trend {
  @apply text-sm;
}

.trend-arrow.up {
  @apply text-green-500;
}

.trend-arrow.down {
  @apply text-red-500;
}

.charts-container {
  @apply grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6;
}

.chart-card {
  @apply bg-gray-50 p-4 rounded-lg;
}

.chart-card h3 {
  @apply text-lg font-medium mb-4;
}

.recent-activity {
  @apply bg-gray-50 p-4 rounded-lg;
}

.recent-activity h3 {
  @apply text-lg font-medium mb-4;
}

table {
  @apply w-full;
}

th {
  @apply text-left py-2 border-b font-medium;
}

td {
  @apply py-2 border-b;
}
</style>