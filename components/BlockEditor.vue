<template>
  <div class="block-editor">
    <div class="block-header">
      <h3>{{ block.type }} Block</h3>
      <button @click="$emit('remove')">Delete</button>
    </div>
    
    <div class="block-content">
      <textarea 
        v-model="localBlock.content" 
        placeholder="Enter content..."
      />
    </div>
    
    <div class="block-settings" v-if="hasSettings">
      <h4>Settings</h4>
      <div v-for="(value, key) in localBlock.settings" :key="key">
        <label>{{ key }}:</label>
        <input type="text" v-model="localBlock.settings[key]" />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, computed } from 'vue';

export default {
  props: {
    block: {
      type: Object,
      required: true
    }
  },
  emits: ['update', 'remove'],
  setup(props, { emit }) {
    const localBlock = ref({ ...props.block });
    
    const hasSettings = computed(() => {
      return Object.keys(localBlock.value.settings).length > 0;
    });
    
    watch(localBlock, (newValue) => {
      emit('update', newValue);
    }, { deep: true });
    
    return { localBlock, hasSettings };
  }
};
</script>

<style scoped>
.block-editor {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 15px;
  margin-bottom: 15px;
}
.block-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.block-content textarea {
  width: 100%;
  min-height: 100px;
  padding: 8px;
}
.block-settings {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}
.block-settings div {
  margin-bottom: 10px;
}
.block-settings label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}
</style>