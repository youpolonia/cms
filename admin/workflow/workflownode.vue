<template>
  <div 
    class="workflow-node"
    :class="node.type"
    :style="{ left: node.position.x + 'px', top: node.position.y + 'px' }"
    @mousedown="startDrag"
  >
    <div class="node-header">
      {{ nodeTitle }}
      <button class="delete-btn" @click="deleteNode">×</button>
    </div>
    
    <div class="node-content">
      <TriggerConfig 
        v-if="node.type === 'trigger'"
        :config="node.config"
        @update="updateConfig"
      />
      
      <ActionConfig
        v-if="node.type === 'action'"
        :config="node.config"
        @update="updateConfig"
      />
    </div>
  </div>
</template>

<script>
import TriggerConfig from './TriggerConfig.vue';
import ActionConfig from './ActionConfig.vue';

export default {
  name: 'WorkflowNode',
  components: {
    TriggerConfig,
    ActionConfig
  },
  props: {
    node: {
      type: Object,
      required: true
    }
  },
  computed: {
    nodeTitle() {
      return this.node.type === 'trigger' 
        ? `Trigger: ${this.node.config.triggerType}`
        : `Action: ${this.node.config.actionType}`;
    }
  },
  methods: {
    startDrag(e) {
      this.$emit('node-drag-start', this.node.id, e);
    },
    updateConfig(updatedConfig) {
      this.$emit('node-updated', {
        ...this.node,
        config: updatedConfig
      });
    },
    deleteNode() {
      this.$emit('node-deleted', this.node.id);
    }
  }
};
</script>

<style scoped>
.workflow-node {
  position: absolute;
  width: 250px;
  background: white;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  cursor: move;
}

.node-header {
  padding: 0.5rem;
  background: #f0f0f0;
  border-bottom: 1px solid #ddd;
  font-weight: bold;
  display: flex;
  justify-content: space-between;
}

.node-content {
  padding: 0.5rem;
}

.delete-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  line-height: 1;
}

.trigger {
  border-left: 4px solid #4CAF50;
}

.action {
  border-left: 4px solid #2196F3;
}
</style>