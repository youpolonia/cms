<template>
  <div class="version-comparison">
    <div class="comparison-controls">
      <div class="version-selectors">
        <VersionSelector 
          v-model="version1Id"
          :content-id="contentId"
          label="Version 1"
        />
        <VersionSelector 
          v-model="version2Id" 
          :content-id="contentId"
          label="Version 2"
        />
      </div>

      <div class="comparison-options">
        <label>
          <input type="checkbox" v-model="highlightChanges"> Highlight Changes
        </label>
        <label>
          <input type="checkbox" v-model="includeMetadata"> Include Metadata
        </label>
        <select v-model="granularity">
          <option value="line">Line-by-line</option>
          <option value="word">Word-by-word</option>
        </select>
        <button @click="compareVersions">Compare</button>
      </div>
    </div>

    <div v-if="loading" class="loading">
      Loading comparison...
    </div>

    <div v-if="error" class="error">
      {{ error }}
    </div>

    <DiffVisualizer 
      v-if="comparisonData"
      :comparison-data="comparisonData"
      :highlight-changes="highlightChanges"
    />
  </div>
</template>

<script>
import DiffVisualizer from '@/components/VersionComparison/DiffVisualizer.vue'
import VersionSelector from '@/components/VersionComparison/VersionSelector.vue'

export default {
  components: {
    DiffVisualizer,
    VersionSelector
  },
  props: {
    contentId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      version1Id: null,
      version2Id: null,
      highlightChanges: true,
      includeMetadata: true,
      granularity: 'line',
      comparisonData: null,
      loading: false,
      error: null
    }
  },
  methods: {
    async compareVersions() {
      if (!this.version1Id || !this.version2Id) {
        this.error = 'Please select both versions to compare'
        return
      }

      this.loading = true
      this.error = null

      try {
        const response = await this.$axios.get(
          `/api/content/${this.contentId}/versions/compare`, {
            params: {
              version1_id: this.version1Id,
              version2_id: this.version2Id,
              granularity: this.granularity,
              include_metadata: this.includeMetadata,
              highlight_changes: this.highlightChanges
            }
          }
        )

        this.comparisonData = response.data.data
        this.trackComparison()
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to compare versions'
      } finally {
        this.loading = false
      }
    },
    trackComparison() {
      this.$analytics.track('Version Comparison', {
        content_id: this.contentId,
        version1_id: this.version1Id,
        version2_id: this.version2Id,
        granularity: this.granularity
      })
    }
  }
}
</script>

<style scoped>
.version-comparison {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem;
}

.comparison-controls {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 0.5rem;
}

.version-selectors {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.comparison-options {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.loading, .error {
  padding: 1rem;
  text-align: center;
  margin: 1rem 0;
  border-radius: 0.25rem;
}

.loading {
  background: #ebf8ff;
  color: #3182ce;
}

.error {
  background: #fff5f5;
  color: #e53e3e;
}
</style>