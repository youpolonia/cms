<template>
  <div class="blocks-container">
    <draggable
      v-model="blocks"
      item-key="id"
      handle=".drag-handle"
      @end="onDragEnd"
      class="space-y-4"
      :disabled="isReordering"
    >
      <template #header>
        <div v-if="isReordering" class="p-2 text-sm text-gray-500">
          Saving new order...
        </div>
      </template>
      <template #item="{element}">
        <div class="block-container">
          <BlockItem
            :block="element"
            :page-id="pageId"
            @update="handleUpdate"
            @delete="handleDelete"
            @export="exportBlock"
          />
          <button
            @click="togglePreview(element.id)"
            class="preview-toggle"
          >
            {{ showPreview[element.id] ? 'Hide Preview' : 'Show Preview' }}
          </button>
          <BlockPreview
            v-if="showPreview[element.id]"
            :block="element"
            class="mt-2"
            @history="showBlockHistory"
            @save-template="saveBlockAsTemplate"
          />
        </div>
      </template>
    </draggable>
    
    <div class="flex flex-wrap items-center gap-2 mt-4">
      <select v-model="newBlockType" class="border rounded px-2 py-1">
        <option v-for="type in blockTypes" :value="type.value">{{ type.label }}</option>
      </select>
      <button
        @click="addBlock"
        class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
      >
        Add Block
      </button>
      
      <div class="flex items-center space-x-2 ml-4">
        <input
          v-model="templateName"
          placeholder="Template name"
          class="border rounded px-2 py-1 w-40"
        >
        <button
          @click="saveTemplate"
          class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
        >
          Save
        </button>
      </div>
      
      <div class="flex items-center space-x-2">
        <select
          v-model="selectedTemplate"
          @change="loadTemplate"
          class="border rounded px-2 py-1"
        >
          <option value="">Load Template</option>
          <option
            v-for="template in templates"
            :value="template.id"
          >
            {{ template.name }}
          </option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import draggable from 'vuedraggable';
import BlockItem from './BlockItem.vue';
import BlockPreview from './BlockPreview.vue';
import useBlocks from './useBlocks';

const props = defineProps({
  pageId: {
    type: String,
    required: true
  },
  initialBlocks: {
    type: Array,
    default: () => []
  }
});

const { blocks, addBlock, updateBlock, deleteBlock, reorderBlocks, duplicateBlock, exportBlock, saveAsTemplate } = useBlocks(props.pageId, props.initialBlocks);

const showPreview = ref({});
const togglePreview = (blockId) => {
  showPreview.value = {
    ...showPreview.value,
    [blockId]: !showPreview.value[blockId]
  };
};

const newBlockType = ref('');
const blockTypes = [
  { value: 'text', label: 'Text Block' },
  { value: 'image', label: 'Image Block' },
  { value: 'video', label: 'Video Block' }
];

const handleUpdate = (updatedBlock) => {
  updateBlock(updatedBlock);
};

const handleDelete = (blockId) => {
  deleteBlock(blockId);
};

const handleDuplicate = async (blockId) => {
  try {
    await duplicateBlock(blockId);
    // Optional: Show success notification
    // toast.success('Block duplicated successfully');
  } catch (error) {
    // Optional: Show error notification
    // toast.error('Failed to duplicate block');
  }
};

const handleReorder = (newOrder) => {
  reorderBlocks(newOrder);
};
const templateName = ref('');
const selectedTemplate = ref('');
const templates = ref([]);

const saveTemplate = async () => {
  if (!templateName.value.trim()) {
    toast.error('Please enter a template name');
    return;
  }
  
  await saveTemplate(templateName.value);
  templateName.value = '';
  await getTemplates();
};

const loadTemplate = async () => {
  if (!selectedTemplate.value) return;
  await loadTemplate(selectedTemplate.value);
  selectedTemplate.value = '';
};

onMounted(async () => {
  await getTemplates();
});

const isReordering = ref(false);

const onDragEnd = async () => {
  try {
    isReordering.value = true;
    await reorderBlocks(blocks.value);
    // Optional: Show success notification
    // toast.success('Blocks reordered successfully');
  } catch (error) {
    // Revert to previous order if reordering fails
    await fetchBlocks();
    // Optional: Show error notification
    // toast.error('Failed to reorder blocks');
  } finally {
    isReordering.value = false;
  }
};

const saveBlockAsTemplate = (block) => {
  emit('save-template', block);
};
</script>

<style scoped>
.drag-handle {
  cursor: move;
  padding: 8px;
  margin-right: 8px;
  color: #64748b;
}
.block-container {
  position: relative;
}

.preview-toggle {
  position: absolute;
  right: 0;
  top: 0;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.25rem;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  cursor: pointer;
}
</style>