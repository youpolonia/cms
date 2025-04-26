<template>
  <div class="workflow-editor">
    <div class="editor-header">
      <h1>{{ workflow.id ? 'Edit' : 'Create' }} Approval Workflow</h1>
    </div>

    <div class="form-section">
      <div class="form-group">
        <label>Workflow Name *</label>
        <input 
          v-model="workflow.name" 
          type="text" 
          required
          placeholder="Enter workflow name"
        >
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea 
          v-model="workflow.description" 
          placeholder="Enter workflow description"
        />
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" v-model="workflow.is_active">
          Active Workflow
        </label>
      </div>
    </div>

    <div class="steps-section">
      <h3>Approval Steps</h3>
      <div 
        class="step-item" 
        v-for="(step, index) in workflow.steps" 
        :key="index"
      >
        <div class="step-header">
          <span class="step-number">Step {{ index + 1 }}</span>
          <button 
            class="remove-step" 
            @click="removeStep(index)"
            v-if="workflow.steps.length > 1"
          >
            Remove
          </button>
        </div>

        <div class="step-form">
          <div class="form-group">
            <label>Step Name *</label>
            <input 
              v-model="step.name" 
              type="text" 
              required
              placeholder="Enter step name"
            >
          </div>

          <div class="form-group">
            <label>Approvers *</label>
            <multi-select
              v-model="step.approvers"
              :options="availableUsers"
              label="name"
              track-by="id"
              :multiple="true"
              placeholder="Select approvers"
            />
          </div>

          <div class="form-group">
            <label>Required Approvals *</label>
            <select v-model="step.required_approvals">
              <option 
                v-for="n in step.approvers.length || 1" 
                :value="n"
                :key="n"
              >
                {{ n }} approval{{ n > 1 ? 's' : '' }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Timeout (hours)</label>
            <input 
              v-model="step.timeout_hours" 
              type="number" 
              min="0"
              placeholder="0 for no timeout"
            >
          </div>
        </div>
      </div>

      <button class="add-step" @click="addStep">
        + Add Step
      </button>
    </div>

    <div class="actions">
      <button class="btn-save" @click="saveWorkflow">
        Save Workflow
      </button>
      <button class="btn-cancel" @click="$router.back()">
        Cancel
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import MultiSelect from '@/Components/MultiSelect.vue';

const props = defineProps({
  workflowId: {
    type: [String, Number],
    default: null
  }
});

const workflow = ref({
  name: '',
  description: '',
  is_active: true,
  steps: [{
    name: '',
    approvers: [],
    required_approvals: 1,
    timeout_hours: 0
  }]
});

const availableUsers = ref([]);

const loadWorkflow = async () => {
  if (!props.workflowId) return;
  
  try {
    const response = await axios.get(`/api/approval-workflows/${props.workflowId}`);
    workflow.value = response.data;
  } catch (error) {
    console.error('Error loading workflow:', error);
  }
};

const loadUsers = async () => {
  try {
    const response = await axios.get('/api/users?role=approver');
    availableUsers.value = response.data;
  } catch (error) {
    console.error('Error loading users:', error);
  }
};

const addStep = () => {
  workflow.value.steps.push({
    name: '',
    approvers: [],
    required_approvals: 1,
    timeout_hours: 0
  });
};

const removeStep = (index) => {
  workflow.value.steps.splice(index, 1);
};

const saveWorkflow = async () => {
  try {
    if (props.workflowId) {
      await axios.put(`/api/approval-workflows/${props.workflowId}`, workflow.value);
    } else {
      await axios.post('/api/approval-workflows', workflow.value);
    }
    // Redirect or show success message
  } catch (error) {
    console.error('Error saving workflow:', error);
  }
};

onMounted(() => {
  loadWorkflow();
  loadUsers();
});
</script>

<style scoped>
.workflow-editor {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.editor-header {
  margin-bottom: 30px;
}

.form-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-group textarea {
  min-height: 100px;
}

.steps-section {
  background: white;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.step-item {
  border: 1px solid #eee;
  border-radius: 6px;
  padding: 15px;
  margin-bottom: 20px;
}

.step-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}

.step-number {
  font-weight: bold;
}

.remove-step {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 14px;
}

.add-step {
  background: none;
  border: 1px dashed #ccc;
  width: 100%;
  padding: 10px;
  border-radius: 4px;
  cursor: pointer;
  color: #3b82f6;
  font-weight: 500;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
}

.btn-save {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-cancel {
  background: #f5f5f5;
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}
</style>