import { ref, computed } from 'vue'

export class SelectionManager {
  constructor() {
    this.selectedBlocks = ref(new Set())
    this.lastSelected = ref(null)
  }

  get selectedCount() {
    return computed(() => this.selectedBlocks.value.size)
  }

  selectBlock(blockId, multiSelect = false) {
    if (!multiSelect) {
      this.selectedBlocks.value.clear()
    }
    
    if (this.selectedBlocks.value.has(blockId)) {
      this.selectedBlocks.value.delete(blockId)
    } else {
      this.selectedBlocks.value.add(blockId)
    }
    this.lastSelected.value = blockId
  }

  selectRange(blocks, startId, endId) {
    const startIdx = blocks.findIndex(b => b.id === startId)
    const endIdx = blocks.findIndex(b => b.id === endId)
    
    if (startIdx === -1 || endIdx === -1) return

    const [from, to] = [startIdx, endIdx].sort((a,b) => a - b)
    this.selectedBlocks.value.clear()

    for (let i = from; i <= to; i++) {
      this.selectedBlocks.value.add(blocks[i].id)
    }
    this.lastSelected.value = endId
  }

  clearSelection() {
    this.selectedBlocks.value.clear()
    this.lastSelected.value = null
  }

  isSelected(blockId) {
    return computed(() => this.selectedBlocks.value.has(blockId))
  }

  getSelectedBlocks(blocks) {
    return computed(() => 
      blocks.filter(block => this.selectedBlocks.value.has(block.id))
  }
}