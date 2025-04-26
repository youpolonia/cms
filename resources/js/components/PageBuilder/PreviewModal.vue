<template>
  <div v-if="isOpen" class="preview-modal">
    <div class="modal-content">
      <button class="close-btn" @click="close">×</button>
      <h3>Page Preview</h3>
      <div class="preview-container">
        <div class="preview-blocks">
          <component 
            v-for="block in blocks" 
            :key="block.id"
            :is="getBlockComponent(block.type)"
            :block="block"
            readonly
          />
        </div>
      </div>
      <button class="save-btn" @click="savePage">Save Page</button>
    </div>
  </div>
</template>

<script>
import TextBlock from './blocks/TextBlock.vue';
import ImageBlock from './blocks/ImageBlock.vue';
import VideoBlock from './blocks/VideoBlock.vue';

export default {
  props: {
    blocks: {
      type: Array,
      required: true
    },
    isOpen: {
      type: Boolean,
      required: true
    }
  },

  methods: {
    getBlockComponent(type) {
      return {
        text: TextBlock,
        image: ImageBlock,
        video: VideoBlock
      }[type];
    },

    close() {
      this.$emit('close');
    },

    savePage() {
      this.$emit('save');
    }
  }
};
</script>

<style scoped>
.preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

.preview-container {
  margin: 20px 0;
}

.preview-blocks {
  border: 1px solid #eee;
  padding: 20px;
}

.save-btn {
  margin-top: 20px;
  padding: 10px 20px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>