<template>
  <ErrorBoundary>
    <div class="content-analytics-dashboard">
    <div class="dashboard-header">
      <h2>Version Comparison Analytics</h2>
      <div class="dashboard-controls">
        <div class="date-range-picker">
          <label>Date Range:</label>
          <select v-model="selectedDateRange" @change="fetchData">
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
            <option value="90d">Last 90 Days</option>
            <option value="custom">Custom</option>
          </select>
          <div v-if="selectedDateRange === 'custom'" class="custom-dates">
            <input type="date" v-model="startDate" @change="fetchData">
            <input type="date" v-model="endDate" @change="fetchData">
          </div>
        </div>
        <button @click="exportData" class="btn btn-primary">
          Export Data
        </button>
      </div>
    </div>

    <div class="dashboard-grid" :class="{'dark': darkMode}" ref="dashboard" tabindex="0" @keydown="handleKeyNavigation">
      <div class="stats-overview">
        <div class="stat-card">
          <h4>Total Comparisons</h4>
          <p class="stat-value">{{ overviewStats.totalComparisons }}</p>
        </div>
        <div class="stat-card">
          <h4>Avg. Similarity</h4>
          <p class="stat-value">{{ overviewStats.avgSimilarity }}%</p>
        </div>
        <div class="stat-card">
          <h4>Avg. Changes</h4>
          <p class="stat-value">{{ overviewStats.avgChanges }}</p>
        </div>
        <div class="stat-card">
          <h4>Active Users</h4>
          <p class="stat-value">{{ overviewStats.activeUsers }}</p>
        </div>
      </div>

      <div class="trends-chart">
        <h3>Comparison Trends</h3>
        <line-chart :data="trendsData" :options="chartOptions"></line-chart>
      </div>

      <div class="comparison-list">
        <h3>Recent Comparisons</h3>
        <div class="list-header">
          <span>Versions</span>
          <span>Date</span>
          <span>Changes</span>
          <span>Similarity</span>
        </div>
        <div 
          v-for="comparison in recentComparisons" 
          :key="comparison.id" 
          class="comparison-item"
          @click="selectComparison(comparison)"
        >
          <span>v{{ comparison.versionA }} ↔ v{{ comparison.versionB }}</span>
          <span>{{ formatDate(comparison.date) }}</span>
          <span>{{ comparison.changes }}</span>
          <span>{{ comparison.similarity }}%</span>
        </div>
      </div>

      <div class="visualization-container">
        <comparison-visualization
          v-if="selectedComparison && !showAnalyticsView"
          :version-a="selectedComparison.versionA"
          :version-b="selectedComparison.versionB"
          :unified-diff="selectedComparison.diff"
          :stats="selectedComparison.stats"
          @show-analytics="showAnalyticsView = true"
        />
        <div v-else-if="!showAnalyticsView" class="empty-state">
          <p>Select a comparison from the list to view details</p>
        </div>
        <div v-else class="analytics-view">
          <div class="flex justify-between items-center mb-4">
            <button @click="showAnalyticsView = false" class="btn btn-sm">
              Back to Comparison
            </button>
            <div class="flex items-center gap-2">
              <button
                @click="fetchAnalyticsData"
                class="btn btn-sm"
                :disabled="isLoadingAnalytics"
              >
                Refresh
              </button>
              <span v-if="analyticsLastUpdated" class="text-xs text-gray-500">
                Last updated: {{ formatTime(analyticsLastUpdated) }} | Size: {{ formatBytes(dataSize) }}
              </span>
            </div>
          </div>
          <h3>Detailed Analytics</h3>
          <div class="analytics-content">
            <div v-if="isDataStale" class="bg-yellow-50 text-yellow-800 p-2 mb-4 rounded text-sm">
              Data may be outdated. Click refresh to update.
            </div>
            <div v-if="isLoadingAnalytics" class="space-y-4">
              <div class="h-8 bg-gray-100 rounded animate-pulse"></div>
              <div class="h-4 bg-gray-100 rounded animate-pulse w-3/4"></div>
              <div class="h-4 bg-gray-100 rounded animate-pulse w-1/2"></div>
              <div class="h-64 bg-gray-100 rounded animate-pulse"></div>
            </div>
            <div v-if="analyticsError" class="error-state">
              {{ analyticsError }}
            </div>
            <template v-else-if="analyticsData">
              <div class="analytics-header">
                <p>Version A: v{{ selectedComparison.versionA.id }}</p>
                <p>Version B: v{{ selectedComparison.versionB.id }}</p>
                <p>Comparison Date: {{ formatDate(selectedComparison.date) }}</p>
              </div>
              
              <div class="analytics-stats">
                <div class="stat-row">
                  <span>Content Changes:</span>
                  <span>{{ analyticsData.contentChanges }}</span>
                </div>
                <div class="stat-row">
                  <span>Structural Changes:</span>
                  <span>{{ analyticsData.structuralChanges }}</span>
                </div>
                <div class="stat-row">
                  <span>Metadata Changes:</span>
                  <span>{{ analyticsData.metadataChanges }}</span>
                </div>
              </div>

              <div class="analytics-chart">
                <h4>Change Distribution</h4>
                <pie-chart
                  :data="analyticsData.changeDistribution"
                  :options="{responsive: true, maintainAspectRatio: false}"
                />
              </div>
            </template>
          </div>
          </div>
      </div>
    </div>
    </div>
  </ErrorBoundary>
