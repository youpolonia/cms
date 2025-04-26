<template>
  <div class="block-preview">
    <div v-if="block.type === 'text'" class="text-preview">
      <h3>Text Block Preview</h3>
      <div v-html="block.content.text"></div>
    </div>
    
    <div v-else-if="block.type === 'image'" class="image-preview">
      <h3>Image Block Preview</h3>
      <img :src="block.content.src" :alt="block.content.alt" class="preview-image">
      <p v-if="block.content.caption">{{ block.content.caption }}</p>
    </div>
    
    <div v-else-if="block.type === 'video'" class="video-preview">
      <h3>Video Block Preview</h3>
      <video controls class="preview-video">
        <source :src="block.content.src" :type="block.content.type">
      </video>
    </div>
    
    <div v-else class="unknown-preview">
      <h3>Block Preview</h3>
      <p>This block type cannot be previewed</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  block: {
    type: Object,
    required: true
  }
});
</script>

<style scoped>
.block-preview {
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 1rem;
  margin: 1rem 0;
  background: white;
}

.preview-image {
  max-width: 100%;
  max-height: 300px;
  display: block;
  margin: 0 auto;
}

.preview-video {
  max-width: 100%;
  max-height: 300px;
  display: block;
  margin: 0 auto;
}

.text-preview,
.image-preview,
.video-preview {
  h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #4a5568;
  }
}
</style>