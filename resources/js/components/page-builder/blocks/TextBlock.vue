<template>
  <BlockComponent 
    :block="block"
    :is-selected="isSelected"
    @select="$emit('select', $event)"
    @resize="$emit('resize', $event)"
    @dragstart="$emit('dragstart', $event)"
  >
    <div class="text-container" :style="containerStyles">
      <div 
        class="text-content" 
        :style="textStyles"
        contenteditable="true"
        @input="$emit('update:text', $event.target.innerText)"
      >
        {{ text }}
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
      default: 'Edit this text...'
    },
    isSelected: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select', 'resize', 'update:text', 'dragstart'],
  computed: {
    containerStyles() {
      return {
        padding: '8px'
      }
    },
    textStyles() {
      return {
        color: '#000000',
        fontSize: '16px',
        lineHeight: '1.5',
        minHeight: '100%',
        outline: 'none'
      }
    }
  }
}
</script>

<style scoped>
.text-container {
  width: 100%;
  height: 100%;
}

.text-content {
  width: 100%;
  height: 100%;
}
</style>