<template>
  <div class="component-library">
    <div class="search-container">
      <input 
        v-model="searchQuery" 
        placeholder="Search components..."
        class="search-input"
      />
    </div>

    <div class="categories">
      <button
        v-for="category in filteredCategories"
        :key="category.id"
        @click="activeCategory = category.id"
        :class="{ active: activeCategory === category.id }"
      >
        {{ category.name }}
      </button>
    </div>

    <div class="component-grid">
      <div
        v-for="component in filteredComponents"
        :key="component.id"
        class="component-card"
        draggable="true"
        @dragstart="handleDragStart($event, component)"
      >
        <div class="component-icon">
          <icon :name="component.icon" />
        </div>
        <h4>{{ component.name }}</h4>
        <p>{{ component.description }}</p>
        
        <div class="component-tags">
          <span 
            v-for="tag in component.tags"
            :key="tag"
            class="tag"
          >
            {{ tag }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, ref } from 'vue'
import icon from './Icon.vue'

export default {
  components: { icon },
  
  setup() {
    const searchQuery = ref('')
    const activeCategory = ref('all')
    
    // Mock data - should be replaced with real API calls
    const categories = ref([
      { id: 'all', name: 'All Components' },
      { id: 'layout', name: 'Layout' },
      { id: 'content', name: 'Content' },
      { id: 'media', name: 'Media' },
      { id: 'forms', name: 'Forms' },
      { id: 'navigation', name: 'Navigation' }
    ])

    const components = ref([
      {
        id: 1,
        name: 'Hero Banner',
        category: 'content',
        icon: 'image',
        description: 'Full-width image with text overlay',
        tags: ['content', 'image']
      },
      // More components would go here...
    ])

    const filteredCategories = computed(() => [      
      { id: 'all', name: 'All' },
      ...categories.value
    ])

    const filteredComponents = computed(() => {
      let filtered = components.value 

      // Filter by search query
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(comp => 
          comp.name.toLowerCase().includes(query) ||
          comp.description.toLowerCase().includes(query) ||
          comp.tags.some(tag => tag.toLowerCase().includes(query))
        )
      }

      // Filter by category
      if (activeCategory.value !== 'all') {
        filtered = filtered.filter(
          comp => comp.category === activeCategory.value
        )
      }

      return filtered
    })

    const handleDragStart = (event, component) => {
      event.dataTransfer.setData(
        'application/json',
        JSON.stringify(component)
      )
      event.dataTransfer.effectAllowed = 'copy'
    }

    return {
      searchQuery,
      activeCategory,
      categories,
      components,
      filteredCategories,
      filteredComponents,
      handleDragStart
    }
  }
}
</script>

<style scoped>
.component-library {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 1rem;
}

.search-container {
  margin-bottom: 1rem;
}

.search-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.categories {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  overflow-x: auto;
}

.categories button {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 20px;
  background: white;
  cursor: pointer;
}

.categories button.active {
  background: #0066cc;
  color: white;
  border-color: #0066cc;
}

.component-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  overflow-y: auto;
}

.component-card {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 1rem;
  cursor: grab;
  transition: all 0.2s;
}

.component-card:hover {
  border-color: #0066cc;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.component-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.component-tags {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.tag {
  font-size: 0.75rem;
  padding: 0.2rem 0.4rem;
  background: #f0f0f0;
  border-radius: 4px;
}
</style>