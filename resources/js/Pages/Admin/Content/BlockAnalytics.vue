<template>
  <div class="block-analytics">
    <div class="time-range-selector">
      <select v-model="timeRange">
        <option value="7d">Last 7 days</option>
        <option value="30d">Last 30 days</option>
        <option value="90d">Last 90 days</option>
        <option value="custom">Custom range</option>
      </select>
      <div v-if="timeRange === 'custom'" class="custom-range">
        <date-picker v-model="startDate" placeholder="Start date" />
        <date-picker v-model="endDate" placeholder="End date" />
      </div>
    </div>

    <div class="metrics-grid">
      <div class="metric-card">
        <h5>Most Used Blocks</h5>
        <div class="chart-container">
          <bar-chart :data="usageData" />
        </div>
      </div>
      <div class="metric-card">
        <h5>Engagement by Block</h5>
        <div class="chart-container">
          <line-chart :data="engagementData" />
        </div>
      </div>
      <div class="metric-card">
        <h5>Block Performance</h5>
        <div class="chart-container">
          <radar-chart :data="performanceData" />
        </div>
      </div>
      <div class="metric-card">
        <h5>Block Types Distribution</h5>
        <div class="chart-container">
          <pie-chart :data="distributionData" />
        </div>
      </div>
    </div>

    <div class="block-table">
      <table>
        <thead>
          <tr>
            <th>Block Type</th>
            <th>Usage Count</th>
            <th>Avg. Engagement</th>
            <th>Avg. Time</th>
            <th>Conversion Rate</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="block in blockStats" :key="block.type">
            <td>{{ block.type }}</td>
            <td>{{ block.usageCount }}</td>
            <td>{{ block.avgEngagement }}%</td>
            <td>{{ block.avgTime }}s</td>
            <td>{{ block.conversionRate }}%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import DatePicker from '@/Components/DatePicker.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import RadarChart from '@/Components/Charts/RadarChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';

export default {
  components: {
    DatePicker,
    BarChart,
    LineChart,
    RadarChart,
    PieChart
  },
  props: {
    pageId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      timeRange: '7d',
      startDate: null,
      endDate: null,
      blockStats: [],
      usageData: {},
      engagementData: {},
      performanceData: {},
      distributionData: {},
      loading: false
    }
  },
  watch: {
    timeRange() {
      this.loadData();
    },
    startDate() {
      if (this.timeRange === 'custom') this.loadData();
    },
    endDate() {
      if (this.timeRange === 'custom') this.loadData();
    }
  },
  async mounted() {
    await this.loadData();
  },
  methods: {
    async loadData() {
      this.loading = true;
      
      try {
        const params = {
          timeRange: this.timeRange
        };
        
        if (this.timeRange === 'custom') {
          params.startDate = this.startDate;
          params.endDate = this.endDate;
        }

        const response = await axios.get(`/api/pages/${this.pageId}/block-analytics`, { params });
        
        this.blockStats = response.data.stats;
        this.usageData = this.transformUsageData(response.data.stats);
        this.engagementData = this.transformEngagementData(response.data.stats);
        this.performanceData = this.transformPerformanceData(response.data.stats);
        this.distributionData = this.transformDistributionData(response.data.stats);
      } catch (error) {
        console.error('Failed to load analytics data:', error);
      } finally {
        this.loading = false;
      }
    },
    transformUsageData(stats) {
      return {
        labels: stats.map(s => s.type),
        datasets: [{
          label: 'Usage Count',
          data: stats.map(s => s.usageCount),
          backgroundColor: '#4f46e5'
        }]
      };
    },
    transformEngagementData(stats) {
      return {
        labels: stats.map(s => s.type),
        datasets: [{
          label: 'Engagement %',
          data: stats.map(s => s.avgEngagement),
          borderColor: '#10b981',
          fill: false
        }]
      };
    },
    transformPerformanceData(stats) {
      return {
        labels: ['Engagement', 'Time', 'Conversions'],
        datasets: stats.map(s => ({
          label: s.type,
          data: [s.avgEngagement, s.avgTime, s.conversionRate],
          backgroundColor: this.getRandomColor()
        }))
      };
    },
    transformDistributionData(stats) {
      return {
        labels: stats.map(s => s.type),
        datasets: [{
          data: stats.map(s => s.usageCount),
          backgroundColor: stats.map(() => this.getRandomColor())
        }]
      };
    },
    getRandomColor() {
      return `#${Math.floor(Math.random()*16777215).toString(16)}`;
    }
  }
}
</script>

<style scoped>
.block-analytics {
  margin-top: 30px;
}
.time-range-selector {
  margin-bottom: 20px;
  display: flex;
  gap: 15px;
  align-items: center;
}
.custom-range {
  display: flex;
  gap: 10px;
}
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}
.metric-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.metric-card h5 {
  margin-top: 0;
  margin-bottom: 15px;
}
.chart-container {
  height: 250px;
}
.block-table {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.block-table table {
  width: 100%;
  border-collapse: collapse;
}
.block-table th, .block-table td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}
.block-table th {
  font-weight: 600;
}
</style>