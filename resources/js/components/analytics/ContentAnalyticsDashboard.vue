<template>
  <div class="content-analytics-dashboard">
    <div class="dashboard-header">
      <h2>Content Analytics</h2>
      <div class="time-range-selector">
        <select v-model="timeRange">
          <option value="7d">Last 7 days</option>
          <option value="30d">Last 30 days</option>
          <option value="90d">Last 90 days</option>
        </select>
      </div>
    </div>

    <div class="metrics-grid">
      <div class="metric-card">
        <h3>Total Views</h3>
        <div class="metric-value">{{ metrics.totalViews }}</div>
        <div class="metric-change" :class="getChangeClass(metrics.viewChange)">
          {{ metrics.viewChange }}%
        </div>
      </div>

      <div class="metric-card">
        <h3>Unique Visitors</h3>
        <div class="metric-value">{{ metrics.uniqueVisitors }}</div>
        <div class="metric-change" :class="getChangeClass(metrics.visitorChange)">
          {{ metrics.visitorChange }}%
        </div>
      </div>

      <div class="metric-card">
        <h3>Avg. Time on Page</h3>
        <div class="metric-value">{{ metrics.avgTimeOnPage }}s</div>
        <div class="metric-change" :class="getChangeClass(metrics.timeChange)">
          {{ metrics.timeChange }}%
        </div>
      </div>

      <div class="metric-card">
        <h3>Bounce Rate</h3>
        <div class="metric-value">{{ metrics.bounceRate }}%</div>
        <div class="metric-change" :class="getChangeClass(-metrics.bounceChange)">
          {{ metrics.bounceChange }}%
        </div>
      </div>
    </div>

    <div class="charts-container">
      <div class="chart-card">
        <h3>Views Over Time</h3>
        <LineChart :data="viewsData" />
      </div>

      <div class="chart-card">
        <h3>Top Performing Content</h3>
        <BarChart :data="topContentData" />
      </div>
    </div>
  </div>
</template>

<script>
import LineChart from './charts/LineChart.vue';
import BarChart from './charts/BarChart.vue';

export default {
  components: {
    LineChart,
    BarChart
  },
  data() {
    return {
      timeRange: '7d',
      metrics: {
        totalViews: 0,
        uniqueVisitors: 0,
        avgTimeOnPage: 0,
        bounceRate: 0,
        viewChange: 0,
        visitorChange: 0,
        timeChange: 0,
        bounceChange: 0
      },
      viewsData: [],
      topContentData: []
    }
  },
  mounted() {
    this.fetchAnalyticsData();
  },
  watch: {
    timeRange() {
      this.fetchAnalyticsData();
    }
  },
  methods: {
    async fetchAnalyticsData() {
      try {
        const response = await axios.get(`/admin/content-analytics/data`, {
          params: { range: this.timeRange }
        });
        this.metrics = response.data.data.metrics;
        this.viewsData = response.data.data.viewsData;
        this.topContentData = response.data.data.topContentData;
      } catch (error) {
        console.error('Error fetching analytics data:', error);
      }
    },
    getChangeClass(value) {
      return {
        'positive': value > 0,
        'negative': value < 0,
        'neutral': value === 0
      };
    }
  }
}
</script>

<style scoped>
.content-analytics-dashboard {
  @apply p-6 bg-white rounded-lg shadow;
}

.dashboard-header {
  @apply flex justify-between items-center mb-6;
}

.time-range-selector select {
  @apply border rounded px-3 py-1;
}

.metrics-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6;
}

.metric-card {
  @apply border rounded p-4;
}

.metric-value {
  @apply text-2xl font-bold my-2;
}

.metric-change {
  @apply text-sm;
}

.metric-change.positive {
  @apply text-green-600;
}

.metric-change.negative {
  @apply text-red-600;
}

.metric-change.neutral {
  @apply text-gray-600;
}

.charts-container {
  @apply grid grid-cols-1 lg:grid-cols-2 gap-6;
}

.chart-card {
  @apply border rounded p-4;
}

.chart-card h3 {
  @apply mb-4;
}
</style>