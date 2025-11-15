<template>
  <div class="workflow-builder">
    <div class="toolbar">
      <button @click="addTriggerNode">Add Trigger</button>
      <button @click="addActionNode">Add Action</button>
      <button @click="showTemplates = true">Use Template</button>
      <button @click="saveWorkflow">Save</button>
      <button @click="loadWorkflow">Load</button>
    </div>
    
    <WorkflowTemplates
      v-if="showTemplates"
      @use-template="applyTemplate"
      @close="showTemplates = false"
    />
    
    <div 
      class="canvas" 
      ref="canvas"
      @mousemove="handleMouseMove"
      @mouseup="handleMouseUp"
    >
      <WorkflowNode
        v-for="node in nodes"
        :key="node.id"
        :node="node"
        @node-drag-start="handleDragStart"
        @node-updated="updateNode"
        @node-deleted="deleteNode"
      />
    </div>
  </div>
</template>

<script>
import WorkflowNode from './WorkflowNode.vue';
import WorkflowTemplates from './WorkflowTemplates.vue';

export default {
  name: 'WorkflowBuilder',
  components: {
    WorkflowNode,
    WorkflowTemplates
  },
  data() {
    return {
      nodes: [],
      nextNodeId: 1,
      draggingNode: null,
      dragOffset: { x: 0, y: 0 },
      showTemplates: false
    };
  },
  methods: {
    addTriggerNode() {
      this.nodes.push({
        id: this.nextNodeId++,
        type: 'trigger',
        position: { x: 50, y: 50 },
        config: {
          triggerType: 'content_published',
          params: {}
        }
      });
    },
    addActionNode() {
      this.nodes.push({
        id: this.nextNodeId++,
        type: 'action',
        position: { x: 250, y: 50 },
        config: {
          actionType: 'send_email',
          params: {}
        }
      });
    },
    handleDragStart(nodeId, e) {
      const node = this.nodes.find(n => n.id === nodeId);
      if (node) {
        this.draggingNode = node;
        this.dragOffset = {
          x: e.clientX - node.position.x,
          y: e.clientY - node.position.y
        };
      }
    },
    handleMouseMove(e) {
      if (this.draggingNode) {
        this.draggingNode.position = {
          x: e.clientX - this.dragOffset.x,
          y: e.clientY - this.dragOffset.y
        };
      }
    },
    handleMouseUp() {
      this.draggingNode = null;
    },
    updateNode(updatedNode) {
      const index = this.nodes.findIndex(n => n.id === updatedNode.id);
      if (index !== -1) {
        this.nodes.splice(index, 1, updatedNode);
      }
    },
    deleteNode(nodeId) {
      this.nodes = this.nodes.filter(n => n.id !== nodeId);
    },
    async saveWorkflow() {
      try {
        const response = await fetch('/api/workflows/save', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            nodes: this.nodes
          })
        });
        
        const result = await response.json();
        console.log('Workflow saved:', result);
      } catch (error) {
        console.error('Error saving workflow:', error);
      }
    },
    async loadWorkflow() {
      try {
        const response = await fetch('/api/workflows/load');
        const data = await response.json();
        
        if (data.nodes) {
          this.nodes = data.nodes;
          this.nextNodeId = Math.max(...this.nodes.map(n => n.id)) + 1;
        }
      } catch (error) {
        console.error('Error loading workflow:', error);
      }
    },
    applyTemplate(template) {
      if (!this.validateTemplate(template)) {
        console.error('Invalid template structure');
        return;
      }
      
      if (template.isSystem) {
        console.error('Cannot override system workflows');
        return;
      }
      
      this.nodes = template.steps.map(step => ({
        id: this.nextNodeId++,
        type: step.type,
        position: step.position || { x: 50, y: 50 },
        config: step.config
      }));
      
      this.showTemplates = false;
    },
    validateTemplate(template) {
      return template &&
        template.steps &&
        Array.isArray(template.steps) &&
        template.steps.every(step =>
          step.type &&
          (step.type === 'trigger' || step.type === 'action') &&
          step.config
        );
    }
  },
};
</script>

<style scoped>
.workflow-builder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.toolbar {
  padding: 0.5rem;
  background: #f0f0f0;
  border-bottom: 1px solid #ddd;
  display: flex;
  gap: 0.5rem;
}

.toolbar button {
  padding: 0.25rem 0.5rem;
}

.canvas {
  flex: 1;
  position: relative;
  background: #f9f9f9;
  overflow: hidden;
}
</style>