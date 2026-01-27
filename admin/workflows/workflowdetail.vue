<template>
  <div class="workflow-detail">
    <div class="header">
      <h2>Workflow: {{ workflow.name }}</h2>
      <div class="status-badge" :class="workflow.status">
        {{ workflow.status }}
      </div>
    </div>

    <div class="workflow-visualization">
      <div class="steps-container">
        <div 
          v-for="step in workflow.steps" 
          :key="step.id"
          class="step"
          :class="{ 
            'current': step.is_current,
            'completed': step.status === 'completed',
            'pending': step.status === 'pending'
          }"
        >
          <div class="step-header">
            <h3>{{ step.name }}</h3>
            <div class="step-status">{{ step.status }}</div>
          </div>
          <div class="step-content">
            <p>{{ step.description }}</p>
            <div v-if="step.is_current" class="step-actions">
              <button 
                @click="completeStep(step.id)"
                class="btn btn-primary"
              >
                Complete Step
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="workflow-history">
      <h3>History</h3>
      <table class="history-table">
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Action</th>
            <th>User</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="event in workflow.history" :key="event.id">
            <td>{{ formatDateTime(event.timestamp) }}</td>
            <td>{{ event.action }}</td>
            <td>{{ event.user }}</td>
            <td>{{ event.details }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    workflowId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      workflow: {
        id: '',
        name: '',
        status: '',
        steps: [],
        history: []
      },
      loading: true,
      error: null
    }
  },
  async created() {
    await this.fetchWorkflow();
  },
  methods: {
    async fetchWorkflow() {
      try {
        const response = await fetch(`/api/workflows/status?id=${this.workflowId}`);
        this.workflow = await response.json();
      } catch (err) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },
    async completeStep(stepId) {
      try {
        const response = await fetch('/api/workflows/complete-step', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            workflow_id: this.workflowId,
            step_id: stepId
          })
        });
        
        if (response.ok) {
          await this.fetchWorkflow(); // Refresh workflow data
        } else {
          throw new Error('Failed to complete step');
        }
      } catch (err) {
        this.error = err.message;
      }
    },
    formatDateTime(timestamp) {
      return new Date(timestamp).toLocaleString();
    }
  }
}
</script>

<style scoped>
.workflow-detail {
  padding: 20px;
}
.header {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}
.status-badge {
  margin-left: 15px;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: bold;
}
.status-badge.completed {
  background-color: #d4edda;
  color: #155724;
}
.status-badge.in-progress {
  background-color: #fff3cd;
  color: #856404;
}
.status-badge.pending {
  background-color: #f8d7da;
  color: #721c24;
}

.steps-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.step {
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
.step.current {
  border-left: 4px solid #007bff;
  background-color: #f8f9fa;
}
.step.completed {
  opacity: 0.7;
}
.step-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.step-status {
  font-weight: bold;
}
.step-actions {
  margin-top: 15px;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
.history-table th, .history-table td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
}
.history-table th {
  background-color: #f5f5f5;
}
</style>