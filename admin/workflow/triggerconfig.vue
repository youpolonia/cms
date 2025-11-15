<template>
  <div class="trigger-config">
    <div v-if="config.triggerType === 'content_published'">
      <label>Content Type</label>
      <select v-model="config.params.content_type" @change="updateConfig">
        <option value="post">Post</option>
        <option value="page">Page</option>
        <option value="product">Product</option>
      </select>
    </div>

    <div v-if="config.triggerType === 'user_registered'">
      <label>User Role</label>
      <select v-model="config.params.role" @change="updateConfig">
        <option value="any">Any Role</option>
        <option value="editor">Editor</option>
        <option value="author">Author</option>
        <option value="subscriber">Subscriber</option>
      </select>
    </div>

    <div v-if="config.triggerType === 'scheduled_time'">
      <label>Schedule Time</label>
      <input 
        type="datetime-local" 
        v-model="config.params.time"
        @change="updateConfig"
      >
    </div>
  </div>
</template>

<script>
export default {
  name: 'TriggerConfig',
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
.trigger-config {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  display: block;
  margin-bottom: 0.25rem;
  font-weight: bold;
}

select, input {
  width: 100%;
  padding: 0.25rem;
  border: 1px solid #ddd;
  border-radius: 3px;
}
</style>