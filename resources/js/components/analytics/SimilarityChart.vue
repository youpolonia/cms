<template>
  <div class="similarity-chart">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'

export default {
  props: {
    similarity: {
      type: Number,
      required: true,
      validator: value => value >= 0 && value <= 100
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
        type: 'doughnut',
        data: {
          labels: ['Similarity', 'Difference'],
          datasets: [{
            data: [props.similarity, 100 - props.similarity],
            backgroundColor: [
              'rgba(54, 162, 235, 0.8)',
              'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '70%',
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return `${context.label}: ${context.raw}%`
                }
              }
            }
          }
        }
      })
    }

    onMounted(initChart)
    watch(() => props.similarity, initChart)

    return {
      chartCanvas
    }
  }
}
</script>

<style scoped>
.similarity-chart {
  position: relative;
  height: 250px;
}
</style>