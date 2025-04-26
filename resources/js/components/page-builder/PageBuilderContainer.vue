<template>
  <div class="page-builder-container">
    <PageBuilder
      :blocks="blocks"
      :is-editing="isEditing"
      @add:block="addBlock"
      @update:block="updateBlock"
      @resize:block="resizeBlock"
      @reorder:blocks="reorderBlocks"
      @select:block="selectBlock"
      @save:layout="saveLayout"
      @load:layout="loadLayout"
    />

    <div class="controls">
      <button @click="toggleEditMode" class="control-button">
        {{ isEditing ? 'Preview' : 'Edit' }}
      </button>
    </div>
  </div>
</template>

<script>
import PageBuilder from './PageBuilder.vue'

export default {
  components: {
    PageBuilder
  },
  data() {
    return {
      blocks: [],
      isEditing: true,
      selectedBlockId: null
    }
  },
  methods: {
    addBlock(type) {
      const newBlock = {
        id: Date.now().toString(),
        type,
        content: this.getDefaultContent(type),
        position: { x: 0, y: 0 },
        size: { width: '100%', height: 'auto' }
      }
      this.blocks.push(newBlock)
    },
    getDefaultContent(type) {
      switch(type) {
        case 'Text': return 'New text block'
        case 'Image': return { src: '', altText: '' }
        case 'Video': return { src: '', controls: true }
        case 'Columns': return { columns: [] }
        case 'Button': return { text: 'Button', url: '#' }
        default: return ''
      }
    },
    updateBlock({ index, updates }) {
      this.blocks[index] = { ...this.blocks[index], ...updates }
    },
    resizeBlock({ index, size }) {
      this.blocks[index].size = size
    },
    reorderBlocks({ fromIndex, toIndex }) {
      const [movedBlock] = this.blocks.splice(fromIndex, 1)
      this.blocks.splice(toIndex, 0, movedBlock)
    },
    selectBlock(id) {
      this.selectedBlockId = id
    },
    toggleEditMode() {
      this.isEditing = !this.isEditing
    },
    saveLayout() {
      localStorage.setItem('pageBuilderLayout', JSON.stringify(this.blocks))
      alert('Layout saved successfully!')
    },
    loadLayout() {
      const savedLayout = localStorage.getItem('pageBuilderLayout')
      if (savedLayout) {
        this.blocks = JSON.parse(savedLayout)
        alert('Layout loaded successfully!')
      } else {
        alert('No saved layout found!')
      }
    }
  },
  computed: {
    selectedBlock() {
      return this.blocks.find(b => b.id === this.selectedBlockId)
    }
  }
}
</script>

<style scoped>
.page-builder-container {
  position: relative;
  margin: 20px;
}

.controls {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
}

.control-button {
  background: #007bff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}
</style>