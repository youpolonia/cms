<template>
  <div class="bar-chart-container">
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
          type: 'bar',
          data: {
            labels: props.data.map(item => item.label),
            datasets: [{
              label: 'Views',
              data: props.data.map(item => item.value),
              backgroundColor: 'rgba(54, 162, 235, 0.5)',
              borderColor: 'rgb(54, 162, 235)',
              borderWidth: 1
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
            },
            indexAxis: 'y'
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
.bar-chart-container {
  @apply h-64;
}
</style>