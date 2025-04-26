<template>
  <div class="version-control">
    <h3>Version History</h3>
    <div class="version-list">
      <div v-for="(version, index) in versions" 
           :key="version.id"
           class="version-item"
           :class="{ 'current': version.id === currentVersion }"
           @click="loadVersion(version.id)">
        <div class="version-meta">
          <span class="version-number">Version {{ versions.length - index }}</span>
          <span class="version-date">{{ formatDate(version.created_at) }}</span>
          <span class="version-author" v-if="version.author">by {{ version.author.name }}</span>
        </div>
        <div class="version-actions">
          <button v-if="version.id !== currentVersion" 
                  @click.stop="restoreVersion(version.id)">
            Restore
          </button>
          <button @click.stop="showDiff(version.id)">
            Compare
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDiffModal" class="diff-modal">
      <div class="modal-content">
        <h4>Comparing versions</h4>
        <div class="diff-viewer">
          <div class="diff-block" v-for="diff in diffs" :key="diff.blockId">
            <h5>Block {{ diff.blockId }}</h5>
            <pre>{{ diff.changes }}</pre>
          </div>
        </div>
        <button @click="showDiffModal = false">Close</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    pageId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      versions: [],
      currentVersion: null,
      showDiffModal: false,
      diffs: []
    }
  },
  async mounted() {
    await this.loadVersions();
  },
  methods: {
    async loadVersions() {
      const response = await axios.get(`/api/pages/${this.pageId}/versions`);
      this.versions = response.data.versions;
      this.currentVersion = response.data.current_version;
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    },
    async loadVersion(versionId) {
      const response = await axios.get(`/api/pages/${this.pageId}/versions/${versionId}`);
      this.$emit('version-loaded', response.data.blocks);
      this.currentVersion = versionId;
    },
    async restoreVersion(versionId) {
      await axios.post(`/api/pages/${this.pageId}/versions/${versionId}/restore`);
      this.loadVersions();
      this.$emit('version-restored');
    },
    async showDiff(versionId) {
      const response = await axios.get(`/api/pages/${this.pageId}/versions/${versionId}/diff`);
      this.diffs = response.data.diffs;
      this.showDiffModal = true;
    },
    createSnapshot() {
      axios.post(`/api/pages/${this.pageId}/versions`, {
        comment: 'Autosave'
      });
    }
  }
}
</script>

<style scoped>
.version-control {
  border-top: 1px solid #eee;
  margin-top: 20px;
  padding-top: 20px;
}
.version-list {
  max-height: 300px;
  overflow-y: auto;
}
.version-item {
  padding: 10px;
  border: 1px solid #ddd;
  margin-bottom: 5px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
}
.version-item.current {
  background-color: #f0f8ff;
}
.version-meta {
  display: flex;
  gap: 10px;
}
.version-number {
  font-weight: bold;
}
.version-actions {
  display: flex;
  gap: 5px;
}
.diff-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  padding: 20px;
  max-width: 800px;
  max-height: 80vh;
  overflow: auto;
}
.diff-viewer {
  margin: 15px 0;
}
.diff-block {
  margin-bottom: 15px;
  padding: 10px;
  border: 1px solid #eee;
}
</style>