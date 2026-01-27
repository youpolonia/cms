<template>
  <div class="trigger-panel">
    <h3>Triggers</h3>
    <div class="trigger-list">
      <div 
        v-for="trigger in triggers"
        :key="trigger.type"
        class="trigger-item"
        @click="selectTrigger(trigger)"
      >
        {{ trigger.label }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TriggerPanel',
  data() {
    return {
      triggers: [
        {
          type: 'content_published',
          label: 'Content Published',
          params: {
            content_type: 'post'
          }
        },
        {
          type: 'user_registered',
          label: 'User Registered',
          params: {
            role: 'any'
          }
        },
        {
          type: 'scheduled_time',
          label: 'Scheduled Time',
          params: {
            time: ''
          }
        }
      ]
    };
  },
  methods: {
    selectTrigger(trigger) {
      this.$emit('trigger-selected', trigger);
    }
  }
};
</script>

<style scoped>
.trigger-panel {
  width: 250px;
  padding: 1rem;
  background: #fff;
  border-right: 1px solid #ddd;
}

h3 {
  margin-top: 0;
  margin-bottom: 1rem;
}

.trigger-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.trigger-item {
  padding: 0.5rem;
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 4px;
  cursor: pointer;
}

.trigger-item:hover {
  background: #e9e9e9;
}
</style>