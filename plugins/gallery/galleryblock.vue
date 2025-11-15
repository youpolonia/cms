<template>
  <div class="gallery-block">
    <div v-if="editMode" class="gallery-editor">
      <input 
        type="file" 
        multiple 
        accept="image/*" 
        @change="handleUpload"
        ref="fileInput"
      >
      <div class="preview">
        <div v-for="img in config.images" :key="img.id" class="image-item">
          <img :src="img.url">
          <button @click="removeImage(img.id)">×</button>
        </div>
      </div>
    </div>
    <div v-else class="gallery-preview">
      <img v-for="img in config.images" :key="img.id" :src="img.url">
    </div>
  </div>
</template>

<script>
export default {
  name: 'GalleryBlock',
  props: {
    config: {
      type: Object,
      required: true
    },
    editMode: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    handleUpload(e) {
      const files = Array.from(e.target.files);
      files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (event) => {
          this.$emit('update', {
            images: [
              ...(this.config.images || []),
              {
                id: Date.now(),
                url: event.target.result,
                file: file
              }
            ]
          });
        };
        reader.readAsDataURL(file);
      });
      this.$refs.fileInput.value = '';
    },
    removeImage(id) {
      this.$emit('update', {
        images: this.config.images.filter(img => img.id !== id)
      });
    }
  }
}
</script>

<style scoped>
.gallery-editor, .gallery-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.image-item {
  position: relative;
}
.image-item button {
  position: absolute;
  top: 0;
  right: 0;
  background: red;
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  cursor: pointer;
}
</style>