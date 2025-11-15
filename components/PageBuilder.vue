<template>
  <div class="page-builder">
    <Toolbox @add-block="addBlock" />
    <div class="blocks-container">
      <BlockEditor 
        v-for="(block, index) in blocks" 
        :key="block.id"
        :block="block"
        @update="updateBlock(index, $event)"
        @remove="removeBlock(index)"
      />
    </div>
    <button @click="savePage">Save Page</button>
  </div>
</template>

<script>
import Toolbox from './Toolbox.vue';
import BlockEditor from './BlockEditor.vue';
import { savePageData } from '../data/pages/pageStorage.js';

export default {
  components: { Toolbox, BlockEditor },
  data() {
    return {
      blocks: [],
      pageId: null
    };
  },
  methods: {
    addBlock(blockType) {
      this.blocks.push({
        id: Date.now(),
        type: blockType,
        content: '',
        settings: {}
      });
    },
    updateBlock(index, updatedBlock) {
      this.blocks.splice(index, 1, updatedBlock);
    },
    removeBlock(index) {
      this.blocks.splice(index, 1);
    },
    async savePage() {
      try {
        await savePageData(this.pageId || 'new-page', {
          blocks: this.blocks
        });
        alert('Page saved successfully');
      } catch (error) {
        console.error('Error saving page:', error);
        alert('Error saving page');
      }
    }
  }
};
</script>

<style scoped>
.page-builder {
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px;
}
.blocks-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
</style>