<template>
  <BlockComponent 
    :block="block"
    :is-selected="isSelected"
    @select="$emit('select', $event)"
    @resize="$emit('resize', $event)"
    @dragstart="$emit('dragstart', $event)"
  >
    <div class="columns-container" :style="containerStyles">
      <div 
        v-for="(column, index) in block.columns" 
        :key="index"
        class="column"
        :style="getColumnStyle(index)"
      >
        <div class="column-content">
          <component
            v-for="child in column.blocks"
            :key="child.id"
            :is="getComponentForBlock(child)"
            :block="child"
            @select="$emit('select-child', { parent: block, child })"
          />
        </div>
        <div v-if="isSelected" class="column-controls">
          <button @click="addColumn(index)" class="column-add-button">+</button>
          <button @click="removeColumn(index)" class="column-remove-button">-</button>
        </div>
      </div>
    </div>
  </BlockComponent>
</template>

<script>
import BlockComponent from './BlockComponent.vue'
import { blockComponents } from '../blockComponents'

export default {
  components: { BlockComponent },
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
  emits: ['select', 'resize', 'dragstart', 'select-child'],
  computed: {
    containerStyles() {
      return {
        display: 'flex',
        gap: '16px',
        padding: '8px',
        height: '100%'
      }
    }
  },
  methods: {
    getColumnStyle(index) {
      return {
        flex: this.block.columns[index].width || 1,
        position: 'relative',
        minHeight: '100%'
      }
    },
    addColumn(index) {
      this.block.columns.splice(index + 1, 0, { width: 1, blocks: [] })
    },
    removeColumn(index) {
      if (this.block.columns.length > 1) {
        this.block.columns.splice(index, 1)
      }
    },
    getComponentForBlock(block) {
      return blockComponents[block.type] || null
    }
  }
}
</script>

<style scoped>
.columns-container {
  background-color: rgba(0,123,255,0.05);
}

.column {
  background-color: rgba(0,123,255,0.1);
  border-radius: 4px;
}

.column-content {
  padding: 8px;
  height: 100%;
}

.column-controls {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  gap: 4px;
  padding: 4px;
  background: rgba(0,0,0,0.7);
}

.column-add-button,
.column-remove-button {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: none;
  background: #007bff;
  color: white;
  cursor: pointer;
}

.column-remove-button {
  background: #dc3545;
}
</style>