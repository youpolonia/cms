<template>
  <div class="version-history">
    <h3>Version History</h3>
    <div class="version-list">
      <div 
        v-for="version in versions" 
        :key="version.id"
        class="version-item"
        :class="{ 'active': selectedVersion === version.id }"
        @click="selectVersion(version)"
      >
        <div class="version-meta">
          <span class="version-date">{{ formatDate(version.created_at) }}</span>
          <span class="version-author">{{ version.user.name }}</span>
        </div>
        <div class="version-actions">
          <button @click.stop="restoreVersion(version)">Restore</button>
          <button
            @click.stop="selectForComparison(version)"
            :class="{ 'active': selectedForComparison.includes(version.id) }"
          >
            Compare
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { ref } from 'vue'
import { format } from 'date-fns'
import VersionDiffModal from './VersionDiffModal.vue'

export default {
  components: { VersionDiffModal },
  props: {
    blockId: {
      type: Number,
      required: true
    }
  },
  setup(props) {
    const versions = ref([])
    const selectedVersion = ref(null)
    const showDiff = ref(false)
    const selectedForComparison = ref([])

    const loadVersions = async () => {
      const response = await axios.get(`/api/block-versions/${props.blockId}`)
      versions.value = response.data.versions
    }

    const selectVersion = (version) => {
      selectedVersion.value = version.id
      this.$emit('version-selected', version)
    }

    const restoreVersion = async (version) => {
      if (confirm('Restore this version?')) {
        await axios.get(`/api/block-versions/${version.id}/restore`)
        this.$emit('version-restored')
      }
    }

    const selectForComparison = (version) => {
      const index = selectedForComparison.value.indexOf(version.id)
      if (index >= 0) {
        selectedForComparison.value.splice(index, 1)
      } else {
        selectedForComparison.value.push(version.id)
      }

      if (selectedForComparison.value.length === 2) {
        const version1 = versions.value.find(v => v.id === selectedForComparison.value[0])
        const version2 = versions.value.find(v => v.id === selectedForComparison.value[1])
        this.$emit('compare', [version1, version2])
        selectedForComparison.value = []
      }
    }

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy h:mm a')
    }

    return {
      versions,
      selectedVersion,
      showDiff,
      loadVersions,
      selectVersion,
      restoreVersion,
      selectForComparison,
      selectedForComparison,
      formatDate
    }
  }
}
</script>

<style scoped>
.version-history {
  border: 1px solid #eee;
  padding: 1rem;
  border-radius: 4px;
}
.version-list {
  max-height: 400px;
  overflow-y: auto;
}
.version-item {
  padding: 0.5rem;
  border-bottom: 1px solid #eee;
  cursor: pointer;
}
.version-item:hover {
  background: #f5f5f5;
}
.version-item.active {
  background: #e3f2fd;
}
.version-meta {
  display: flex;
  justify-content: space-between;
}
.version-actions {
  margin-top: 0.5rem;
  display: flex;
  gap: 0.5rem;
}
</style>