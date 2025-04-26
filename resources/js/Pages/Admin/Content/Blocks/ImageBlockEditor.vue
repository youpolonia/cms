<template>
  <div class="image-block-editor" draggable="true">
    <div class="block-header">
      <span>Image Block</span>
      <button @click="$emit('remove')">×</button>
      <button @click="$emit('move-up')">↑</button>
      <button @click="$emit('move-down')">↓</button>
    </div>
    
    <div v-if="localBlock.content.src" class="image-preview">
      <img :src="localBlock.content.src" :alt="localBlock.content.alt">
      <button @click="removeImage">Remove Image</button>
    </div>
    
    <div v-else class="upload-area" @click="triggerFileInput">
      <input 
        type="file" 
        ref="fileInput"
        accept="image/*"
        @change="handleFileUpload"
        style="display: none">
      <span>Click to upload image</span>
    </div>
    
    <div class="image-settings">
      <label>
        Alt Text:
        <input 
          type="text" 
          v-model="localBlock.content.alt"
          @input="updateBlock"
          placeholder="Describe the image...">
      </label>
      <label>
        Caption:
        <input 
          type="text" 
          v-model="localBlock.content.caption"
          @input="updateBlock"
          placeholder="Optional caption...">
      </label>
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
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    async handleFileUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('image', file);

      try {
        const response = await axios.post('/api/media/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        this.localBlock.content.src = response.data.url;
        this.updateBlock();
      } catch (error) {
        console.error('Upload failed:', error);
      }
    },
    removeImage() {
      this.localBlock.content.src = '';
      this.updateBlock();
    },
    updateBlock() {
      this.$emit('update:block', this.localBlock);
    }
  }
}
</script>

<style scoped>
.image-block-editor {
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
.image-preview {
  margin-bottom: 10px;
}
.image-preview img {
  max-width: 100%;
  max-height: 200px;
  display: block;
  margin-bottom: 5px;
}
.upload-area {
  padding: 20px;
  border: 2px dashed #ccc;
  text-align: center;
  cursor: pointer;
  margin-bottom: 10px;
}
.upload-area:hover {
  border-color: #999;
}
.image-settings {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.image-settings label {
  display: flex;
  flex-direction: column;
}
.image-settings input {
  padding: 5px;
}
</style>