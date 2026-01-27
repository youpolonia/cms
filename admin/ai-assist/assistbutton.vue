<template>
  <button 
    class="ai-assist-button"
    @click="handleClick"
    :disabled="disabled"
    :title="tooltip"
  >
    <slot>{{ label }}</slot>
  </button>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      default: 'AI Assist'
    },
    tooltip: {
      type: String,
      default: 'Get AI assistance'
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    handleClick() {
      this.$emit('click');
    }
  }
};
</script>

<style scoped>
.ai-assist-button {
  padding: 8px 16px;
  border-radius: 4px;
  background: var(--ai-primary);
  color: white;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.ai-assist-button:hover {
  background: var(--ai-primary-dark);
}

.ai-assist-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>