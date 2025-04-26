<template>
  <div class="text-block-editor" draggable="true">
    <div class="block-header">
      <span>Text Block</span>
      <button @click="$emit('remove')">×</button>
      <button @click="$emit('move-up')">↑</button>
      <button @click="$emit('move-down')">↓</button>
    </div>
    <textarea 
      v-model="localBlock.content.text"
      @input="updateBlock"
      placeholder="Enter your text here...">
    </textarea>
    <div class="formatting-toolbar">
      <button @click="formatText('bold')">B</button>
      <button @click="formatText('italic')">I</button>
      <button @click="formatText('link')">Link</button>
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
    },
    formatText(format) {
      // Simple formatting - would be enhanced with proper rich text editor
      switch(format) {
        case 'bold':
          this.localBlock.content.text += ' **bold** ';
          break;
        case 'italic':
          this.localBlock.content.text += ' *italic* ';
          break;
        case 'link':
          this.localBlock.content.text += ' [link text](url) ';
          break;
      }
      this.updateBlock();
    }
  }
}
</script>

<style scoped>
.text-block-editor {
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
textarea {
  width: 100%;
  min-height: 100px;
  padding: 8px;
}
.formatting-toolbar {
  margin-top: 5px;
}
.formatting-toolbar button {
  margin-right: 5px;
  padding: 2px 5px;
}
</style>