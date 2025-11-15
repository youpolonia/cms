<template>
  <div class="workflow-tester">
    <h1>Workflow Fallback Tester</h1>
    
    <div class="control-panel">
      <h2>Fallback Simulation</h2>
      <div class="controls">
        <label>
          <input type="checkbox" v-model="simulateFallback" />
          Simulate Fallback
        </label>
        
        <select v-model="selectedFallbackMode">
          <option value="gemini1">Gemini Pro 1.0</option>
          <option value="gemini2">Gemini Pro 2.5</option>
          <option value="local">Local LLM</option>
          <option value="reduced">Reduced Functionality</option>
        </select>
        
        <button @click="triggerTest">Run Test</button>
      </div>
      
      <div class="status-indicator" :class="statusClass">
        {{ statusMessage }}
      </div>
    </div>
    
    <div class="history-panel">
      <h2>Fallback History</h2>
      <table>
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Mode</th>
            <th>Status</th>
            <th>Duration</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="entry in history" :key="entry.timestamp">
            <td>{{ formatDate(entry.timestamp) }}</td>
            <td>{{ entry.mode }}</td>
            <td>{{ entry.status }}</td>
            <td>{{ entry.duration }}ms</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="context-panel">
      <h2>Context Variables</h2>
      <pre>{{ JSON.stringify(context, null, 2) }}</pre>
    </div>
    
    <div class="metrics-panel">
      <h2>Performance Metrics</h2>
      <div class="metric">
        <h3>Average Response Time</h3>
        <div class="metric-value">{{ avgResponseTime }}ms</div>
      </div>
      <div class="metric">
        <h3>Success Rate</h3>
        <div class="metric-value">{{ successRate }}%</div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  setup() {
    const simulateFallback = ref(false);
    const selectedFallbackMode = ref('gemini1');
    const testStatus = ref('idle');
    const history = ref([]);
    const context = ref({
      tokenUsage: 0,
      quotaStatus: 'normal',
      activeModel: 'primary',
      fallbackTriggered: false
    });

    const statusMessage = computed(() => {
      switch(testStatus.value) {
        case 'idle': return 'Ready for testing';
        case 'running': return 'Test in progress...';
        case 'success': return 'Test completed successfully';
        case 'failed': return 'Test failed - fallback triggered';
        default: return '';
      }
    });

    const statusClass = computed(() => {
      return {
        'status-idle': testStatus.value === 'idle',
        'status-running': testStatus.value === 'running',
        'status-success': testStatus.value === 'success',
        'status-failed': testStatus.value === 'failed'
      };
    });

    const avgResponseTime = computed(() => {
      if (history.value.length === 0) return 0;
      const sum = history.value.reduce((acc, entry) => acc + entry.duration, 0);
      return Math.round(sum / history.value.length);
    });

    const successRate = computed(() => {
      if (history.value.length === 0) return 100;
      const successes = history.value.filter(entry => entry.status === 'success').length;
      return Math.round((successes / history.value.length) * 100);
    });

    const formatDate = (timestamp) => {
      return new Date(timestamp).toLocaleString();
    };

    const triggerTest = async () => {
      const startTime = Date.now();
      testStatus.value = 'running';
      
      try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 500));
        
        if (simulateFallback.value) {
          testStatus.value = 'failed';
          context.value.fallbackTriggered = true;
          context.value.activeModel = selectedFallbackMode.value;
        } else {
          testStatus.value = 'success';
          context.value.fallbackTriggered = false;
          context.value.activeModel = 'primary';
        }
        
        const endTime = Date.now();
        history.value.unshift({
          timestamp: endTime,
          mode: simulateFallback.value ? selectedFallbackMode.value : 'primary',
          status: testStatus.value,
          duration: endTime - startTime
        });
        
      } catch (error) {
        testStatus.value = 'failed';
        const endTime = Date.now();
        history.value.unshift({
          timestamp: endTime,
          mode: 'error',
          status: 'failed',
          duration: endTime - startTime
        });
      }
    };

    return {
      simulateFallback,
      selectedFallbackMode,
      testStatus,
      history,
      context,
      statusMessage,
      statusClass,
      avgResponseTime,
      successRate,
      formatDate,
      triggerTest
    };
  }
};
</script>

<style scoped>
.workflow-tester {
  padding: 20px;
  font-family: Arial, sans-serif;
}

.control-panel, .history-panel, .context-panel, .metrics-panel {
  margin-bottom: 30px;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.controls {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
  align-items: center;
}

.status-indicator {
  padding: 10px;
  border-radius: 5px;
  font-weight: bold;
}

.status-idle {
  background-color: #f0f0f0;
  color: #666;
}

.status-running {
  background-color: #fff3cd;
  color: #856404;
}

.status-success {
  background-color: #d4edda;
  color: #155724;
}

.status-failed {
  background-color: #f8d7da;
  color: #721c24;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 8px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f4f4f4;
}

.metrics-panel {
  display: flex;
  gap: 20px;
}

.metric {
  flex: 1;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 5px;
}

.metric-value {
  font-size: 24px;
  font-weight: bold;
  margin-top: 5px;
}

pre {
  background-color: #f8f9fa;
  padding: 10px;
  border-radius: 5px;
  overflow-x: auto;
}
</style>