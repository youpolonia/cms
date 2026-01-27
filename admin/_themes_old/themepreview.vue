<template>
  <div class="theme-preview">
    <div class="preview-controls">
      <h3>Live Preview</h3>
      <div class="device-selector">
        <button 
          @click="setDevice('desktop')" 
          :class="{ active: currentDevice === 'desktop' }">
          Desktop
        </button>
        <button 
          @click="setDevice('tablet')" 
          :class="{ active: currentDevice === 'tablet' }">
          Tablet
        </button>
        <button 
          @click="setDevice('mobile')" 
          :class="{ active: currentDevice === 'mobile' }">
          Mobile
        </button>
      </div>
    </div>

    <div class="preview-container" :class="currentDevice">
      <iframe 
        ref="previewFrame" 
        :src="previewUrl" 
        frameborder="0">
      </iframe>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    themeId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      currentDevice: 'desktop',
      previewUrl: ''
    }
  },
  watch: {
    themeId: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.previewUrl = `/api/themes/preview?id=${newVal}`;
        }
      }
    }
  },
  methods: {
    setDevice(device) {
      this.currentDevice = device;
      this.updateFrameSize();
    },
    updateFrameSize() {
      const sizes = {
        desktop: '100%',
        tablet: '768px', 
        mobile: '375px'
      };
      
      if (this.$refs.previewFrame) {
        this.$refs.previewFrame.style.width = sizes[this.currentDevice];
      }
    }
  }
}
</script>

<style scoped>
.theme-preview {
  margin-top: 30px;
  border: 1px solid #eee;
  padding: 20px;
  border-radius: 4px;
}
.preview-controls {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}
.device-selector button {
  padding: 5px 10px;
  margin-left: 5px;
  background: #f5f5f5;
  border: 1px solid #ddd;
  cursor: pointer;
}
.device-selector button.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}
.preview-container {
  position: relative;
  height: 600px;
  overflow: auto;
  border: 1px solid #ddd;
}
.preview-container.desktop {
  width: 100%;
}
.preview-container.tablet {
  width: 768px;
  margin: 0 auto;
}
.preview-container.mobile {
  width: 375px;
  margin: 0 auto;
}
.preview-container iframe {
  height: 100%;
  width: 100%;
}
</style>