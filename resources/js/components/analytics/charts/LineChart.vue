<template>
  <div class="line-chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

export default {
  props: {
    data: {
      type: Array,
      required: true
    }
  },
  setup(props) {
    const chartCanvas = ref(null);
    let chartInstance = null;

    const renderChart = () => {
      if (chartInstance) {
        chartInstance.destroy();
      }

      if (chartCanvas.value && props.data.length) {
        const ctx = chartCanvas.value.getContext('2d');
        chartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: props.data.map(item => item.date),
            datasets: [{
              label: 'Views',
              data: props.data.map(item => item.value),
              borderColor: 'rgb(75, 192, 192)',
              tension: 0.1,
              fill: true,
              backgroundColor: 'rgba(75, 192, 192, 0.1)'
            }]
          },
          options: {
            responsive: true,
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
        });
      }
    };

    onMounted(renderChart);
    watch(() => props.data, renderChart);

    return {
      chartCanvas
    };
  }
}
</script>

<style scoped>
.line-chart-container {
  @apply h-64;
}
</style>