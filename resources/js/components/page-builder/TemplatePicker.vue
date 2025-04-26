<template>
  <div class="template-picker">
    <div class="category-filter">
      <select v-model="selectedCategory">
        <option value="">All Categories</option>
        <option 
          v-for="category in categories" 
          :key="category.id"
          :value="category.id"
        >
          {{ category.name }}
        </option>
      </select>
    </div>
    
    <div class="template-grid">
      <div 
        class="template-card"
        v-for="template in filteredTemplates"
        :key="template.id"
        @click="$emit('select', template)"
      >
        <div class="template-preview">
          <img :src="template.preview_image" :alt="template.name">
        </div>
        <div class="template-name">{{ template.name }}</div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
  props: {
    templates: {
      type: Array,
      required: true
    },
    categories: {
      type: Array,
      required: true
    }
  },
  setup(props) {
    const selectedCategory = ref('')

    const filteredTemplates = computed(() => {
      if (!selectedCategory.value) return props.templates
      return props.templates.filter(t => 
        t.categories.includes(selectedCategory.value)
      )
    })

    return {
      selectedCategory,
      filteredTemplates
    }
  }
}
</script>

<style scoped>
.template-picker {
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.category-filter {
  margin-bottom: 15px;
}

select {
  padding: 8px 12px;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.template-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 15px;
}

.template-card {
  border: 1px solid #eee;
  border-radius: 4px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s;
}

.template-card:hover {
  transform: translateY(-5px);
}

.template-preview img {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.template-name {
  padding: 10px;
  text-align: center;
  font-weight: 500;
}
</style>