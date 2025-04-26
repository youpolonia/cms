<template>
  <div class="category-content-manager">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-medium">Manage Category Contents</h3>
      <button @click="showBulkModal = true" class="btn btn-secondary">
        Bulk Operations
      </button>
    </div>

    <div v-if="loading" class="text-center py-8">
      <spinner />
    </div>

    <draggable 
      v-model="contents"
      group="contents"
      @end="onReorder"
      class="space-y-2"
    >
      <div 
        v-for="content in contents"
        :key="content.id"
        class="flex items-center p-3 bg-white rounded shadow"
      >
        <div class="flex-grow">
          {{ content.title }}
        </div>
        <button 
          @click="removeContent(content)"
          class="text-red-500 hover:text-red-700"
        >
          <icon name="trash" />
        </button>
      </div>
    </draggable>

    <div class="mt-4">
      <select-content-modal 
        v-model="showAddModal"
        @selected="addContent"
      />
    </div>

    <bulk-content-modal 
      v-model="showBulkModal"
      @add="bulkAdd"
      @remove="bulkRemove"
    />
  </div>
</template>

<script>
import draggable from 'vuedraggable'
import SelectContentModal from './SelectContentModal.vue'
import BulkContentModal from './BulkContentModal.vue'

export default {
  components: { draggable, SelectContentModal, BulkContentModal },

  props: {
    categoryId: {
      type: Number,
      required: true
    }
  },

  data() {
    return {
      loading: false,
      contents: [],
      showAddModal: false,
      showBulkModal: false
    }
  },

  async mounted() {
    await this.fetchContents()
  },

  methods: {
    async fetchContents() {
      this.loading = true
      try {
        const response = await axios.get(`/api/categories/${this.categoryId}/contents`)
        this.contents = response.data
      } finally {
        this.loading = false
      }
    },

    async addContent(content) {
      try {
        await axios.post(`/api/categories/${this.categoryId}/contents/add`, {
          content_id: content.id
        })
        await this.fetchContents()
      } catch (error) {
        this.$toast.error(error.response.data.message)
      }
    },

    async removeContent(content) {
      try {
        await axios.post(`/api/categories/${this.categoryId}/contents/remove`, {
          content_id: content.id
        })
        await this.fetchContents()
      } catch (error) {
        this.$toast.error(error.response.data.message)
      }
    },

    async onReorder() {
      try {
        await axios.post(`/api/categories/${this.categoryId}/contents/reorder`, {
          content_ids: this.contents.map(c => c.id)
        })
      } catch (error) {
        this.$toast.error('Failed to reorder contents')
        await this.fetchContents() // Reset to server state
      }
    },

    async bulkAdd(contents) {
      try {
        await axios.post(`/api/categories/${this.categoryId}/contents/bulk`, {
          add: contents.map(c => c.id)
        })
        await this.fetchContents()
      } catch (error) {
        this.$toast.error(error.response.data.message)
      }
    },

    async bulkRemove(contents) {
      try {
        await axios.post(`/api/categories/${this.categoryId}/contents/bulk`, {
          remove: contents.map(c => c.id)
        })
        await this.fetchContents()
      } catch (error) {
        this.$toast.error(error.response.data.message)
      }
    }
  }
}
</script>