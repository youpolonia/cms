<template>
  <div class="completion-rates-chart bg-white rounded-lg shadow p-6">
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

    <div v-else class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-medium text-gray-900">Workflow Completion Rates</h3>
      <div class="flex space-x-2">
        <button 
          v-for="period in timePeriods" 
          :key="period.value"
          @click="selectedPeriod = period.value"
          class="px-3 py-1 text-sm rounded-md"
          :class="selectedPeriod === period.value 
            ? 'bg-indigo-100 text-indigo-700' 
            : 'text-gray-500 hover:bg-gray-100'"
        >
          {{ period.label }}
        </button>
      </div>
    </div>

    <div class="chart-container h-80">
      <canvas ref="chartCanvas"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  data: {
    type: Array,
    default: () => []
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

const chartCanvas = ref(null)
const chartInstance = ref(null)
const selectedPeriod = ref('month')

const timePeriods = [
  { label: 'Week', value: 'week' },
  { label: 'Month', value: 'month' },
  { label: 'Quarter', value: 'quarter' }
]

onMounted(() => {
  renderChart()
})

watch(() => props.data, () => {
  renderChart()
})

watch(selectedPeriod, () => {
  renderChart()
})

function renderChart() {
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }

  if (!props.data || !props.data.length) return

  const labels = props.data.map(item => item.stage)
  const chartData = {
    labels,
    datasets: [
      {
        label: 'Completed',
        data: props.data.map(item => item.completed),
        backgroundColor: 'rgba(79, 70, 229, 0.8)',
        borderColor: 'rgba(79, 70, 229, 1)',
        borderWidth: 1
      },
      {
        label: 'Abandoned',
        data: props.data.map(item => item.abandoned),
        backgroundColor: 'rgba(239, 68, 68, 0.8)',
        borderColor: 'rgba(239, 68, 68, 1)',
        borderWidth: 1
      }
    ]
  }

  chartInstance.value = new Chart(chartCanvas.value, {
    type: 'bar',
    data: chartData,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: ${context.raw}%`
            }
          }
        }
      },
      scales: {
        x: {
          stacked: true,
        },
        y: {
          stacked: true,
          beginAtZero: true,
          max: 100,
          title: {
            display: true,
            text: 'Percentage'
          }
        }
      }
    }
  })
}
</script>

<style scoped>
.chart-container {
  position: relative;
}
</style>
