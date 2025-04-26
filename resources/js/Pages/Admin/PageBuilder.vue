<template>
  <div class="page-builder">
    <div class="toolbar">
      <ResponsiveControls :responsiveService="responsiveService" />
      <button @click="showTemplatePicker = true">Templates</button>
      <button @click="savePage">Save</button>
    </div>

    <div class="canvas" @click="selectionManager.clearSelection()">
      <BlockComponent
        v-for="block in blocks"
        :key="block.id"
        :block="block"
        :isSelected="selectionManager.isSelected(block.id)"
        @select="handleBlockSelect($event, block.id)"
        @update:styles="updateBlockStyles(block, $event)"
      />
    </div>

    <SelectionControls 
      :selectionManager="selectionManager"
      :blocks="blocks"
      @copy="handleCopy"
      @delete="handleDelete"
      @duplicate="handleDuplicate"
    />

    <Modal v-if="showTemplatePicker" @close="showTemplatePicker = false">
      <TemplatePicker 
        :templates="templates"
        :categories="templateCategories"
        @select="applyTemplate"
      />
    </Modal>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { SelectionManager } from '@/services/page-builder/SelectionManager'
import { ResponsiveService } from '@/services/page-builder/ResponsiveService'
import { TemplateService } from '@/services/page-builder/TemplateService'
import BlockComponent from '@/components/page-builder/BlockComponent.vue'
import ResponsiveControls from '@/components/page-builder/ResponsiveControls.vue'
import SelectionControls from '@/components/page-builder/SelectionControls.vue'
import TemplatePicker from '@/components/page-builder/TemplatePicker.vue'
import Modal from '@/components/Modal.vue'

export default {
  components: {
    BlockComponent,
    ResponsiveControls,
    SelectionControls,
    TemplatePicker,
    Modal
  },
  setup() {
    const blocks = ref([])
    const templates = ref([])
    const templateCategories = ref([])
    const showTemplatePicker = ref(false)

    const selectionManager = new SelectionManager()
    const responsiveService = new ResponsiveService()
    const templateService = new TemplateService()

    onMounted(async () => {
      templates.value = await templateService.getTemplates()
      templateCategories.value = await templateService.getTemplateCategories()
    })

    const handleBlockSelect = (event, blockId) => {
      selectionManager.selectBlock(blockId, event.shiftKey || event.ctrlKey)
    }

    const updateBlockStyles = (block, styles) => {
      responsiveService.updateBlockStyles(
        block, 
        styles,
        responsiveService.activeBreakpoint
      )
    }

    const applyTemplate = async (template) => {
      const newBlocks = await templateService.applyTemplate(template.id)
      blocks.value = [...blocks.value, ...newBlocks]
      showTemplatePicker.value = false
    }

    const handleCopy = (selectedBlocks) => {
      // Copy logic
    }

    const handleDelete = (selectedIds) => {
      blocks.value = blocks.value.filter(b => !selectedIds.includes(b.id))
    }

    const handleDuplicate = (selectedBlocks) => {
      const duplicates = selectedBlocks.map(b => ({
        ...b,
        id: generateId(),
        position: { ...b.position, y: b.position.y + 50 }
      }))
      blocks.value = [...blocks.value, ...duplicates]
    }

    const savePage = () => {
      // Save logic
    }

    const generateId = () => {
      return Math.random().toString(36).substr(2, 9)
    }

    return {
      blocks,
      templates,
      templateCategories,
      showTemplatePicker,
      selectionManager,
      responsiveService,
      handleBlockSelect,
      updateBlockStyles,
      applyTemplate,
      handleCopy,
      handleDelete,
      handleDuplicate,
      savePage
    }
  }
}
</script>

<style scoped>
.page-builder {
  position: relative;
  height: 100vh;
}

.toolbar {
  padding: 10px;
  background: #f5f5f5;
  display: flex;
  gap: 10px;
}

.canvas {
  height: calc(100vh - 50px);
  overflow: auto;
  background: #f9f9f9;
  position: relative;
}
</style>