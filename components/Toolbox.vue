<template>
  <div class="toolbox">
    <div class="search-box">
      <input 
        v-model="searchQuery" 
        placeholder="Search blocks..." 
        @input="filterBlocks"
      />
    </div>
    
    <div class="categories">
      <button 
        v-for="category in categories" 
        :key="category"
        :class="{ active: activeCategory === category }"
        @click="setActiveCategory(category)"
      >
        {{ category }}
      </button>
    </div>
    
    <div class="blocks-list">
      <div 
        v-for="block in filteredBlocks" 
        :key="block.type"
        class="block-item"
        @click="$emit('add-block', block.type)"
      >
        <div class="block-icon">
          {{ block.icon }}
        </div>
        <div class="block-label">
          {{ block.label }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  emits: ['add-block'],
  setup() {
    const searchQuery = ref('');
    const activeCategory = ref('All');
    
    const blockTypes = [
      { type: 'text', label: 'Text Block', icon: 'T', category: 'Basic' },
      { type: 'image', label: 'Image Block', icon: '📷', category: 'Media' },
      { type: 'heading', label: 'Heading', icon: 'H', category: 'Basic' },
      { type: 'video', label: 'Video', icon: '▶️', category: 'Media' },
      { type: 'columns', label: 'Columns', icon: '⏸️', category: 'Layout' },
      { type: 'button', label: 'Button', icon: '🔘', category: 'Basic' },
    ];
    
    const categories = computed(() => {
      const allCategories = ['All', ...new Set(blockTypes.map(b => b.category))];
      return allCategories;
    });
    
    const filteredBlocks = computed(() => {
      let blocks = blockTypes;
      
      if (activeCategory.value !== 'All') {
        blocks = blocks.filter(b => b.category === activeCategory.value);
      }
      
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        blocks = blocks.filter(b => 
          b.label.toLowerCase().includes(query) || 
          b.type.toLowerCase().includes(query)
        );
      }
      
      return blocks;
    });
    
    const setActiveCategory = (category) => {
      activeCategory.value = category;
    };
    
    return { 
      searchQuery, 
      activeCategory,
      categories,
      filteredBlocks,
      setActiveCategory
    };
  }
};
</script>

<style scoped>
.toolbox {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 15px;
  margin-bottom: 20px;
}
.search-box {
  margin-bottom: 15px;
}
.search-box input {
  width: 100%;
  padding: 8px;
}
.categories {
  display: flex;
  gap: 5px;
  margin-bottom: 15px;
  flex-wrap: wrap;
}
.categories button {
  padding: 5px 10px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 4px;
  cursor: pointer;
}
.categories button.active {
  background: #eee;
}
.blocks-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 10px;
}
.block-item {
  border: 1px solid #eee;
  padding: 10px;
  border-radius: 4px;
  cursor: pointer;
  text-align: center;
}
.block-item:hover {
  background: #f5f5f5;
}
.block-icon {
  font-size: 24px;
  margin-bottom: 5px;
}
.block-label {
  font-size: 12px;
}
</style>