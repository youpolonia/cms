<template>
  <div class="gallery-block">
    <div class="gallery-header">
      <h3>{{ data.title || 'Image Gallery' }}</h3>
      <button @click="addImage">Add Image</button>
    </div>
    
    <div class="gallery-grid">
      <div 
        v-for="(image, index) in data.images" 
        :key="index"
        class="gallery-item"
      >
        <img :src="image.url" :alt="image.alt || 'Gallery image'">
        <button @click="removeImage(index)">×</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    data: {
      type: Object,
      default: () => ({
        title: '',
        images: []
      })
    }
  },
  methods: {
    addImage() {
      this.$emit('update', {
        ...this.data,
        images: [...this.data.images, { url: '', alt: '' }]
      })
    },
    removeImage(index) {
      this.$emit('update', {
        ...this.data,
        images: this.data.images.filter((_, i) => i !== index)
      })
    }
  }
}
</script>

<style>
.gallery-block {
  border: 1px solid #eee;
  padding: 15px;
  margin-bottom: 20px;
}

.gallery-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 10px;
}

.gallery-item {
  position: relative;
  padding-top: 100%;
}

.gallery-item img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.gallery-item button {
  position: absolute;
  top: 5px;
  right: 5px;
  background: red;
  color: white;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  cursor: pointer;
}
</style>