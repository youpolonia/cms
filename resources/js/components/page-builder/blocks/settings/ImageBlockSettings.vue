<template>
  <div class="image-settings">
    <div class="form-group">
      <label>Image Source</label>
      <input 
        type="text" 
        v-model="localContent.src"
        @change="updateContent"
        placeholder="Image URL"
      >
      <input 
        type="file" 
        accept="image/*"
        @change="handleImageUpload"
        class="image-upload-input"
      >
      <button @click="triggerFileInput" class="upload-button">
        Upload Image
      </button>
    </div>

    <div class="form-group">
      <label>Alt Text</label>
      <input 
        type="text" 
        v-model="localContent.altText"
        @change="updateContent"
        placeholder="Description for screen readers"
      >
    </div>

    <div class="form-group">
      <label>Image Fit</label>
      <select v-model="localContent.objectFit" @change="updateContent">
        <option value="cover">Cover</option>
        <option value="contain">Contain</option>
        <option value="fill">Fill</option>
      </select>
    </div>

    <div class="form-group">
      <label>Border Radius</label>
      <input 
        type="range" 
        v-model="localContent.borderRadius"
        min="0"
        max="50"
        @change="updateContent"
      >
      <span>{{ localContent.borderRadius }}px</span>
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
    altText: {
      type: String,
      default: ''
    },
    objectFit: {
      type: String,
      default: 'cover'
    },
    borderRadius: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      localContent: {
        src: this.src,
        altText: this.altText,
        objectFit: this.objectFit,
        borderRadius: this.borderRadius
      }
    }
  },
  methods: {
    updateContent() {
      this.$emit('update', this.localContent)
    },
    triggerFileInput() {
      this.$el.querySelector('.image-upload-input').click()
    },
    handleImageUpload(e) {
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
.image-settings {
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

.image-upload-input {
  display: none;
}

input[type="range"] {
  width: 100%;
  margin-top: 8px;
}
</style>