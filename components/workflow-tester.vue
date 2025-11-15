<template>
  <div class="workflow-tester">
    <!-- Main test interface -->
    <div class="test-interface">
      <slot></slot>
    </div>

    <!-- Fallback visualization -->
    <div class="fallback-visualization" v-if="showFallback">
      <div class="status-indicator" :class="fallbackStatusClass">
        <span class="icon">⚠️</span>
        <span class="label">Fallback Active</span>
      </div>

      <div class="fallback-details" v-if="showDetails">
        <h3>Fallback Details</h3>
        <div class="detail-row">
          <span class="detail-label">Trigger:</span>
          <span class="detail-value">{{ fallbackData.trigger }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Action:</span>
          <span class="detail-value">{{ fallbackData.action }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Original Result:</span>
          <pre class="detail-value">{{ fallbackData.original }}</pre>
        </div>
        <div class="detail-row">
          <span class="detail-label">Fallback Result:</span>
          <pre class="detail-value">{{ fallbackData.fallback }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WorkflowTester',
  props: {
    fallbackData: {
      type: Object,
      default: () => ({
        trigger: '',
        action: '',
        original: '',
        fallback: ''
      })
    },
    showDetails: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    showFallback() {
      return this.fallbackData.trigger !== '';
    },
    fallbackStatusClass() {
      return {
        'warning': this.fallbackData.trigger === 'timeout',
        'error': this.fallbackData.trigger === 'error',
        'info': this.fallbackData.trigger === 'manual'
      };
    }
  }
};
</script>

<style scoped>
.workflow-tester {
  border: 1px solid #ddd;
  padding: 1rem;
  border-radius: 4px;
}

.fallback-visualization {
  margin-top: 1rem;
  padding: 1rem;
  border-radius: 4px;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border-radius: 4px;
  font-weight: bold;
}

.status-indicator.warning {
  background-color: #fff3cd;
  color: #856404;
}

.status-indicator.error {
  background-color: #f8d7da;
  color: #721c24;
}

.status-indicator.info {
  background-color: #d1ecf1;
  color: #0c5460;
}

.fallback-details {
  margin-top: 1rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.detail-row {
  display: flex;
  margin-bottom: 0.5rem;
}

.detail-label {
  font-weight: bold;
  min-width: 120px;
}

.detail-value {
  flex: 1;
  white-space: pre-wrap;
  word-break: break-all;
}
</style>