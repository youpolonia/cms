<template>
  <div class="system-stats">
    <div class="stats-header">
      <h2 class="stats-title">System Performance Metrics</h2>
      <div class="time-range">
        <select v-model="timeRange" class="time-range-select">
          <option value="5m">Last 5 minutes</option>
          <option value="15m">Last 15 minutes</option>
          <option value="1h">Last hour</option>
          <option value="6h">Last 6 hours</option>
          <option value="24h">Last 24 hours</option>
        </select>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card cpu-usage">
        <h3 class="stat-title">CPU Usage</h3>
        <div class="stat-value">{{ cpuUsage }}%</div>
        <div class="stat-chart">
          <canvas ref="cpuChart"></canvas>
        </div>
      </div>

      <div class="stat-card memory-usage">
        <h3 class="stat-title">Memory Usage</h3>
        <div class="stat-value">
          {{ formatBytes(memoryUsage) }} / {{ formatBytes(memoryTotal) }}
        </div>
        <div class="stat-chart">
          <canvas ref="memoryChart"></canvas>
        </div>
      </div>

      <div class="stat-card query-stats">
        <h3 class="stat-title">Query Statistics</h3>
        <div class="stat-metrics">
          <div class="metric">
            <span class="metric-label">Avg Time:</span>
            <span class="metric-value">{{ queryStats.avgTime }}ms</span>
          </div>
          <div class="metric">
            <span class="metric-label">Total:</span>
            <span class="metric-value">{{ queryStats.total }}</span>
          </div>
          <div class="metric">
            <span class="metric-label">Slow:</span>
            <span class="metric-value">{{ queryStats.slow }}</span>
          </div>
        </div>
        <div class="stat-chart">
          <canvas ref="queryChart"></canvas>
        </div>
      </div>

      <div class="stat-card cache-stats">
        <h3 class="stat-title">Cache Statistics</h3>
        <div class="stat-metrics">
          <div class="metric">
            <span class="metric-label">Hit Rate:</span>
            <span class="metric-value">{{ cacheStats.hitRate }}%</span>
          </div>
          <div class="metric">
            <span class="metric-label">Miss Rate:</span>
            <span class="metric-value">{{ cacheStats.missRate }}%</span>
          </div>
          <div class="metric">
            <span class="metric-label">Size:</span>
            <span class="metric-value">{{ formatBytes(cacheStats.size) }}</span>
          </div>
        </div>
        <div class="stat-chart">
          <canvas ref="cacheChart"></canvas>
        </div>
      </div>

      <div class="stat-card response-times">
        <h3 class="stat-title">Response Times</h3>
        <div class="stat-metrics">
          <div class="metric">
            <span class="metric-label">Avg:</span>
            <span class="metric-value">{{ responseTimeData.avg }}ms</span>
          </div>
          <div class="metric">
            <span class="metric-label">P95:</span>
            <span class="metric-value">{{ responseTimeData.p95 }}ms</span>
          </div>
          <div class="metric">
            <span class="metric-label">Max:</span>
            <span class="metric-value">{{ responseTimeData.max }}ms</span>
          </div>
        </div>
        <div class="stat-chart">
          <canvas ref="responseTimeChart"></canvas>
        </div>
      </div>

      <div class="stat-card error-rates">
        <h3 class="stat-title">Error Rates</h3>
        <div class="stat-metrics">
          <div class="metric">
            <span class="metric-label">Total:</span>
            <span class="metric-value">{{ errorRateData.total }}</span>
          </div>
          <div class="metric">
            <span class="metric-label">Rate:</span>
            <span class="metric-value">{{ errorRateData.rate }}%</span>
          </div>
          <div class="metric">
            <span class="metric-label">5xx:</span>
            <span class="metric-value">{{ errorRateData.serverErrors }}</span>
          </div>
        </div>
        <div class="stat-chart">
          <canvas ref="errorRateChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  props: {
    cpuUsage: {
      type: Number,
      default: 0
    },
    memoryUsage: {
      type: Number,
      default: 0
    },
    memoryTotal: {
      type: Number,
      default: 0
    },
    queryStats: {
      type: Object,
      default: () => ({
        avgTime: 0,
        total: 0,
        slow: 0
      })
    },
    cacheStats: {
      type: Object,
      default: () => ({
        hitRate: 0,
        missRate: 0,
        size: 0
      })
    },
    responseTimeData: {
      type: Object,
      default: () => ({
        avg: 0,
        p95: 0,
        max: 0,
        timeline: []
      })
    },
    errorRateData: {
      type: Object,
      default: () => ({
        total: 0,
        rate: 0,
        serverErrors: 0,
        timeline: []
      })
    }
  },
  data() {
    return {
      timeRange: '15m',
      charts: {
        cpu: null,
        memory: null,
        query: null,
        cache: null,
        responseTime: null,
        errorRate: null
      }
    }
  },
  mounted() {
    this.initCharts()
  },
  beforeUnmount() {
    this.destroyCharts()
  },
  methods: {
    formatBytes(bytes) {
      if (bytes === 0) return '0 Bytes'
      
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i]
    },
    initCharts() {
      this.charts.cpu = this.createChart(
        this.$refs.cpuChart,
        'CPU Usage',
        this.generateTimelineData(100),
        'rgba(54, 162, 235, 0.5)'
      )
      
      this.charts.memory = this.createChart(
        this.$refs.memoryChart,
        'Memory Usage',
        this.generateTimelineData(this.memoryTotal),
        'rgba(255, 99, 132, 0.5)'
      )
      
      this.charts.query = this.createChart(
        this.$refs.queryChart,
        'Query Count',
        this.generateTimelineData(100),
        'rgba(75, 192, 192, 0.5)'
      )
      
      this.charts.cache = this.createChart(
        this.$refs.cacheChart,
        'Cache Hit Rate',
        this.generateTimelineData(100),
        'rgba(153, 102, 255, 0.5)'
      )
      
      this.charts.responseTime = this.createChart(
        this.$refs.responseTimeChart,
        'Response Time (ms)',
        this.responseTimeData.timeline.length ? 
          this.responseTimeData.timeline : 
          this.generateTimelineData(1000),
        'rgba(255, 159, 64, 0.5)'
      )
      
      this.charts.errorRate = this.createChart(
        this.$refs.errorRateChart,
        'Error Rate (%)',
        this.errorRateData.timeline.length ? 
          this.errorRateData.timeline : 
          this.generateTimelineData(100),
        'rgba(255, 99, 132, 0.5)'
      )
    },
    destroyCharts() {
      Object.values(this.charts).forEach(chart => {
        if (chart) {
          chart.destroy()
        }
      })
    },
    createChart(canvas, label, data, backgroundColor) {
      return new Chart(canvas, {
        type: 'line',
        data: {
          labels: this.generateTimeLabels(data.length),
          datasets: [{
            label,
            data,
            backgroundColor,
            borderColor: backgroundColor.replace('0.5', '1'),
            borderWidth: 1,
            tension: 0.1,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      })
    },
    generateTimelineData(maxValue) {
      return Array.from({ length: 12 }, () => 
        Math.floor(Math.random() * maxValue)
    },
    generateTimeLabels(count) {
      const now = new Date()
      return Array.from({ length: count }, (_, i) => {
        const d = new Date(now)
        d.setMinutes(d.getMinutes() - (count - i) * 5)
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      })
    }
  },
  watch: {
    timeRange() {
      // TODO: Implement time range filtering
      console.log('Time range changed to:', this.timeRange)
    },
    cpuUsage(newVal) {
      if (this.charts.cpu) {
        this.charts.cpu.data.datasets[0].data.shift()
        this.charts.cpu.data.datasets[0].data.push(newVal)
        this.charts.cpu.update()
      }
    },
    memoryUsage(newVal) {
      if (this.charts.memory) {
        this.charts.memory.data.datasets[0].data.shift()
        this.charts.memory.data.datasets[0].data.push(newVal)
        this.charts.memory.update()
      }
    },
    queryStats: {
      deep: true,
      handler(newVal) {
        if (this.charts.query) {
          this.charts.query.data.datasets[0].data.shift()
          this.charts.query.data.datasets[0].data.push(newVal.total)
          this.charts.query.update()
        }
      }
    },
    cacheStats: {
      deep: true,
      handler(newVal) {
        if (this.charts.cache) {
          this.charts.cache.data.datasets[0].data.shift()
          this.charts.cache.data.datasets[0].data.push(newVal.hitRate)
          this.charts.cache.update()
        }
      }
    },
    responseTimeData: {
      deep: true,
      handler(newVal) {
        if (this.charts.responseTime && newVal.timeline.length) {
          this.charts.responseTime.data.datasets[0].data = newVal.timeline
          this.charts.responseTime.update()
        }
      }
    },
    errorRateData: {
      deep: true,
      handler(newVal) {
        if (this.charts.errorRate && newVal.timeline.length) {
          this.charts.errorRate.data.datasets[0].data = newVal.timeline
          this.charts.errorRate.update()
        }
      }
    }
  }
}
</script>

<style scoped>
.system-stats {
  @apply bg-white rounded-lg shadow p-4;
}

.stats-header {
  @apply flex flex-col md:flex-row justify-between items-start md:items-center mb-6;
}

.stats-title {
  @apply text-xl font-bold mb-2 md:mb-0;
}

.time-range {
  @apply w-full md:w-auto;
}

.time-range-select {
  @apply px-3 py-2 border border-gray-300 rounded text-sm;
}

.stats-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4;
}

.stat-card {
  @apply p-4 border border-gray-200 rounded-lg;
}

.stat-title {
  @apply text-lg font-medium mb-2;
}

.stat-value {
  @apply text-2xl font-bold mb-4;
}

.stat-metrics {
  @apply grid grid-cols-3 gap-2 mb-4;
}

.metric {
  @apply flex flex-col;
}

.metric-label {
  @apply text-xs text-gray-500;
}

.metric-value {
  @apply text-sm font-medium;
}

.stat-chart {
  @apply h-40;
}

.cpu-usage .stat-value {
  @apply text-blue-600;
}

.memory-usage .stat-value {
  @apply text-purple-600;
}

.query-stats .stat-value {
  @apply text-green-600;
}

.cache-stats .stat-value {
  @apply text-yellow-600;
}

.response-times .stat-value {
  @apply text-red-600;
}

.error-rates .stat-value {
  @apply text-red-600;
}
</style>
