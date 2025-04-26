<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="space-y-4">
    <draggable 
      v-model="blocks" 
      item-key="id"
      handle=".drag-handle"
      class="space-y-4"
    >
      <template #item="{element, index}">
        <div class="border rounded p-4">
          <div class="flex justify-between items-center mb-2">
            <button class="drag-handle cursor-move px-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <select v-model="element.type" class="form-select">
              <option value="text">Text</option>
              <option value="image">Image</option>
              <option value="video">Video</option>
              <option value="columns">Columns</option>
            </select>
            <button @click="removeBlock(index)" class="text-red-500">
              Remove
            </button>
          </div>
          
          <div v-if="element.type === 'text'" class="mt-2">
            <textarea v-model="element.content" class="form-textarea w-full" rows="4"></textarea>
          </div>
          
          <div v-if="element.type === 'image'" class="mt-2">
            <input type="file" @change="handleImageUpload(index, $event)" class="form-input">
          </div>
        </div>
      </template>
    </draggable>
    
    <div class="flex flex-wrap gap-2 mt-4">
      <button @click="addBlock" class="btn btn-secondary">
        Add Block
      </button>
      <button @click="generateAIContent" class="btn btn-primary">
        AI Generate
      </button>
      <button @click="suggestBlocks" class="btn btn-primary">
        AI Suggest
      </button>
    </div>
    </div>
    
    <PreviewPane :blocks="blocks" />
  </div>
</template>

<script>
import draggable from 'vue-draggable-next'
import PreviewPane from './PreviewPane.vue'

export default {
  components: { draggable, PreviewPane },
  props: {
    initialBlocks: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      blocks: [...this.initialBlocks]
    }
  },
  watch: {
    blocks: {
      deep: true,
      handler() {
        this.$emit('update', this.blocks)
      }
    }
  },
  methods: {
    addBlock() {
      this.blocks.push({
        id: Date.now(),
        type: 'text',
        content: ''
      })
    },
    removeBlock(index) {
      this.blocks.splice(index, 1)
    },
    async generateAIContent() {
      try {
        const response = await axios.post('/api/page-builder/generate-content', {
          prompt: 'Generate content for a new block'
        })
        
        this.blocks.push({
          id: Date.now(),
          type: 'text',
          content: response.data.content
        })
      } catch (error) {
        console.error('AI content generation failed:', error)
        alert('Failed to generate content. Please try again.')
      }
    },
    async suggestBlocks() {
      try {
        const response = await axios.post('/api/page-builder/suggest-blocks', {
          currentBlocks: this.blocks
        })
        
        this.blocks = this.blocks.concat(response.data.suggestions.map(suggestion => ({
          id: Date.now() + Math.random(),
          type: suggestion.type,
          content: suggestion.content
        })))
      } catch (error) {
        console.error('AI block suggestion failed:', error)
        alert('Failed to get block suggestions. Please try again.')
      }
    },
    async handleImageUpload(index, event) {
      const file = event.target.files[0]
      if (file) {
        try {
          const formData = new FormData()
          formData.append('image', file)
          
          const response = await axios.post('/api/page-builder/upload-image', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
          
          this.blocks[index].content = response.data.url
        } catch (error) {
          console.error('Image upload failed:', error)
          alert('Image upload failed. Please try again.')
        }
      }
    }
  }
}
</script>