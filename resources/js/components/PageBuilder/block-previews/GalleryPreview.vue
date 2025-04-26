<template>
  <div class="gallery-preview">
    <div class="preview-header">
      <span class="preview-title">Gallery Block</span>
    </div>
    <div class="image-grid">
      <div 
        v-for="i in 3"
        :key="i"
        class="image-placeholder"
      >
        👆 Upload Images
      </div>
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
  }
}
</script>

<style scoped>
.gallery-preview {
  border: 2px dashed #ddd;
  border-radius: 6px;
  padding: 1rem;
}

.preview-header {
  margin-bottom: 1rem;
}

.preview-title {
  font-weight: bold;
}

.image-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.image-placeholder {
  aspect-ratio: 1/1;
  background: #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  color: #999;
  font-size: 0.9rem;
}
</style>