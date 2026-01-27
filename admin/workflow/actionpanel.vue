<template>
  <div class="action-panel">
    <h3>Actions</h3>
    <div class="action-list">
      <div 
        v-for="action in actions"
        :key="action.type"
        class="action-item"
        @click="selectAction(action)"
      >
        {{ action.label }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ActionPanel',
  data() {
    return {
      actions: [
        {
          type: 'send_email',
          label: 'Send Email',
          params: {
            recipients: '',
            subject: '',
            body: ''
          }
        },
        {
          type: 'update_content',
          label: 'Update Content',
          params: {
            content_id: '',
            fields: '{}'
          }
        },
        {
          type: 'create_notification',
          label: 'Create Notification',
          params: {
            users: '',
            message: ''
          }
        },
        {
          type: 'run_script',
          label: 'Run Script',
          params: {
            script_path: ''
          }
        }
      ]
    };
  },
  methods: {
    selectAction(action) {
      this.$emit('action-selected', action);
    }
  }
};
</script>

<style scoped>
.action-panel {
  width: 250px;
  padding: 1rem;
  background: #fff;
  border-left: 1px solid #ddd;
}

h3 {
  margin-top: 0;
  margin-bottom: 1rem;
}

.action-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.action-item {
  padding: 0.5rem;
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
}

.action-item:hover {
  background: #e9e9e9;
}
</style>