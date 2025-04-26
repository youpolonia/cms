<template>
  <BlockComponent
    :block="block"
    :is-selected="isSelected"
    @select="$emit('select', $event)"
    @resize="$emit('resize', $event)"
    @dragstart="$emit('dragstart', $event)"
  >
    <div class="image-container" 
      :style="containerStyles"
      @dragover.prevent
      @drop="handleDrop"
    >
      <div v-if="loading" class="image-loading">
        <div class="loading-spinner"></div>
        <span>Loading image...</span>
      </div>
      <template v-else>
        <img
          ref="image"
          :src="src"
          :alt="altText"
          class="image-content"
          :style="imageStyles"
          @load="handleImageLoad"
          @error="handleImageError"
        >
        <div v-if="!src" class="image-placeholder">
          <span>Drop image here or click to upload</span>
        </div>
      </template>
      
      <div v-if="isSelected" class="image-controls">
        <div class="image-controls-group">
          <input
            type="file"
            accept="image/*"
            @change="handleImageUpload"
            class="image-upload-input"
            ref="fileInput"
          >
          <button 
            class="image-control-button"
            @click="triggerFileInput"
            title="Upload Image"
          >
            <span class="icon">📤</span>
          </button>
          <button 
            class="image-control-button"
            @click="toggleFullWidth"
            :class="{ active: fullWidth }"
            title="Full Width"
          >
            <span class="icon">↔️</span>
          </button>
          <button 
            class="image-control-button"
            @click="toggleContain"
            :class="{ active: contain }"
            title="Contain Mode"
          >
            <span class="icon">📏</span>
          </button>
        </div>
        
        <div class="image-stats" v-if="imageStats">
          {{ imageStats.width }}×{{ imageStats.height }} | {{ imageStats.size }}
        </div>
      </div>
    </div>
  </BlockComponent>
</template>

<script>
import BlockComponent from './BlockComponent.vue'

export default {
  components: { BlockComponent },
  props: {
    block: {
      type: Object,
      required: true
    },
    src: {
      type: String,
      default: ''
    },
    altText: {
      type: String,
      default: ''
    },
    isSelected: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select', 'resize', 'update:image', 'dragstart', 'drop'],
  data() {
    return {
      loading: false,
      error: null,
      imageStats: null,
      fullWidth: false,
      contain: false,
      imageStyles: {
        borderRadius: '0px',
        filter: 'none',
        opacity: 1
      },
      containerStyles: {
        padding: '0px',
        backgroundColor: 'transparent'
      }
    }
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click()
    },
    handleImageUpload(e) {
      const file = e.target.files[0]
      if (file) {
        if (file.size > 10 * 1024 * 1024) {
          this.error = 'Image too large (max 10MB)'
          return
        }
        
        this.loading = true
        const reader = new FileReader()
        reader.onload = (event) => {
          this.$emit('update:image', {
            src: event.target.result,
            size: this.formatFileSize(file.size),
            name: file.name,
            type: file.type
          })
          this.loading = false
        }
        reader.onerror = () => {
          this.error = 'Error reading image'
          this.loading = false
        }
        reader.readAsDataURL(file)
      }
    },
    handleDrop(e) {
      e.preventDefault()
      if (e.dataTransfer.files.length) {
        this.handleImageUpload({ target: { files: e.dataTransfer.files } })
      }
    },
    handleImageLoad() {
      if (this.$refs.image) {
        this.imageStats = {
          width: this.$refs.image.naturalWidth,
          height: this.$refs.image.naturalHeight,
          size: this.formatFileSize(this.$refs.image.naturalWidth * this.$refs.image.naturalHeight * 4)
        }
      }
    },
    handleImageError() {
      this.error = 'Error loading image'
    },
    toggleFullWidth() {
      this.fullWidth = !this.fullWidth
      this.updateImageStyles()
    },
    toggleContain() {
      this.contain = !this.contain
      this.updateImageStyles()
    },
    updateImageStyles() {
      this.imageStyles.objectFit = this.contain ? 'contain' : 'cover'
      this.containerStyles.padding = this.fullWidth ? '0' : '8px'
    },
    formatFileSize(bytes) {
      if (bytes < 1024) return bytes + ' bytes'
      else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB'
      else return (bytes / 1048576).toFixed(1) + ' MB'
    }
  }
}
</script>

<style scoped>
.image-container {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  transition: all 0.2s ease;
}

.image-content {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.2s ease;
}

.image-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f5f5;
  color: #999;
  border: 2px dashed #ddd;
  cursor: pointer;
}

.image-loading {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: rgba(0,0,0,0.1);
  color: #666;
}

.loading-spinner {
  width: 24px;
  height: 24px;
  border: 3px solid rgba(0,123,255,0.3);
  border-radius: 50%;
  border-top-color: #007bff;
  animation: spin 1s linear infinite;
  margin-bottom: 8px;
}

.image-controls {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0,0,0,0.7);
  padding: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.image-controls-group {
  display: flex;
  gap: 4px;
}

.image-control-button {
  background: rgba(255,255,255,0.2);
  color: white;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-control-button.active {
  background: #007bff;
}

.image-stats {
  color: #fff;
  font-size: 12px;
  opacity: 0.8;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>