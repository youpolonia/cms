<template>
  <div class="cta-block-editor" draggable="true">
    <div class="block-header">
      <span>Call to Action</span>
      <button @click="$emit('remove')">×</button>
      <button @click="$emit('move-up')">↑</button>
      <button @click="$emit('move-down')">↓</button>
    </div>

    <div class="cta-content">
      <label>
        Button Text:
        <input 
          type="text" 
          v-model="localBlock.content.text"
          @input="updateBlock"
          placeholder="Click me">
      </label>
      
      <label>
        Link URL:
        <input 
          type="text" 
          v-model="localBlock.content.url"
          @input="updateBlock"
          placeholder="https://example.com">
      </label>
    </div>

    <div class="style-options">
      <label>
        Button Style:
        <select v-model="localBlock.content.style" @change="updateBlock">
          <option value="primary">Primary</option>
          <option value="secondary">Secondary</option>
          <option value="outline">Outline</option>
          <option value="ghost">Ghost</option>
        </select>
      </label>

      <label>
        Button Color:
        <input 
          type="color" 
          v-model="localBlock.content.color"
          @change="updateBlock">
      </label>

      <label>
        <input 
          type="checkbox" 
          v-model="localBlock.content.newTab"
          @change="updateBlock">
        Open in new tab
      </label>
    </div>

    <div class="preview">
      <h4>Preview:</h4>
      <button 
        :class="['cta-button', localBlock.content.style]"
        :style="{ backgroundColor: localBlock.content.style === 'primary' ? localBlock.content.color : '' }">
        {{ localBlock.content.text || 'Button' }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    block: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      localBlock: JSON.parse(JSON.stringify(this.block))
    }
  },
  methods: {
    updateBlock() {
      this.$emit('update:block', this.localBlock);
    }
  }
}
</script>

<style scoped>
.cta-block-editor {
  margin: 10px 0;
  padding: 10px;
  border: 1px solid #ddd;
  background: white;
}
.block-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.cta-content {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 15px;
}
.cta-content label {
  display: flex;
  flex-direction: column;
}
.cta-content input {
  padding: 5px;
}
.style-options {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 15px;
}
.style-options label {
  display: flex;
  align-items: center;
  gap: 5px;
}
.preview {
  margin-top: 10px;
}
.cta-button {
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}
.cta-button.primary {
  color: white;
  border: none;
}
.cta-button.secondary {
  background: #f0f0f0;
  border: 1px solid #ddd;
}
.cta-button.outline {
  background: transparent;
  border: 1px solid currentColor;
}
.cta-button.ghost {
  background: transparent;
  border: none;
  text-decoration: underline;
}
</style>