<template>
  <modal :show="modelValue" @close="$emit('update:modelValue', false)">
    <template #header>
      <h3 class="text-lg font-medium">Select Content</h3>
    </template>

    <div class="space-y-4">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search content..."
          class="w-full pl-10 pr-4 py-2 border rounded"
        />
        <icon name="search" class="absolute left-3 top-3 text-gray-400" />
      </div>

      <div v-if="loading" class="text-center py-8">
        <spinner />
      </div>

      <div v-else class="max-h-96 overflow-y-auto">
        <div
          v-for="content in filteredContents"
          :key="content.id"
          class="p-3 hover:bg-gray-50 cursor-pointer"
          @click="selectContent(content)"
        >
          {{ content.title }}
        </div>
      </div>
    </div>

    <template #footer>
      <button @click="$emit('update:modelValue', false)" class="btn btn-secondary">
        Cancel
      </button>
    </template>
  </modal>
</template>

<script>
export default {
  props: {
    modelValue: {
      type: Boolean,
      required: true
    }
  },

  emits: ['update:modelValue', 'selected'],

  data() {
    return {
      loading: false,
      contents: [],
      searchQuery: ''
    }
  },

  computed: {
    filteredContents() {
      return this.contents.filter(content =>
        content.title.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    }
  },

  watch: {
    modelValue(val) {
      if (val) {
        this.fetchContents()
      }
    }
  },

  methods: {
    async fetchContents() {
      this.loading = true
      try {
        const response = await axios.get('/api/contents')
        this.contents = response.data
      } finally {
        this.loading = false
      }
    },

    selectContent(content) {
      this.$emit('selected', content)
      this.$emit('update:modelValue', false)
    }
  }
}
</script>