</template>

<script>
import ErrorBoundary from './ErrorBoundary.vue'
import LineChart from '@/components/Charts/LineChart.vue'
import PieChart from '@/components/Charts/PieChart.vue'
import ComparisonVisualization from './ComparisonVisualization.vue'
import { format } from 'date-fns'

export default {
  components: {
    LineChart,
    PieChart,
    ComparisonVisualization,
    ErrorBoundary
  },
  data() {
    return {
      showAnalyticsView: false,
      selectedDateRange: '7d',
      startDate: format(new Date(Date.now() - 7 * 24 * 60 * 60 * 1000), 'yyyy-MM-dd'),
      endDate: format(new Date(), 'yyyy-MM-dd'),
      overviewStats: {
        totalComparisons: 0,
        avgSimilarity: 0,
        avgChanges: 0,
        activeUsers: 0
      },
      trendsData: {
        labels: [],
        datasets: [
          {
            label: 'Comparisons',
            data: [],
            backgroundColor: 'rgba(99, 102, 241, 0.2)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 2
          }
        ]
      },
      recentComparisons: [],
      selectedComparison: null,
      analyticsData: null,
      isLoadingAnalytics: false,
      analyticsLastUpdated: null,
      analyticsCacheKey: null,
      autoRefreshInterval: null,
      isDataStale: false,
      dataSize: 0,
      isLoading: false,
      error: null,
      currentPage: 1,
      totalPages: 1,
      comparisonHistory: [],
      darkMode: false,
      userSegments: [],
      selectedSegment: null,
      contentTypes: [],
      selectedContentType: null,
      performanceMetrics: {
        loadTime: 0,
        renderTime: 0,
        apiResponseTime: 0
      },
      chartOptions: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    }
  },
  mounted() {
    this.fetchInitialData()
    this.startAutoRefresh()
    this.trackPerformance()
  },
  beforeUnmount() {
    this.stopAutoRefresh()
  },
  methods: {
    async exportData(format = 'csv') {
      try {
        this.trackEvent('export_initiated', { format })
        const response = await this.$axios.get(`/api/analytics/export?format=${format}`)
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `analytics_export.${format}`)
        document.body.appendChild(link)
        link.click()
        this.trackEvent('export_completed', { format, size: response.data.length })
      } catch (error) {
        this.error = error
        this.trackEvent('export_failed', { format, error: error.message })
      }
    },
    handleKeyNavigation(event) {
      if (event.key === 'ArrowRight' && this.currentPage < this.totalPages) {
        this.currentPage++
        this.fetchData()
      } else if (event.key === 'ArrowLeft' && this.currentPage > 1) {
        this.currentPage--
        this.fetchData()
      }
    },
    async fetchData() {
      try {
        this.isLoading = true
        const response = await this.$axios.get('/api/analytics', {
          params: {
            page: this.currentPage,
            segment: this.selectedSegment,
            contentType: this.selectedContentType
          }
        })
        this.data = response.data.data
        this.totalPages = response.data.meta.last_page
        this.comparisonHistory = response.data.meta.history
        this.isLoading = false
        this.trackPerformance()
      } catch (error) {
        this.error = error
        this.isLoading = false
        this.trackEvent('fetch_error', { error: error.message })
      }
    },
    async fetchInitialData() {
      await Promise.all([
        this.fetchData(),
        this.fetchUserSegments(),
        this.fetchContentTypes()
      ])
    },
    async fetchUserSegments() {
      try {
        const response = await this.$axios.get('/api/analytics/segments')
        this.userSegments = response.data
      } catch (error) {
        console.error('Error fetching user segments:', error)
      }
    },
    async fetchContentTypes() {
      try {
        const response = await this.$axios.get('/api/content/types')
        this.contentTypes = response.data
      } catch (error) {
        console.error('Error fetching content types:', error)
      }
    },
    trackPerformance() {
      const startTime = performance.now()
      
      window.addEventListener('load', () => {
        this.performanceMetrics.loadTime = performance.now() - startTime
        this.performanceMetrics.renderTime = performance.now() - startTime
      })
    },
    async fetchData() {
      try {
        const params = {
          range: this.selectedDateRange,
          start: this.startDate,
          end: this.endDate
        }

        // Fetch overview stats
        const statsResponse = await this.$axios.get('/api/analytics/overview', { params })
        this.overviewStats = statsResponse.data

        // Fetch trends data
        const trendsResponse = await this.$axios.get('/api/analytics/trends', { params })
        this.trendsData.labels = trendsResponse.data.labels
        this.trendsData.datasets[0].data = trendsResponse.data.values

        // Fetch recent comparisons
        const comparisonsResponse = await this.$axios.get('/api/analytics/comparisons', { params })
        this.recentComparisons = comparisonsResponse.data
      } catch (error) {
        console.error('Error fetching analytics data:', error)
      }
    },
    async selectComparison(comparison) {
      this.selectedComparison = comparison
      this.showAnalyticsView = false
      this.analyticsData = null
    },
    toggleAnalyticsView() {
      this.showAnalyticsView = !this.showAnalyticsView
      if (this.showAnalyticsView && this.selectedComparison) {
        this.fetchAnalyticsData()
      }
    },
    formatDate(date) {
      return format(new Date(date), 'MMM d, yyyy')
    },
    formatTime(date) {
      return format(new Date(date), 'h:mm a')
    },
    checkDataStaleness() {
      if (!this.analyticsLastUpdated) return
      const now = new Date()
      const hoursOld = (now - new Date(this.analyticsLastUpdated)) / (1000 * 60 * 60)
      this.isDataStale = hoursOld > 1
    },
    startAutoRefresh() {
      if (this.autoRefreshInterval) {
        clearInterval(this.autoRefreshInterval)
      }
      this.autoRefreshInterval = setInterval(() => {
        this.fetchAnalyticsData()
      }, 300000) // 5 minutes
    },
    stopAutoRefresh() {
      if (this.autoRefreshInterval) {
        clearInterval(this.autoRefreshInterval)
        this.autoRefreshInterval = null
      }
    },
    formatBytes(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },
    async exportData() {
      try {
        const response = await this.$axios.post('/api/analytics/export', {
          range: this.selectedDateRange,
          start: this.startDate,
          end: this.endDate
        }, {
          responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `version-comparisons-${this.selectedDateRange}.csv`)
        document.body.appendChild(link)
        link.click()
      } catch (error) {
        console.error('Error exporting data:', error)
      }
    },
    async fetchAnalyticsData(forceRefresh = false) {
      // Check cache first if not forcing refresh
      if (!forceRefresh && this.analyticsCacheKey) {
        const cached = localStorage.getItem(`analytics-${this.analyticsCacheKey}`)
        if (cached) {
          const { data, timestamp } = JSON.parse(cached)
          this.analyticsData = data
          this.analyticsLastUpdated = new Date(timestamp)
          this.dataSize = new Blob([JSON.stringify(data)]).size
          this.checkDataStaleness()
          return
        }
      }

      this.isLoadingAnalytics = true
      this.analyticsError = null
      try {
        const response = await this.$axios.get(
          `/api/content/${this.selectedComparison.contentId}/versions/${this.selectedComparison.versionA.id}/compare/${this.selectedComparison.versionB.id}/analytics`
        )
        this.analyticsData = response.data
        this.analyticsLastUpdated = new Date()
        this.analyticsCacheKey = `${this.selectedComparison.contentId}-${this.selectedComparison.versionA.id}-${this.selectedComparison.versionB.id}`
        this.dataSize = new Blob([JSON.stringify(response.data)]).size
        
        // Save to cache
        localStorage.setItem(
          `analytics-${this.analyticsCacheKey}`,
          JSON.stringify({
            data: response.data,
            timestamp: new Date().toISOString()
          })
          )
          
          // Clean up old cache entries
          this.cleanupCache()
          this.checkDataStaleness()
      } catch (error) {
        console.error('Failed to fetch analytics:', error)
        this.analyticsError = 'Failed to load analytics data'
      } finally {
        this.isLoadingAnalytics = false
      }
    },
    toggleDarkMode() {
      this.darkMode = !this.darkMode
      document.documentElement.classList.toggle('dark', this.darkMode)
    },
    trackEvent(eventName, payload = {}) {
      if (window.analytics) {
        window.analytics.track(eventName, {
          ...payload,
          component: 'ContentAnalyticsDashboard'
        })
      }
    },
    cleanupCache() {
      const now = new Date()
      const oneWeekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
      
      Object.keys(localStorage).forEach(key => {
        if (key.startsWith('analytics-')) {
          try {
            const { timestamp } = JSON.parse(localStorage.getItem(key))
            if (new Date(timestamp) < oneWeekAgo) {
              localStorage.removeItem(key)
            }
          } catch (e) {
            console.error('Error cleaning cache:', e)
          }
        }
      })
    }
  }
}
</script>

