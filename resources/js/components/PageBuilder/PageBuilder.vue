<template>
  <div class="page-builder">
    <div class="builder-container">
      <!-- Blocks Panel -->
      <BlocksList
        class="blocks-panel"
        @add-block="addBlock"
      />
      
      <!-- Main Canvas Area -->
      <div
        class="canvas-area"
        @click="deselectBlock"
      >
        <div class="toolbar">
          <button @click="undo" :disabled="historyIndex <= 0" class="toolbar-button">
            Undo
          </button>
          <button @click="redo" :disabled="historyIndex >= history.length - 1" class="toolbar-button">
            Redo
          </button>
          <button
            @click="savePage"
            class="toolbar-button save-button"
            :class="{'is-dirty': isDirty, 'saving': isSaving}"
            :title="isDirty ? 'You have unsaved changes' : 'All changes saved'">
            <span v-if="!isSaving">
              <span v-if="isDirty">⏺️</span> Save Page
            </span>
            <span v-else>Saving...</span>
          </button>
        </div>

        <draggable
          v-model="pageBlocks"
          class="page-preview-wrapper"
          group="blocks"
          item-key="id"
          @end="onDragEnd"
        >
          <template #item="{element}">
            <div
              class="draggable-block"
              :class="{ 'selected': selectedBlock?.id === element.id }"
              @click.stop="selectBlock(element)"
            >
              <div
                class="block-controls"
                @click="removeBlock(element.id)"
              >
                ✖
              </div>
              <component
                :is="`${element.type}-preview`"
                :block="element"
              />
            </div>
          </template>
        </draggable>
      </div>
      
      <!-- Edit Panel -->
      <EditPanel
        v-if="selectedBlock"
        :block="selectedBlock"
        @update="updateBlock"
        @close="deselectBlock"
      />
    </div>
  </div>
</template>

<script>
import BlocksList from './BlocksList.vue'
import EditPanel from './EditPanel.vue'
import draggable from 'vuedraggable'

