<template>
  <div class="usage-timeline">
    <h3>Usage Timeline</h3>
    <line-chart
      :data="chartData"
      :options="chartOptions"
    />
  </div>
</template>

<script>
export default {
  props: {
    data: {
      type: Array,
      required: true
    }
  },

  computed: {
    chartData() {
      return {
        labels: this.data.map(item => item.date),
        datasets: [{
          label: 'Template Usage',
          data: this.data.map(item => item.count),
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      };
    },

    chartOptions() {
      return {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      };
    }
  }
};
</script>

<style scoped>
.usage-timeline {
  margin-top: 30px;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>