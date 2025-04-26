<template>
  <div class="page-builder-canvas">
    <div class="toolbar">
      <button @click="addTextBlock">Add Text</button>
      <button @click="addImageBlock">Add Image</button>
      <button @click="addVideoBlock">Add Video</button>
      <button @click="savePage">Save Page</button>
    </div>

    <div class="blocks-container">
      <draggable 
        v-model="blocks" 
        group="blocks" 
        @end="onDragEnd"
        item-key="id"
      >
        <template #item="{element}">
          <component 
            :is="getBlockComponent(element.type)" 
            :block="element"
            @update="updateBlock"
            @remove="removeBlock"
          />
        </template>
      </draggable>
    </div>
  </div>
</template>

<script>
import draggable from 'vuedraggable';
import TextBlock from './blocks/TextBlock.vue';
import ImageBlock from './blocks/ImageBlock.vue';
import VideoBlock from './blocks/VideoBlock.vue';

export default {
  components: { draggable },
  data() {
    return {
      blocks: [],
      nextId: 1
    };
  },

  methods: {
    getBlockComponent(type) {
      return {
        text: TextBlock,
        image: ImageBlock,
        video: VideoBlock
      }[type];
    },

    addTextBlock() {
      this.blocks.push({
        id: this.nextId++,
        type: 'text',
        content: 'New text block',
        styles: {}
      });
    },

    addImageBlock() {
      this.blocks.push({
        id: this.nextId++,
        type: 'image',
        src: '',
        alt: '',
        styles: {}
      });
    },

    addVideoBlock() {
      this.blocks.push({
        id: this.nextId++,
        type: 'video',
        src: '',
        styles: {}
      });
    },

    updateBlock(updatedBlock) {
      const index = this.blocks.findIndex(b => b.id === updatedBlock.id);
      if (index >= 0) {
        this.blocks.splice(index, 1, updatedBlock);
      }
    },

    removeBlock(id) {
      this.blocks = this.blocks.filter(b => b.id !== id);
    },

    onDragEnd() {
      // Reorder logic if needed
    },

    async savePage() {
      try {
        const response = await fetch('/api/pages', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            blocks: this.blocks
          })
        });

        if (!response.ok) throw new Error('Failed to save page');
        
        // Handle successful save
      } catch (error) {
        console.error('Error saving page:', error);
      }
    }
  }
};
</script>

<style scoped>
.page-builder-canvas {
  border: 1px solid #ddd;
  min-height: 500px;
  padding: 20px;
}

.toolbar {
  margin-bottom: 20px;
}

.toolbar button {
  margin-right: 10px;
}

.blocks-container {
  min-height: 400px;
  padding: 10px;
  background: #f9f9f9;
}
</style>