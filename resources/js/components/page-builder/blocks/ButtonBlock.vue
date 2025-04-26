<template>
  <BlockComponent 
    :block="block" 
    :is-selected="isSelected"
    @select="$emit('select', $event)"
    @resize="$emit('resize', $event)"
    @dragstart="$emit('dragstart', $event)"
  >
    <div class="button-container">
      <button
        class="button-element"
        :style="buttonStyles"
        @click="$emit('click', $event)"
        :disabled="disabled"
      >
        <span v-if="loading" class="loading-indicator"></span>
        <span v-else>{{ text }}</span>
      </button>
      <div v-if="showStats" class="button-stats">
        <span class="stat">{{ buttonWidth }}px × {{ buttonHeight }}px</span>
      </div>
    </div>
  </BlockComponent>
</template>

<script>
import BlockComponent from './BlockComponent.vue'

export default {
  components: { BlockComponent },
  props: {
    block: {
      type: Object,
      required: true
    },
    text: {
      type: String,
      default: 'Button'
    },
    color: {
      type: String,
      default: '#007bff'
    },
    textColor: {
      type: String,
      default: '#ffffff'
    },
    isSelected: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    showStats: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select', 'resize', 'click', 'dragstart'],
  computed: {
    buttonStyles() {
      return {
        backgroundColor: this.color,
        color: this.textColor,
        padding: '8px 16px',
        border: 'none',
        borderRadius: '4px',
        cursor: this.disabled ? 'not-allowed' : 'pointer',
        fontSize: '14px',
        width: '100%',
        height: '100%',
        opacity: this.disabled ? 0.7 : 1,
        transition: 'all 0.2s ease'
      }
    },
    buttonWidth() {
      return this.block?.width || 100
    },
    buttonHeight() {
      return this.block?.height || 40
    }
  }
}
</script>

<style scoped>
.button-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.button-element {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.loading-indicator {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-radius: 50%;
  border-top-color: #fff;
  animation: spin 1s ease-in-out infinite;
}

.button-stats {
  position: absolute;
  bottom: -20px;
  right: 0;
  font-size: 11px;
  color: #666;
  background: rgba(255,255,255,0.9);
  padding: 2px 4px;
  border-radius: 3px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>