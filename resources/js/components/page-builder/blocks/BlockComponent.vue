<template>
  <div 
    class="block-component"
    :style="blockStyles"
    @click.stop="$emit('select', block)"
    @dragstart="$emit('dragstart', $event)"
  >
    <slot></slot>
  </div>
</template>

<script>
export default {
  props: {
    block: {
      type: Object,
      required: true
    },
    isSelected: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select', 'resize', 'dragstart'],
  computed: {
    blockStyles() {
      return {
        position: 'absolute',
        left: `${this.block.x}px`,
        top: `${this.block.y}px`,
        width: `${this.block.width}px`,
        height: `${this.block.height}px`,
        border: this.isSelected ? '2px dashed #007bff' : 'none',
        backgroundColor: this.isSelected ? 'rgba(0, 123, 255, 0.1)' : 'transparent',
        boxSizing: 'border-box',
        overflow: 'hidden',
        zIndex: this.isSelected ? 100 : 1,
        transition: 'all 0.2s ease'
      }
    }
  }
}
</script>

<style scoped>
.block-component {
  cursor: move;
  user-select: none;
}
</style>