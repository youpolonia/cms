<template>
  <div class="views-chart">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'

export default {
  props: {
    version1Views: {
      type: Number,
      required: true,
      default: 0
    },
    version2Views: {
      type: Number,
      required: true,
      default: 0
    }
  },
  setup(props) {
    const chartCanvas = ref(null)
    let chartInstance = null

    const initChart = () => {
      if (chartInstance) {
        chartInstance.destroy()
      }

      const ctx = chartCanvas.value.getContext('2d')
      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Version 1', 'Version 2'],
          datasets: [{
            label: 'Views',
            data: [props.version1Views, props.version2Views],
            backgroundColor: [
              'rgba(54, 162, 235, 0.7)',
              'rgba(75, 192, 192, 0.7)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(75, 192, 192, 1)'
            ],
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
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return `Views: ${context.raw}`
                }
              }
            }
          }
        }
      })
    }

    onMounted(initChart)
    watch(() => [props.version1Views, props.version2Views], initChart)

    return {
      chartCanvas
    }
  }
}
</script>

<style scoped>
.views-chart {
  position: relative;
  height: 250px;
}
</style>