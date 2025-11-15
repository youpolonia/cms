<template>
  <div class="action-config">
    <div v-if="config.actionType === 'send_email'">
      <label>Recipients</label>
      <input 
        type="text" 
        v-model="config.params.recipients"
        @input="updateConfig"
        placeholder="comma-separated emails"
      >
      
      <label>Subject</label>
      <input 
        type="text" 
        v-model="config.params.subject"
        @input="updateConfig"
      >
      
      <label>Message</label>
      <textarea 
        v-model="config.params.body"
        @input="updateConfig"
      ></textarea>
    </div>

    <div v-if="config.actionType === 'update_content'">
      <label>Content ID</label>
      <input 
        type="text" 
        v-model="config.params.content_id"
        @input="updateConfig"
      >
      
      <label>Fields (JSON)</label>
      <textarea 
        v-model="config.params.fields"
        @input="updateConfig"
      ></textarea>
    </div>

    <div v-if="config.actionType === 'create_notification'">
      <label>Users</label>
      <input 
        type="text" 
        v-model="config.params.users"
        @input="updateConfig"
        placeholder="comma-separated user IDs"
      >
      
      <label>Message</label>
      <textarea 
        v-model="config.params.message"
        @input="updateConfig"
      ></textarea>
    </div>

    <div v-if="config.actionType === 'run_script'">
      <label>Script Path</label>
      <input 
        type="text" 
        v-model="config.params.script_path"
        @input="updateConfig"
        placeholder="relative path to script"
      >
    </div>
  </div>
</template>

<script>
export default {
  name: 'ActionConfig',
  props: {
    config: {
      type: Object,
      required: true
    }
  },
  methods: {
    updateConfig() {
      this.$emit('update', this.config);
    }
  }
};
</script>

<style scoped>
.action-config {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  display: block;
  margin-bottom: 0.25rem;
  font-weight: bold;
}

input, textarea {
  width: 100%;
  padding: 0.25rem;
  border: 1px solid #ddd;
  border-radius: 3px;
  margin-bottom: 0.5rem;
}

textarea {
  min-height: 80px;
}
</style>