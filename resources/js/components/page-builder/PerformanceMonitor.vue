<template>
  <div class="performance-monitor">
    <div class="metric">
      <span class="label">Blocks:</span>
      <span class="value">{{ metrics.blocks }}</span>
    </div>
    <div class="metric">
      <span class="label">Render Time:</span>
      <span class="value">{{ metrics.renderTime }}ms</span>
    </div>
    <div class="metric">
      <span class="label">Memory:</span>
      <span class="value">{{ metrics.memory }}MB</span>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      metrics: {
        blocks: 0,
        renderTime: 0,
        memory: 0
      },
      interval: null
    }
  },
  methods: {
    updateMetrics() {
      this.metrics.blocks = this.$parent.blocks.length
      
      // Measure render time
      const start = performance.now()
      this.$forceUpdate()
      const end = performance.now()
      this.metrics.renderTime = (end - start).toFixed(2)
      
      // Memory usage (approximate)
      if (window.performance?.memory) {
        this.metrics.memory = (window.performance.memory.usedJSHeapSize / (1024 * 1024)).toFixed(2)
      }
    }
  },
  mounted() {
    this.interval = setInterval(this.updateMetrics, 5000)
    this.updateMetrics()
  },
  beforeUnmount() {
    clearInterval(this.interval)
  }
}
</script>

<style scoped>
.performance-monitor {
  position: fixed;
  bottom: 10px;
  left: 10px;
  background: rgba(0,0,0,0.7);
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  display: flex;
  gap: 15px;
}

.metric {
  display: flex;
  gap: 5px;
}

.label {
  opacity: 0.7;
}

.value {
  font-weight: bold;
}
</style>