<style scoped>
.content-analytics-dashboard {
  @apply p-6;
}

.dashboard-header {
  @apply flex justify-between items-center mb-6;
}

.dashboard-header h2 {
  @apply text-2xl font-bold;
}

.dashboard-controls {
  @apply flex items-center gap-4;
}

.date-range-picker {
  @apply flex items-center gap-2;
}

.custom-dates {
  @apply flex gap-2;
}

.dashboard-grid {
  @apply grid grid-cols-1 lg:grid-cols-2 gap-6;
}

.stats-overview {
  @apply grid grid-cols-2 md:grid-cols-4 gap-4;
}

.stat-card {
  @apply border rounded-lg p-4 bg-white shadow-sm;
}

.stat-card h4 {
  @apply text-sm font-medium text-gray-500 mb-1;
}

.stat-value {
  @apply text-2xl font-bold;
}

.trends-chart {
  @apply border rounded-lg p-4 bg-white shadow-sm;
}

.trends-chart h3 {
  @apply text-lg font-semibold mb-4;
}

.comparison-list {
  @apply border rounded-lg p-4 bg-white shadow-sm lg:col-span-2;
}

.loading-state {
  @apply p-8 text-center text-gray-500;
}

.analytics-header {
  @apply mb-6 space-y-2;
}

