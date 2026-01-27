<template>
  <div class="workflow-tester">
    <h2>Workflow Test Console</h2>
    
    <div class="test-section">
      <h3>1. Create Workflow</h3>
      <form @submit.prevent="createWorkflow">
        <div class="form-group">
          <label>Workflow Name</label>
          <input v-model="workflowData.name" required>
        </div>
        
        <div class="form-group">
          <label>Content Types</label>
          <input v-model="workflowData.content_types" placeholder="Comma separated list">
        </div>
        
        <div v-for="(step, index) in workflowData.steps" :key="index" class="step">
          <h4>Step {{ index + 1 }}</h4>
          <div class="form-group">
            <label>Step Name</label>
            <input v-model="step.name" required>
          </div>
          <div class="form-group">
            <label>Approvers (User IDs)</label>
            <input v-model="step.approvers" placeholder="Comma separated user IDs">
          </div>
          <div class="form-group">
            <label>Required Approvals</label>
            <input v-model="step.required_approvals" type="number" min="1">
          </div>
          <button type="button" @click="removeStep(index)">Remove Step</button>
        </div>
        
        <button type="button" @click="addStep">Add Step</button>
        <button type="submit">Create Workflow</button>
      </form>
    </div>
    
    <div class="test-section" v-if="workflowId">
      <h3>2. Assign to Content</h3>
      <form @submit.prevent="assignWorkflow">
        <div class="form-group">
          <label>Content ID</label>
          <input v-model="assignmentData.content_id" required>
        </div>
        <button type="submit">Assign Workflow</button>
      </form>
    </div>
    
    <div class="test-section" v-if="assignmentId">
      <h3>3. Test Approval Steps</h3>
      <div v-if="currentStep">
        <p>Current Step: {{ currentStep }}</p>
        <form @submit.prevent="approveStep">
          <div class="form-group">
            <label>Approver User ID</label>
            <input v-model="approvalData.approver_id" required>
          </div>
          <div class="form-group">
            <label>Notes</label>
            <textarea v-model="approvalData.notes"></textarea>
          </div>
          <button type="submit">Approve Step</button>
          <button type="button" @click="rejectStep">Reject Step</button>
        </form>
      </div>
      <div v-else>
        <p>Workflow completed!</p>
      </div>
    </div>
    
    <div class="results" v-if="results">
      <h3>Test Results</h3>
      <pre>{{ JSON.stringify(results, null, 2) }}</pre>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      workflowData: {
        name: '',
        content_types: '',
        steps: [
          {
            name: '',
            approvers: '',
            required_approvals: 1
          }
        ]
      },
      assignmentData: {
        content_id: ''
      },
      approvalData: {
        approver_id: '',
        notes: ''
      },
      workflowId: null,
      assignmentId: null,
      currentStep: null,
      results: null
    }
  },
  methods: {
    addStep() {
      this.workflowData.steps.push({
        name: '',
        approvers: '',
        required_approvals: 1
      })
    },
    removeStep(index) {
      this.workflowData.steps.splice(index, 1)
    },
    async createWorkflow() {
      try {
        const response = await fetch('/api/workflows/test-run.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'create',
            data: {
              ...this.workflowData,
              content_types: this.workflowData.content_types.split(',').map(t => t.trim()),
              steps: this.workflowData.steps.map(step => ({
                ...step,
                approvers: step.approvers.split(',').map(a => a.trim())
              }))
            }
          })
        })
        
        const result = await response.json()
        this.workflowId = result.workflow_id
        this.results = result
      } catch (error) {
        console.error(error)
        this.results = { error: error.message }
      }
    },
    async assignWorkflow() {
      try {
        const response = await fetch('/api/workflows/test-run.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'assign',
            workflow_id: this.workflowId,
            data: this.assignmentData
          })
        })
        
        const result = await response.json()
        this.assignmentId = result.assignment_id
        this.currentStep = result.current_step
        this.results = result
      } catch (error) {
        console.error(error)
        this.results = { error: error.message }
      }
    },
    async approveStep() {
      await this.processStep('approve')
    },
    async rejectStep() {
      await this.processStep('reject')
    },
    async processStep(action) {
      try {
        const response = await fetch('/api/workflows/test-run.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action,
            assignment_id: this.assignmentId,
            approver_id: this.approvalData.approver_id,
            notes: this.approvalData.notes
          })
        })
        
        const result = await response.json()
        this.currentStep = result.next_step || null
        this.results = result
      } catch (error) {
        console.error(error)
        this.results = { error: error.message }
      }
    }
  }
}
</script>

<style scoped>
.workflow-tester {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.test-section {
  margin-bottom: 30px;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}

input, textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

button {
  padding: 8px 15px;
  margin-right: 10px;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background: #0056b3;
}

.results {
  margin-top: 20px;
  padding: 15px;
  background: #f5f5f5;
  border-radius: 5px;
}

pre {
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>