<template>
  <div class="block-item p-4 border rounded-lg bg-white shadow-sm">
    <div class="flex justify-between items-center mb-3">
      <div class="flex items-center space-x-2">
        <div class="drag-handle">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
          </svg>
        </div>
        <span class="text-sm font-medium text-gray-700">
          {{ block.type.charAt(0).toUpperCase() + block.type.slice(1) }} Block
        </span>
      </div>
      <div class="flex space-x-2 items-center">
        <button
          @click="showHistory"
          class="history-block text-gray-500 hover:text-gray-700"
          title="Version History"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
          </svg>
        </button>
        <button
          @click="handleExport"
          class="export-block text-purple-500 hover:text-purple-700"
          title="Export Block"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
        <button
          @click="handleDuplicate"
          class="duplicate-block text-blue-500 hover:text-blue-700"
          title="Duplicate Block"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z" />
            <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z" />
          </svg>
        </button>
        <button
          @click="confirmDelete"
          class="delete-block text-red-500 hover:text-red-700"
          title="Delete Block"
        >
        <button
          @click="handleSaveTemplate"
          class="save-template text-blue-500 hover:text-blue-700"
          title="Save as Template"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd" />
          </svg>
        </button>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
    
    <BlockEditor 
      :block="block"
      :page-id="pageId"
      @update="$emit('update', $event)"
    />
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';
import BlockEditor from './BlockEditor.vue';

const props = defineProps({
  block: {
    type: Object,
    required: true
  },
  pageId: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['update', 'delete', 'duplicate', 'export', 'history']);

const confirmDelete = () => {
  if (confirm('Are you sure you want to delete this block?')) {
    emit('delete', props.block.id);
  }
};

const handleDuplicate = () => {
  if (confirm('Duplicate this block?')) {
    emit('duplicate', props.block.id);
  }
};

const handleExport = () => {
  emit('export', props.block);
};

const showHistory = () => {
  emit('history', props.block.id);
};

const handleSaveTemplate = () => {
  emit('save-template', props.block);
};
</script>