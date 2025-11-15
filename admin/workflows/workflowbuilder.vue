<template>
  <div class="workflow-builder">
    <div class="builder-header">
      <h2>Workflow Builder</h2>
      <div class="version-control">
        <button @click="saveVersion">Save Version</button>
        <select v-model="currentVersion">
          <option v-for="version in versions" :value="version.id">
            v{{ version.id }} - {{ version.date }}
          </option>
        </select>
      </div>
    </div>

    <div class="builder-grid">
      <TriggerPanel 
        :triggers="availableTriggers"
        @add-trigger="addTrigger"
      />
      
      <ActionPanel
        :actions="availableActions" 
        @add-action="addAction"
      />

      <div class="workflow-canvas">
        <WorkflowNode
          v-for="node in workflowNodes"
          :key="node.id"
          :node="node"
          @remove="removeNode"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import TriggerPanel from './TriggerPanel.vue';
import ActionPanel from './ActionPanel.vue';
import WorkflowNode from './WorkflowNode.vue';

export default {
  components: { TriggerPanel, ActionPanel, WorkflowNode },
  setup() {
    const availableTriggers = ref([
      { id: 'time', label: 'Time Trigger' },
      { id: 'content', label: 'Content Change' },
      { id: 'webhook', label: 'Webhook' }
    ]);

    const availableActions = ref([
      { id: 'notification', label: 'Send Notification' },
      { id: 'content', label: 'Update Content' },
      { id: 'api', label: 'API Call' }
    ]);

    const workflowNodes = ref([]);
    const versions = ref([]);
    const currentVersion = ref(null);

    const fetchVersions = async () => {
      try {
        const response = await axios.get('/api/workflows/versions');
        versions.value = response.data;
      } catch (error) {
        console.error('Error fetching versions:', error);
      }
    };

    const addTrigger = (trigger) => {
      workflowNodes.value.push({
        id: Date.now(),
        type: 'trigger',
        config: { ...trigger }
      });
    };

    const addAction = (action) => {
      workflowNodes.value.push({
        id: Date.now(),
        type: 'action',
        config: { ...action }
      });
    };

    const removeNode = (id) => {
      workflowNodes.value = workflowNodes.value.filter(n => n.id !== id);
    };

    const saveVersion = async () => {
      try {
        await axios.post('/api/workflows/versions', {
          nodes: workflowNodes.value
        });
        fetchVersions();
      } catch (error) {
        console.error('Error saving version:', error);
      }
    };

    onMounted(fetchVersions);

    return {
      availableTriggers,
      availableActions,
      workflowNodes,
      versions,
      currentVersion,
      addTrigger,
      addAction,
      removeNode,
      saveVersion
    };
  }
};
</script>

<style scoped>
.workflow-builder {
  padding: 20px;
}

.builder-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.builder-grid {
  display: grid;
  grid-template-columns: 250px 1fr 250px;
  gap: 20px;
}

.workflow-canvas {
  grid-column: 2;
  min-height: 500px;
  border: 1px dashed #ccc;
  padding: 20px;
}
</style>