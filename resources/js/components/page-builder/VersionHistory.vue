<template>
  <div class="version-history">
    <h3>Version History</h3>
    <div class="versions">
      <div 
        v-for="version in versions" 
        :key="version.id"
        class="version"
        :class="{ active: version.id === currentVersion }"
        @click="restoreVersion(version)"
      >
        <div class="version-meta">
          <div class="version-name">{{ version.name }}</div>
          <div class="version-date">{{ formatDate(version.created_at) }}</div>
        </div>
        <div class="version-actions">
          <button @click.stop="deleteVersion(version.id)">Delete</button>
        </div>
      </div>
    </div>
    <button @click="createSnapshot">Create Snapshot</button>
  </div>
</template>

<script>
export default {
  props: {
    pageId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      versions: [],
      currentVersion: null
    }
  },
  methods: {
    async loadVersions() {
      const response = await axios.get(`/api/pages/${this.pageId}/versions`)
      this.versions = response.data
    },
    async createSnapshot() {
      const name = prompt('Enter snapshot name:')
      if (name) {
        await axios.post(`/api/pages/${this.pageId}/versions`, { name })
        await this.loadVersions()
      }
    },
    async restoreVersion(version) {
      this.currentVersion = version.id
      this.$emit('restore', version.data)
    },
    async deleteVersion(id) {
      if (confirm('Delete this version?')) {
        await axios.delete(`/api/pages/${this.pageId}/versions/${id}`)
        await this.loadVersions()
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString()
    }
  },
  mounted() {
    this.loadVersions()
  }
}
</script>

<style scoped>
.version-history {
  background: white;
  padding: 15px;
  border-radius: 5px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.version {
  padding: 10px;
  margin: 5px 0;
  border: 1px solid #eee;
  border-radius: 4px;
  display: flex;
  justify-content: space-between;
  cursor: pointer;
}

.version.active {
  border-color: #007bff;
  background: #f0f7ff;
}

.version:hover {
  background: #f9f9f9;
}

.version-meta {
  flex: 1;
}

.version-name {
  font-weight: bold;
}

.version-date {
  font-size: 12px;
  color: #666;
}

.version-actions button {
  background: none;
  border: none;
  color: #dc3545;
  cursor: pointer;
}
</style>