export default {
  components: {
    BlocksList,
    EditPanel,
    draggable
  },
  data() {
    return {
      isSaving: false,
      pageBlocks: [],
      nextBlockId: 1,
      selectedBlock: null,
      history: [],
      historyIndex: -1,
      maxHistorySteps: 20,
      lastSavedSnapshot: null,
      isDirty: false
    }
  },
  methods: {
    addBlock(blockType) {
      this.saveHistory()
      this.pageBlocks.push({
        id: this.nextBlockId++,
        type: blockType,
        data: this.getDefaultBlockData(blockType)
      })
    },
    getDefaultBlockData(type) {
      switch(type) {
        case 'gallery': return { images: [] }
        case 'form': return { fields: [], submitText: 'Submit' }
        default: return {}
      }
    },
    saveHistory() {
      if (!this.isDirty) {
        const currentSnapshot = JSON.stringify(this.pageBlocks)
        const previousSnapshot = this.lastSavedSnapshot ||
                               (this.historyIndex >= 0 ?
                                JSON.stringify(this.history[this.historyIndex]) :
                                null)
        this.isDirty = !previousSnapshot ? false : currentSnapshot !== previousSnapshot
      }
      if (this.historyIndex > -1 &&
          JSON.stringify(this.history[this.historyIndex]) === JSON.stringify(this.pageBlocks)) {
        return
      }

      if (this.historyIndex < this.history.length - 1) {
        this.history = this.history.slice(0, this.historyIndex + 1)
      }

      const snapshot = JSON.parse(JSON.stringify(this.pageBlocks))
      this.history.push(snapshot)
      this.historyIndex++
      
      while (this.history.length > this.maxHistorySteps) {
        this.history.shift()
        this.historyIndex--
      }
    },
    undo() {
      if (this.historyIndex > 0) {
        this.historyIndex--
        this.pageBlocks = JSON.parse(JSON.stringify(this.history[this.historyIndex]))
      }
    },
    redo() {
      if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++
        this.pageBlocks = JSON.parse(JSON.stringify(this.history[this.historyIndex]))
      }
    },
    async savePage() {
      this.isSaving = true
      try {
        const response = await this.$api.pageBuilder.save(this.pageBlocks)
        this.lastSavedSnapshot = JSON.stringify(this.history[this.historyIndex])
        this.isDirty = false
        this.$notify.success('Page saved successfully')
      } catch (error) {
        console.error('Error saving page:', error)
        this.$notify.error('Failed to save page')
      } finally {
        this.isSaving = false
      }
    },
    onDragEnd() {
      this.saveHistory()
      console.log('Blocks reordered', this.pageBlocks)
    },
    selectBlock(block) {
      this.selectedBlock = block
    },
    deselectBlock() {
      this.selectedBlock = null
    },
    updateBlock(updatedBlock) {
      this.saveHistory()
      const index = this.pageBlocks.findIndex(b => b.id === updatedBlock.id)
      if (index > -1) {
        this.$set(this.pageBlocks, index, updatedBlock)
      }
    },
    handleKeyDown(e) {
      // Undo/Redo
      if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
        e.preventDefault()
        this.undo()
      } else if ((e.ctrlKey || e.metaKey) && e.key === 'y' ||
                ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'z')) {
        e.preventDefault()
        this.redo()
      }
      
      // Save (Ctrl/Cmd + S)
      else if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault()
        this.savePage()
      }
      
      // Block navigation
      else if (this.selectedBlock) {
        const index = this.pageBlocks.findIndex(b => b.id === this.selectedBlock.id)
        if (e.key === 'ArrowUp' && index > 0) {
          e.preventDefault()
          this.selectBlock(this.pageBlocks[index - 1])
        }
        else if (e.key === 'ArrowDown' && index < this.pageBlocks.length - 1) {
          e.preventDefault()
          this.selectBlock(this.pageBlocks[index + 1])
        }
        else if (e.key === 'Delete' || e.key === 'Backspace') {
          e.preventDefault()
          this.removeBlock(this.selectedBlock.id)
        }
      }
    },
    
    removeBlock(blockId) {
      this.saveHistory()
      this.pageBlocks = this.pageBlocks.filter(b => b.id !== blockId)
      if (this.selectedBlock?.id === blockId) {
        this.selectedBlock = null
      }
    },
    
    duplicateBlock(blockId) {
      const existingBlock = this.pageBlocks.find(b => b.id === blockId)
      if (existingBlock) {
        const newBlock = {
          ...JSON.parse(JSON.stringify(existingBlock)),
          id: this.nextBlockId++
        }
        this.pageBlocks.splice(
          this.pageBlocks.indexOf(existingBlock) + 1,
          0,
          newBlock
        )
        this.saveHistory()
        this.selectBlock(newBlock)
        return newBlock
      }
    }
  }
}
</script>

<style scoped>
.page-builder {
  height: 100%;
  display: flex;
}

.builder-container {
  display: flex;
  width: 100%;
  height: 100%;
  gap: 20px;
  position: relative;
}

.blocks-panel {
  width: 250px;
  border-right: 1px solid #eee;
}

.canvas-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding-right: 300px;
}

.toolbar {
  padding: 10px;
  background: #f5f5f5;
  border-bottom: 1px solid #eee;
}

.toolbar-button {
  padding: 8px 16px;
  margin-right: 8px;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.toolbar-button[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
}

.toolbar-button:not(.save-button) {
  background: #2196F3;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.save-button:hover {
  background: #45a049;
}

.page-preview-wrapper {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.draggable-block {
  position: relative;
  cursor: move;
}

.block-controls {
  position: absolute;
  right: -10px;
  top: -10px;
  width: 24px;
  height: 24px;
  background: red;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  opacity: 0;
  transition: opacity 0.2s;
}

.draggable-block {
  position: relative;
  border: 2px solid transparent;
  border-radius: 4px;
  transition: border-color 0.2s ease;
}
.draggable-block.selected {
  border-color: #0084ff;
}
.draggable-block:hover .block-controls,
.draggable-block.selected .block-controls {
  opacity: 1;
}
</style>