<template>
  <div class="layout-section" :style="sectionStyles">
    <div class="section-header">
      <h4>Section Layout</h4>
      <button @click="removeSection">Remove Section</button>
    </div>
    
    <div class="columns-container" :style="columnsContainerStyles">
      <div 
        v-for="(column, index) in columns" 
        :key="index"
        class="column"
        :style="getColumnStyle(index)"
        @dragover.prevent
        @drop="onDrop($event, index)"
      >
        <div class="column-content">
          <component 
            v-for="block in column.blocks" 
            :key="block.id"
            :is="getBlockComponent(block.type)"
            :block="block"
            @update="updateBlock(block.id, $event)"
            @remove="removeBlock(block.id, index)"
          />
        </div>
        <button class="add-block-btn" @click="addBlock(index)">+ Add Block</button>
      </div>
    </div>
  </div>
</template>

<script>
import TextBlock from './blocks/TextBlock.vue';
import ImageBlock from './blocks/ImageBlock.vue';
import VideoBlock from './blocks/VideoBlock.vue';

export default {
  props: {
    section: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      columns: this.section.columns || [
        { blocks: [], width: 50 },
        { blocks: [], width: 50 }
      ]
    }
  },

  computed: {
    sectionStyles() {
      return {
        margin: '20px 0',
        padding: '20px',
        border: '1px dashed #ccc',
        backgroundColor: '#f9f9f9'
      }
    },
    columnsContainerStyles() {
      return {
        display: 'flex',
        gap: '20px'
      }
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

    getColumnStyle(index) {
      return {
        flex: `1 1 ${this.columns[index].width}%`,
        minHeight: '200px',
        backgroundColor: '#fff',
        padding: '15px',
        border: '1px solid #eee'
      }
    },

    addBlock(columnIndex) {
      const newBlock = {
        id: Date.now(),
        type: 'text',
        content: 'New text block',
        styles: {}
      };
      this.columns[columnIndex].blocks.push(newBlock);
      this.emitUpdate();
    },

    updateBlock(blockId, updatedBlock) {
      for (const column of this.columns) {
        const index = column.blocks.findIndex(b => b.id === blockId);
        if (index !== -1) {
          column.blocks[index] = updatedBlock;
          break;
        }
      }
      this.emitUpdate();
    },

    removeBlock(blockId, columnIndex) {
      this.columns[columnIndex].blocks = this.columns[columnIndex].blocks.filter(b => b.id !== blockId);
      this.emitUpdate();
    },

    onDrop(event, columnIndex) {
      const blockData = JSON.parse(event.dataTransfer.getData('application/json'));
      this.columns[columnIndex].blocks.push(blockData);
      this.emitUpdate();
    },

    removeSection() {
      this.$emit('remove');
    },

    emitUpdate() {
      this.$emit('update', {
        ...this.section,
        columns: this.columns
      });
    }
  }
}
</script>

<style scoped>
.layout-section {
  margin: 20px 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}

.column {
  position: relative;
}

.column-content {
  min-height: 150px;
}

.add-block-btn {
  margin-top: 10px;
  padding: 5px 10px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>