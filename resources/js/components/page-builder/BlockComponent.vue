<template>
  <div 
    class="block"
    :class="{ selected: isSelected }"
    :style="blockStyles"
    @click.stop="$emit('select', $event)"
  >
    <div class="block-content">
      <slot></slot>
    </div>
    <div v-if="isSelected" class="block-handles">
      <div class="resize-handle" @mousedown.stop="startResize"></div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

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
  emits: ['select', 'resize'],
  setup(props, { emit }) {
    const blockStyles = computed(() => ({
      position: 'absolute',
      left: `${props.block.position.x}px`,
      top: `${props.block.position.y}px`,
      width: `${props.block.size.width}px`,
      height: `${props.block.size.height}px`,
      zIndex: props.block.zIndex
    }))

    const startResize = (e) => {
      emit('resize', { 
        event: e,
        blockId: props.block.id
      })
    }

    return {
      blockStyles,
      startResize
    }
  }
}
</script>

<style scoped>
.block {
  border: 1px dashed transparent;
  transition: all 0.2s;
}

.block.selected {
  border-color: #007bff;
  box-shadow: 0 0 0 1px #007bff;
}

.block-content {
  width: 100%;
  height: 100%;
}

.block-handles {
  position: absolute;
  bottom: -5px;
  right: -5px;
  width: 10px;
  height: 10px;
  background: #007bff;
  cursor: nwse-resize;
  border-radius: 50%;
}
</style>