<template>
  <div class="version-selector">
    <label>{{ label }}</label>
    <select v-model="selectedVersion" @change="onChange">
      <option value="" disabled>Select a version</option>
      <option 
        v-for="version in versions" 
        :key="version.id"
        :value="version.id"
      >
        Version {{ version.version_number }} - {{ formatDate(version.created_at) }}
      </option>
    </select>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: [String, Number],
      default: null
    },
    contentId: {
      type: [String, Number],
      required: true
    },
    label: {
      type: String,
      default: 'Version'
    }
  },
  data() {
    return {
      versions: [],
      loading: false,
      error: null
    }
  },
  computed: {
    selectedVersion: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      }
    }
  },
  mounted() {
    this.fetchVersions()
  },
  methods: {
    async fetchVersions() {
      this.loading = true
      this.error = null

      try {
        const response = await this.$axios.get(
          `/api/content/${this.contentId}/versions`
        )
        this.versions = response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load versions'
      } finally {
        this.loading = false
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    },
    onChange() {
      this.$emit('change', this.selectedVersion)
    }
  }
}
</script>

<style scoped>
.version-selector {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

label {
  font-weight: 600;
  color: #4a5568;
}

select {
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.25rem;
  background-color: white;
  font-size: 1rem;
}

select:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 1px #4299e1;
}
</style>
