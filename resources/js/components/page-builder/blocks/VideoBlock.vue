<template>
  <BlockComponent 
    :block="block" 
    :is-selected="isSelected"
    @select="$emit('select', $event)"
    @resize="$emit('resize', $event)"
    @dragstart="$emit('dragstart', $event)"
  >
    <div class="video-block">
      <div v-if="loading" class="video-loading">
        <div class="loading-spinner"></div>
        <span>Loading video...</span>
      </div>
      <video 
        v-else-if="src"
        :src="src"
        :poster="poster"
        :autoplay="autoplay"
        :loop="loop"
        :muted="muted"
        controls
        class="video-element"
        @loadedmetadata="handleVideoLoaded"
        @error="handleVideoError"
      ></video>
      <div v-else class="video-upload-container">
        <input 
          type="file" 
          accept="video/*"
          @change="handleVideoUpload"
          class="video-upload-input"
          ref="fileInput"
        >
        <button 
          class="video-upload-button"
          @click="triggerFileInput"
        >
          <span class="icon">↑</span> Upload Video
        </button>
        <div class="video-upload-hint">
          MP4, WebM or OGG up to 50MB
        </div>
      </div>
      <div v-if="showStats && videoInfo" class="video-stats">
        {{ videoInfo.width }}×{{ videoInfo.height }} | {{ videoInfo.duration }}s | {{ videoInfo.size }}
      </div>
      <div v-if="isSelected" class="video-controls">
        <button 
          @click="toggleAutoplay"
          :class="['control-button', { active: autoplay }]"
          title="Autoplay"
        >
          <span class="icon">▶️</span>
        </button>
        <button 
          @click="toggleLoop"
          :class="['control-button', { active: loop }]"
          title="Loop"
        >
          <span class="icon">🔁</span>
        </button>
        <button 
          @click="toggleMute"
          :class="['control-button', { active: muted }]"
          title="Mute"
        >
          <span class="icon">{{ muted ? '🔇' : '🔊' }}</span>
        </button>
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
    poster: {
      type: String,
      default: ''
    },
    isSelected: {
      type: Boolean,
      default: false
    },
    showStats: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select', 'resize', 'update:video', 'dragstart'],
  data() {
    return {
      loading: false,
      autoplay: false,
      loop: false,
      muted: false,
      videoInfo: null,
      error: null
    }
  },
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click()
    },
    handleVideoUpload(e) {
      const file = e.target.files[0]
      if (file) {
        if (file.size > 50 * 1024 * 1024) {
          this.error = 'File size exceeds 50MB limit'
          return
        }

        this.loading = true
        const reader = new FileReader()
        reader.onload = (event) => {
          this.$emit('update:video', {
            src: event.target.result,
            name: file.name,
            size: this.formatFileSize(file.size)
          })
          this.loading = false
        }
        reader.onerror = () => {
          this.error = 'Error reading video file'
          this.loading = false
        }
        reader.readAsDataURL(file)
      }
    },
    handleVideoLoaded(e) {
      const video = e.target
      this.videoInfo = {
        width: video.videoWidth,
        height: video.videoHeight,
        duration: video.duration.toFixed(1),
        size: this.formatFileSize(video.fileSize || 0)
      }
    },
    handleVideoError() {
      this.error = 'Error loading video'
    },
    toggleAutoplay() {
      this.autoplay = !this.autoplay
    },
    toggleLoop() {
      this.loop = !this.loop
    },
    toggleMute() {
      this.muted = !this.muted
    },
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }
  }
}
</script>

<style scoped>
.video-block {
  width: 100%;
  height: 100%;
  position: relative;
}

.video-element {
  width: 100%;
  height: 100%;
  object-fit: cover;
  background: #000;
}

.video-upload-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  gap: 8px;
  background: #f8f9fa;
  border: 2px dashed #dee2e6;
  border-radius: 4px;
  padding: 16px;
}

.video-upload-input {
  display: none;
}

.video-upload-button {
  background: #007bff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.video-upload-hint {
  font-size: 12px;
  color: #6c757d;
}

.video-stats {
  position: absolute;
  bottom: 8px;
  left: 8px;
  font-size: 11px;
  color: white;
  background: rgba(0,0,0,0.7);
  padding: 2px 4px;
  border-radius: 3px;
}

.video-controls {
  position: absolute;
  top: 8px;
  right: 8px;
  display: flex;
  gap: 4px;
}

.control-button {
  background: rgba(0,0,0,0.7);
  color: white;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.control-button.active {
  background: #007bff;
}

.video-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 8px;
  color: #6c757d;
}

.loading-spinner {
  width: 24px;
  height: 24px;
  border: 3px solid rgba(0,123,255,0.3);
  border-radius: 50%;
  border-top-color: #007bff;
  animation: spin 1s linear infinite;
}

.icon {
  font-size: 14px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>