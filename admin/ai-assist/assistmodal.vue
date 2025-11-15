<template>
  <div class="ai-assist-modal" :class="{ 'sidebar-mode': sidebar }">
    <div class="modal-overlay" @click="close"></div>
    
    <div class="modal-container">
      <div class="modal-header">
        <h3>{{ title }}</h3>
        <button class="close-button" @click="close">&times;</button>
      </div>

      <div class="modal-content">
        <slot name="content"></slot>
      </div>

      <div class="modal-actions">
        <slot name="actions">
          <AssistButton 
            label="Apply" 
            @click="apply"
            class="apply-button"
          />
          <AssistButton 
            label="Cancel" 
            @click="close"
            class="cancel-button"
          />
        </slot>
      </div>
    </div>
  </div>
</template>

<script>
import AssistButton from './AssistButton.vue';

export default {
  components: { AssistButton },
  props: {
    title: {
      type: String,
      default: 'AI Assistant'
    },
    sidebar: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    close() {
      this.$emit('close');
    },
    apply() {
      this.$emit('apply');
    }
  }
};
</script>

<style scoped>
.ai-assist-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-overlay {
  position: absolute;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
}

.modal-container {
  position: relative;
  background: white;
  border-radius: 8px;
  width: 80%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  z-index: 1001;
}

.sidebar-mode .modal-container {
  position: absolute;
  right: 0;
  top: 0;
  height: 100vh;
  width: 400px;
  max-width: 100%;
  border-radius: 0;
  max-height: none;
}

.modal-header {
  padding: 16px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-content {
  padding: 16px;
  overflow-y: auto;
  flex-grow: 1;
}

.modal-actions {
  padding: 16px;
  border-top: 1px solid #eee;
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.close-button {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  padding: 0 8px;
}

.apply-button {
  background: var(--ai-primary);
}

.cancel-button {
  background: #ccc;
}
</style>