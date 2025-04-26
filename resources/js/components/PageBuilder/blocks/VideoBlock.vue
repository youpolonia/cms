<template>
  <div class="video-block" :style="block.styles">
    <div v-if="!block.src" class="upload-area" @click="triggerFileInput">
      Click to upload video
      <input 
        type="file" 
        ref="fileInput"
        accept="video/*"
        @change="handleFileUpload"
        style="display: none"
      >
    </div>
    <video v-else controls :src="block.src"></video>
    <div class="block-controls">
      <button @click="emitRemove">Remove</button>
      <input 
        type="text" 
        v-model="block.src" 
        @input="emitUpdate" 
        placeholder="Or paste video URL"
      >
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
  methods: {
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.block.src = e.target.result;
          this.emitUpdate();
        };
        reader.readAsDataURL(file);
      }
    },
    emitUpdate() {
      this.$emit('update', this.block);
    },
    emitRemove() {
      this.$emit('remove', this.block.id);
    }
  }
};
</script>

<style scoped>
.video-block {
  margin: 10px 0;
  padding: 10px;
  border: 1px dashed #ccc;
  background: white;
}

.upload-area {
  padding: 40px;
  border: 2px dashed #aaa;
  text-align: center;
  cursor: pointer;
}

video {
  max-width: 100%;
  display: block;
}

.block-controls {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
}
</style>