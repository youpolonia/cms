<template>
  <div class="pie-chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js'
import { ref, onMounted, watch } from 'vue'

Chart.register(...registerables)

export default {
  props: {
    data: {
      type: Array,
      required: true
    }
  },
  setup(props) {
    const chartCanvas = ref(null)
    let chartInstance = null

    const renderChart = () => {
      if (chartInstance) {
        chartInstance.destroy()
      }

      if (chartCanvas.value && props.data.length) {
        const ctx = chartCanvas.value.getContext('2d')
        chartInstance = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: props.data.map(item => item.label),
            datasets: [
              {
                data: props.data.map(item => item.value),
                backgroundColor: [
                  '#3b82f6',
                  '#10b981',
                  '#f59e0b',
                  '#ef4444',
                  '#8b5cf6',
                  '#ec4899'
                ],
                borderWidth: 1
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right'
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || ''
                    const value = context.raw || 0
                    const total = context.dataset.data.reduce((a, b) => a + b, 0)
                    const percentage = Math.round((value / total) * 100)
                    return `${label}: ${value} (${percentage}%)`
                  }
                }
              }
            }
          }
        })
      }
    }

    onMounted(renderChart)
    watch(() => props.data, renderChart, { deep: true })

    return {
      chartCanvas
    }
  }
}
</script>

<style scoped>
.pie-chart-container {
  @apply relative h-64 w-full;
}
</style>