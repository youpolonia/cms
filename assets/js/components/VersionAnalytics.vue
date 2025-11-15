<template>
  <div class="version-analytics">
    <h2>Version Analytics</h2>
    
    <div class="metrics-grid">
      <div class="metric-card">
        <h3>Total Views</h3>
        <div class="metric-value">{{ totalViews }}</div>
      </div>
      
      <div class="metric-card">
        <h3>Engagement Rate</h3>
        <div class="metric-value">{{ engagementRate }}%</div>
      </div>
    </div>

    <div class="chart-container">
      <canvas ref="viewsChart"></canvas>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js';

export default {
  data() {
    return {
      totalViews: 0,
      engagementRate: 0,
      chart: null
    }
  },
  
  mounted() {
    this.initWebSocket();
    this.initChart();
  },
  
  methods: {
    initWebSocket() {
      const socket = new WebSocket('ws://localhost:8080/analytics');
      
      socket.onmessage = (event) => {
        const data = JSON.parse(event.data);
        this.updateMetrics(data);
      };
    },
    
    updateMetrics(data) {
      this.totalViews = data.total_views;
      this.engagementRate = data.engagement_rate;
      
      if (this.chart) {
        this.chart.data.datasets[0].data = data.views_over_time;
        this.chart.update();
      }
    },
    
    initChart() {
      const ctx = this.$refs.viewsChart.getContext('2d');
      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Views',
            data: [],
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
    }
  }
}
</script>

<style scoped>
.version-analytics {
  padding: 20px;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.metric-card {
  background: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
}

.metric-value {
  font-size: 24px;
  font-weight: bold;
}

.chart-container {
  height: 400px;
}
</style>