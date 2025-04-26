<template>
  <div class="line-chart-container">
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
          type: 'line',
          data: {
            labels: props.data.map(item => item.label),
            datasets: [
              {
                label: 'Comparisons',
                data: props.data.map(item => item.value),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                mode: 'index',
                intersect: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  precision: 0
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
.line-chart-container {
  @apply relative h-64 w-full;
}
</style>