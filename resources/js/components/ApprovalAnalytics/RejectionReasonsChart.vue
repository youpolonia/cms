<template>
  <div class="chart-container bg-white rounded-lg shadow p-6">
    <div v-if="loading" class="h-80 flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading chart data...</p>
        <p v-if="retryCount" class="text-sm text-gray-500 mt-2">
          Attempt {{ retryCount + 1 }} of {{ maxRetries + 1 }}
        </p>
      </div>
    </div>

    <div v-else-if="error" class="h-80 flex items-center justify-center">
      <div class="text-center">
        <div class="mx-auto w-12 h-12 flex items-center justify-center rounded-full bg-red-100 text-red-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Failed to render chart</h3>
        <p class="mt-2 text-sm text-gray-600">{{ error }}</p>
        <p v-if="retryCount" class="text-sm text-gray-500 mt-2">
          Tried {{ retryCount + 1 }} times before failing
        </p>
        <button
          v-if="onRetry"
          @click="onRetry()"
          class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Retry
        </button>
      </div>
    </div>

    <div v-else class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-medium text-gray-900">Rejection Reasons</h3>
      <div class="flex space-x-2">
        <button 
          @click="setChartType('bar')"
          class="px-3 py-1 text-sm rounded"
          :class="chartType === 'bar' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
        >
          Bar
        </button>
        <button 
          @click="setChartType('horizontalBar')"
          class="px-3 py-1 text-sm rounded"
          :class="chartType === 'horizontalBar' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
        >
          Horizontal
        </button>
      </div>
    </div>
    <div class="relative h-80">
      <canvas ref="chartCanvas"></canvas>
    </div>
    <div class="mt-4 text-sm text-gray-500">
      <p>Breakdown of content rejection reasons</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  timePeriod: {
    type: String,
    default: 'month'
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: [String, Error, null],
    default: null
  },
  retryCount: {
    type: Number,
    default: 0
  },
  maxRetries: {
    type: Number,
    default: 3
  },
  onRetry: {
    type: Function,
    default: null
  }
})

const loading = ref(false)
const error = ref(null)
const chartData = ref([])

const timePeriods = [
  { label: 'Week', value: 'week' },
  { label: 'Month', value: 'month' },
  { label: 'Quarter', value: 'quarter' }
]

const chartCanvas = ref(null)
const chartType = ref('bar')
let chartInstance = null

const severityColors = {
  critical: 'rgba(220, 38, 38, 0.7)',
  high: 'rgba(234, 88, 12, 0.7)',
  medium: 'rgba(202, 138, 4, 0.7)',
  low: 'rgba(101, 163, 13, 0.7)'
}

onMounted(() => {
  renderChart()
})

onMounted(() => {
  fetchData()
})

watch(() => props.timePeriod, () => {
  fetchData()
})

async function fetchData() {
  loading.value = true
  error.value = null
  
  try {
    const response = await axios.get('/api/content-approval-analytics/rejection-reasons', {
      params: {
        period: props.timePeriod
      }
    })
    chartData.value = response.data
    renderChart()
  } catch (err) {
    error.value = err.message || 'Failed to load rejection reasons data'
    console.error('Error fetching rejection reasons:', err)
  } finally {
    loading.value = false
  }
}

const setChartType = (type) => {
  chartType.value = type
}

const renderChart = () => {
  const ctx = chartCanvas.value.getContext('2d')
  
  chartInstance = new Chart(ctx, {
    type: chartType.value,
    data: {
      labels: chartData.value.map(item => item.reason),
      datasets: [{
        label: 'Rejection Count',
        data: chartData.value.map(item => item.count),
        backgroundColor: chartData.value.map(item => {
          if (item.severity === 'critical') return severityColors.critical
          if (item.severity === 'high') return severityColors.high
          if (item.severity === 'medium') return severityColors.medium
          return severityColors.low
        }),
        borderColor: 'rgba(255, 255, 255, 0.8)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: (context) => {
              return `${context.dataset.label}: ${context.raw}`
            }
          }
        },
        legend: {
          display: false
        }
      }
    }
  })
}
</script>

<style scoped>
.chart-container {
  transition: all 0.2s ease;
}
.chart-container:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>
