<template>
  <div class="personalization-chart">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js';

export default {
  name: 'PersonalizationChart',
  props: {
    chartData: {
      type: Object,
      required: true
    },
    options: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      chartInstance: null
    };
  },
  mounted() {
    this.renderChart();
  },
  methods: {
    renderChart() {
      if (this.chartInstance) {
        this.chartInstance.destroy();
      }
      
      this.chartInstance = new Chart(
        this.$refs.chartCanvas,
        {
          type: 'bar',
          data: this.chartData,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true
              }
            },
            ...this.options
          }
        }
      );
    }
  },
  watch: {
    chartData: {
      handler() {
        this.renderChart();
      },
      deep: true
    }
  },
  beforeDestroy() {
    if (this.chartInstance) {
      this.chartInstance.destroy();
    }
  }
};
</script>

<style scoped>
.personalization-chart {
  position: relative;
  width: 100%;
  min-height: 300px;
}
</style>