.analytics-stats {
  @apply mb-6 space-y-3;
}

.stat-row {
  @apply flex justify-between border-b pb-2;
}

.analytics-chart {
  @apply mt-8;
}

.comparison-list h3 {
  @apply text-lg font-semibold mb-4;
}

.list-header {
  @apply grid grid-cols-4 gap-4 font-medium border-b pb-2 mb-2;
}

.comparison-item {
  @apply grid grid-cols-4 gap-4 py-2 border-b cursor-pointer hover:bg-gray-50;
}

.visualization-container {
  @apply lg:col-span-2;
}

.empty-state {
  @apply border rounded-lg p-8 text-center text-gray-500;
}

.analytics-view {
  @apply border rounded-lg p-4 bg-white shadow-sm;
}

.analytics-content {
  @apply mt-4 space-y-2;
}
</style>

<style scoped>
.dark .dashboard-grid {
  @apply bg-gray-900 text-gray-100;
}

.dark .stat-card,
.dark .trends-chart,
.dark .comparison-list,
.dark .analytics-view {
  @apply bg-gray-800 border-gray-700;
}

.dark .stat-card h4 {
  @apply text-gray-400;
}

.dark .comparison-item:hover {
  @apply bg-gray-700;
}

@media (max-width: 768px) {
  .dashboard-grid {
    @apply grid-cols-1;
  }
  
  .stats-overview {
    @apply grid-cols-2;
  }
}
</style>
