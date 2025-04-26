<template>
  <div class="page-builder">
    <div class="builder-toolbar">
      <button @click="undo" :disabled="!canUndo">Undo</button>
      <button @click="redo" :disabled="!canRedo">Redo</button>
      <button @click="savePage">Save</button>
    </div>

    <div class="builder-container">
      <div class="blocks-palette">
        <div v-for="blockType in availableBlockTypes" 
             :key="blockType"
             class="block-item"
             draggable="true"
             @dragstart="startDrag($event, blockType)">
          {{ blockType }}
        </div>
        <button @click="getBlockSuggestions">Suggest Blocks</button>
      </div>

      <div class="page-canvas" 
           @drop="onDrop($event)" 
           @dragover.prevent
           @dragenter.prevent>
        <template v-for="(block, index) in blocks" :key="block.id">
          <component 
            :is="getBlockComponent(block.type)"
            :block="block"
            @update:block="updateBlock(index, $event)"
            @remove="removeBlock(index)"
            @move-up="moveBlockUp(index)"
            @move-down="moveBlockDown(index)"
          />
        </template>
      </div>

      <div class="preview-pane">
        <h3>Preview</h3>
        <div v-html="renderedContent"></div>
      </div>
    </div>

    <div v-if="suggestions.length > 0" class="suggestions-panel">
      <h3>Suggested Blocks</h3>
      <div v-for="(suggestion, index) in suggestions" :key="index">
        {{ suggestion.type }} ({{ (suggestion.score * 100).toFixed(0) }}%)
        <button @click="addSuggestedBlock(suggestion)">Add</button>
      </div>
    </div>
  </div>
</template>

<script>
import TextBlockEditor from './Blocks/TextBlockEditor.vue';
import ImageBlockEditor from './Blocks/ImageBlockEditor.vue';
import CtaBlockEditor from './Blocks/CtaBlockEditor.vue';

export default {
  components: {
    TextBlockEditor,
    ImageBlockEditor,
    CtaBlockEditor
  },
  props: {
    pageId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      blocks: [],
      availableBlockTypes: ['text-block', 'image-block', 'cta-block'],
      history: [],
      historyIndex: -1,
      suggestions: [],
      renderedContent: ''
    }
  },
  computed: {
    canUndo() {
      return this.historyIndex > 0;
    },
    canRedo() {
      return this.historyIndex < this.history.length - 1;
    }
  },
  async mounted() {
    await this.loadPageBlocks();
    this.saveHistory();
  },
  methods: {
    getBlockComponent(blockType) {
      return {
        'text-block': TextBlockEditor,
        'image-block': ImageBlockEditor,
        'cta-block': CtaBlockEditor
      }[blockType];
    },
    async loadPageBlocks() {
      const response = await axios.get(`/api/page-builder/${this.pageId}/blocks`);
      this.blocks = response.data.blocks;
      this.updatePreview();
    },
    startDrag(event, blockType) {
      event.dataTransfer.setData('blockType', blockType);
    },
    onDrop(event) {
      const blockType = event.dataTransfer.getData('blockType');
      this.addBlock(blockType);
    },
    addBlock(type) {
      const newBlock = {
        id: Date.now(),
        type,
        content: this.getDefaultContent(type),
        order: this.blocks.length
      };
      this.blocks.push(newBlock);
      this.updatePreview();
      this.saveHistory();
    },
    getDefaultContent(type) {
      const defaults = {
        'text-block': { text: '' },
        'image-block': { src: '', alt: '' },
        'cta-block': { text: '', url: '' }
      };
      return defaults[type];
    },
    updateBlock(index, updatedBlock) {
      this.blocks[index] = updatedBlock;
      this.updatePreview();
      this.saveHistory();
    },
    removeBlock(index) {
      this.blocks.splice(index, 1);
      this.updatePreview();
      this.saveHistory();
    },
    moveBlockUp(index) {
      if (index > 0) {
        [this.blocks[index], this.blocks[index-1]] = [this.blocks[index-1], this.blocks[index]];
        this.updatePreview();
        this.saveHistory();
      }
    },
    moveBlockDown(index) {
      if (index < this.blocks.length - 1) {
        [this.blocks[index], this.blocks[index+1]] = [this.blocks[index+1], this.blocks[index]];
        this.updatePreview();
        this.saveHistory();
      }
    },
    async savePage() {
      await axios.post(`/api/page-builder/${this.pageId}/blocks`, {
        blocks: this.blocks
      });
    },
    saveHistory() {
      this.history = this.history.slice(0, this.historyIndex + 1);
      this.history.push(JSON.parse(JSON.stringify(this.blocks)));
      this.historyIndex = this.history.length - 1;
    },
    undo() {
      if (this.canUndo) {
        this.historyIndex--;
        this.blocks = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
        this.updatePreview();
      }
    },
    redo() {
      if (this.canRedo) {
        this.historyIndex++;
        this.blocks = JSON.parse(JSON.stringify(this.history[this.historyIndex]));
        this.updatePreview();
      }
    },
    updatePreview() {
      // Simple preview rendering - would be enhanced with actual block renderers
      this.renderedContent = this.blocks.map(block => {
        return `<div class="block-${block.type}">${JSON.stringify(block.content)}</div>`;
      }).join('');
    },
    async getBlockSuggestions() {
      const response = await axios.post('/api/page-builder/suggest-blocks', {
        content: this.blocks.map(b => b.content).join(' '),
        current_blocks: this.blocks.map(b => b.type)
      });
      this.suggestions = response.data.suggestions;
    },
    addSuggestedBlock(suggestion) {
      this.addBlock(suggestion.type);
    }
  }
}
</script>

<style scoped>
.page-builder {
  display: flex;
  flex-direction: column;
  height: 100vh;
}
.builder-toolbar {
  padding: 10px;
  background: #eee;
}
.builder-container {
  display: flex;
  flex: 1;
}
.blocks-palette {
  width: 200px;
  padding: 10px;
  background: #f5f5f5;
}
.block-item {
  padding: 8px;
  margin: 5px 0;
  background: white;
  border: 1px solid #ddd;
  cursor: move;
}
.page-canvas {
  flex: 1;
  padding: 20px;
  background: white;
}
.preview-pane {
  width: 300px;
  padding: 20px;
  border-left: 1px solid #ddd;
  overflow-y: auto;
}
.suggestions-panel {
  position: fixed;
  bottom: 0;
  right: 0;
  width: 300px;
  background: white;
  border: 1px solid #ddd;
  padding: 10px;
}
</style>