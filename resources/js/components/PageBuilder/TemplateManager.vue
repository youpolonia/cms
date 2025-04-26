<template>
  <div class="template-manager">
    <h4>Templates</h4>
    
    <div class="template-list">
      <div 
        v-for="template in templates" 
        :key="template.id"
        class="template-item"
        @click="applyTemplate(template)"
      >
        <div class="template-preview">
          <div 
            v-for="block in template.blocks" 
            :key="block.id"
            class="preview-block"
            :style="getBlockStyle(block)"
          ></div>
        </div>
        <div class="template-name">{{ template.name }}</div>
      </div>
    </div>

    <button class="save-template-btn" @click="saveCurrentAsTemplate">
      Save Current as Template
    </button>
  </div>
</template>

<script>
export default {
  props: {
    currentBlocks: {
      type: Array,
      required: true
    }
  },

  data() {
    return {
      templates: [
        {
          id: 1,
          name: 'Hero Section',
          blocks: [
            { type: 'image', styles: { height: '300px', backgroundColor: '#f0f0f0' }},
            { type: 'text', styles: { fontSize: '24px', padding: '20px' }}
          ]
        },
        {
          id: 2, 
          name: 'Text Columns',
          blocks: [
            { type: 'text', styles: { width: '48%', display: 'inline-block' }},
            { type: 'text', styles: { width: '48%', display: 'inline-block' }}
          ]
        }
      ]
    }
  },

  methods: {
    getBlockStyle(block) {
      const baseStyles = {
        margin: '2px',
        backgroundColor: '#ddd',
        border: '1px solid #999'
      };

      if (block.type === 'text') {
        return { ...baseStyles, height: '30px' };
      } else if (block.type === 'image') {
        return { ...baseStyles, height: '50px', backgroundColor: '#aaa' };
      } else {
        return baseStyles;
      }
    },

    applyTemplate(template) {
      const newBlocks = template.blocks.map(block => ({
        ...block,
        id: Date.now() + Math.random(),
        content: block.type === 'text' ? 'Sample text' : ''
      }));
      this.$emit('apply', newBlocks);
    },

    saveCurrentAsTemplate() {
      const name = prompt('Enter template name:');
      if (name) {
        const newTemplate = {
          id: Date.now(),
          name,
          blocks: JSON.parse(JSON.stringify(this.currentBlocks))
        };
        this.templates.push(newTemplate);
      }
    }
  }
}
</script>

<style scoped>
.template-manager {
  padding: 15px;
  background: #f5f5f5;
  border-radius: 5px;
  margin-top: 20px;
}

.template-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 15px;
  margin: 15px 0;
}

.template-item {
  cursor: pointer;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 10px;
  background: white;
}

.template-preview {
  height: 80px;
  margin-bottom: 5px;
  overflow: hidden;
}

.preview-block {
  margin: 2px;
}

.template-name {
  font-size: 12px;
  text-align: center;
}

.save-template-btn {
  width: 100%;
  padding: 8px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>