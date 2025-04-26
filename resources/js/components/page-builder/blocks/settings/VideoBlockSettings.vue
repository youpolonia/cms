<template>
  <div class="video-settings">
    <div class="form-group">
      <label>Video Source</label>
      <input 
        type="text" 
        v-model="localContent.src"
        @change="updateContent"
        placeholder="Video URL"
      >
      <input 
        type="file" 
        accept="video/*"
        @change="handleVideoUpload"
        class="video-upload-input"
      >
      <button @click="triggerFileInput" class="upload-button">
        Upload Video
      </button>
    </div>

    <div class="form-group">
      <label>Autoplay</label>
      <input 
        type="checkbox" 
        v-model="localContent.autoplay"
        @change="updateContent"
      >
    </div>

    <div class="form-group">
      <label>Loop</label>
      <input 
        type="checkbox" 
        v-model="localContent.loop"
        @change="updateContent"
      >
    </div>

    <div class="form-group">
      <label>Controls</label>
      <input 
        type="checkbox" 
        v-model="localContent.controls"
        @change="updateContent"
      >
    </div>

    <div class="form-group">
      <label>Muted</label>
      <input 
        type="checkbox" 
        v-model="localContent.muted"
        @change="updateContent"
      >
    </div>

    <div class="form-group">
      <label>Poster Image</label>
      <input 
        type="text" 
        v-model="localContent.poster"
        @change="updateContent"
        placeholder="Poster image URL"
      >
    </div>
  </div>
</template>

<script>
export default {
  props: {
    src: {
      type: String,
      default: ''
    },
    autoplay: {
      type: Boolean,
      default: false
    },
    loop: {
      type: Boolean,
      default: false
    },
    controls: {
      type: Boolean,
      default: true
    },
    muted: {
      type: Boolean,
      default: false
    },
    poster: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      localContent: {
        src: this.src,
        autoplay: this.autoplay,
        loop: this.loop,
        controls: this.controls,
        muted: this.muted,
        poster: this.poster
      }
    }
  },
  methods: {
    updateContent() {
      this.$emit('update', this.localContent)
    },
    triggerFileInput() {
      this.$el.querySelector('.video-upload-input').click()
    },
    handleVideoUpload(e) {
      const file = e.target.files[0]
      if (file) {
        const reader = new FileReader()
        reader.onload = (event) => {
          this.localContent.src = event.target.result
          this.updateContent()
        }
        reader.readAsDataURL(file)
      }
    }
  }
}
</script>

<style scoped>
.video-settings {
  padding: 10px 0;
}

.upload-button {
  background: #007bff;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 8px;
}

.video-upload-input {
  display: none;
}

input[type="checkbox"] {
  margin-left: 8px;
}
</style>