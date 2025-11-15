<template>
  <div class="block-selector">
    <div class="search-bar">
      <input 
        v-model="searchQuery"
        placeholder="Search blocks..."
        class="search-input"
      >
    </div>
    
    <div class="categories">
      <button
        v-for="category in categories"
        :key="category"
        @click="setActiveCategory(category)"
        :class="{ active: activeCategory === category }"
      >
        {{ category }}
      </button>
    </div>
    
    <div class="blocks-grid">
      <div
        v-for="block in filteredBlocks"
        :key="block.type"
        class="block-item"
        @click="selectBlock(block)"
      >
        <div class="block-icon">
          <i class="material-icons">{{ block.icon }}</i>
        </div>
        <div class="block-label">{{ block.label }}</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      searchQuery: '',
      activeCategory: 'all',
      blocks: []
    }
  },
  
  computed: {
    categories() {
      const cats = new Set(['all']);
      this.blocks.forEach(block => cats.add(block.category));
      return Array.from(cats);
    },
    
    filteredBlocks() {
      return this.blocks.filter(block => {
        const matchesSearch = block.label.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                             block.type.toLowerCase().includes(this.searchQuery.toLowerCase());
        const matchesCategory = this.activeCategory === 'all' || 
                               block.category === this.activeCategory;
        return matchesSearch && matchesCategory;
      });
    }
  },
  
  methods: {
    setActiveCategory(category) {
      this.activeCategory = category;
    },
    
    selectBlock(block) {
      this.$emit('select', block.type);
    },
    
    async loadBlocks() {
      try {
        const response = await fetch('/api/blocks');
        this.blocks = await response.json();
      } catch (error) {
        console.error('Failed to load blocks:', error);
      }
    }
  },
  
  created() {
    this.loadBlocks();
  }
}
</script>

<style scoped>
.block-selector {
  padding: 1rem;
  max-width: 800px;
  margin: 0 auto;
}

.search-bar {
  margin-bottom: 1rem;
}

.search-input {
  width: 100%;
  padding: 0.5rem;
}

.categories {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.categories button {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
}

.categories button.active {
  background: #eee;
}

.blocks-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
}

.block-item {
  border: 1px solid #ddd;
  padding: 1rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.block-item:hover {
  background: #f5f5f5;
}

.block-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.block-label {
  font-size: 0.9rem;
}
</style>