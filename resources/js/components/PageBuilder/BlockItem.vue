<template>
  <div class="block-item" :class="`block-${block.type}`">
    <div class="block-header">
      <h4>{{ blockTitle }}</h4>
      <div class="block-actions">
        <button @click="$emit('edit', block.id)">Edit</button>
        <button @click="$emit('remove', block.id)">Remove</button>
      </div>
    </div>
    <div class="block-content">
      <component 
        :is="block.type" 
        v-bind="block.content"
        @update="onUpdate"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  block: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['edit', 'remove', 'update'])

const blockTitle = computed(() => {
  return props.block.type.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
})

const onUpdate = (updates) => {
  emit('update', props.block.id, updates)
}
</script>

<style scoped>
.block-item {
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-bottom: 1rem;
  padding: 1rem;
}
.block-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}
.block-actions {
  display: flex;
  gap: 0.5rem;
}
</style>