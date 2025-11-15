<template>
  <div class="workflow-list">
    <h2>Workflows</h2>
    <div class="workflow-actions">
      <button @click="createNewWorkflow" class="btn btn-primary">
        Create New Workflow
      </button>
    </div>
    <div v-if="loading" class="loading">Loading workflows...</div>
    <div v-else>
      <table class="workflow-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="workflow in workflows" :key="workflow.id">
            <td>{{ workflow.id }}</td>
            <td>{{ workflow.name }}</td>
            <td>{{ workflow.status }}</td>
            <td>{{ formatDate(workflow.created_at) }}</td>
            <td>
              <button @click="viewWorkflow(workflow.id)" class="btn btn-sm btn-info">
                View
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      workflows: [],
      loading: true,
      error: null
    }
  },
  async created() {
    await this.fetchWorkflows();
  },
  methods: {
    async fetchWorkflows() {
      try {
        const response = await fetch('/api/workflows/status');
        this.workflows = await response.json();
      } catch (err) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },
    createNewWorkflow() {
      // TODO: Implement workflow creation
      console.log('Create new workflow');
    },
    viewWorkflow(id) {
      // TODO: Navigate to workflow detail view
      console.log('View workflow', id);
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString();
    }
  }
}
</script>

<style scoped>
.workflow-list {
  padding: 20px;
}
.workflow-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
.workflow-table th, .workflow-table td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
}
.workflow-table th {
  background-color: #f5f5f5;
}
.loading {
  padding: 20px;
  text-align: center;
}
</style>