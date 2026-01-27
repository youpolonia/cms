<template>
  <div class="version-comparison">
    <div class="version-selectors">
      <VersionSelector 
        label="Old Version"
        :versions="availableVersions"
        v-model="oldVersion"
      />
      <VersionSelector 
        label="New Version"
        :versions="availableVersions"
        v-model="newVersion"
      />
    </div>

    <div class="diff-view">
      <DiffVisualizer 
        :oldText="oldText"
        :newText="newText"
        mode="side-by-side"
      />
    </div>

    <RestorationDialog 
      v-if="showRestoreDialog"
      :version="restoreVersion"
      @confirm="handleRestore"
      @cancel="showRestoreDialog = false"
    />
  </div>
</template>

<script>
export default {
  data() {
    return {
      availableVersions: [],
      oldVersion: null,
      newVersion: null,
      oldText: '',
      newText: '',
      showRestoreDialog: false,
      restoreVersion: null
    }
  },
  methods: {
    async fetchVersions() {
      const response = await fetch('/api/analytics/versions');
      this.availableVersions = await response.json();
    },
    async compareVersions() {
      const response = await fetch(`/api/analytics/compare?old=${this.oldVersion}&new=${this.newVersion}`);
      const data = await response.json();
      this.oldText = data.oldText;
      this.oldText = data.newText;
    },
    promptRestore(version) {
      this.restoreVersion = version;
      this.showRestoreDialog = true;
    },
    async handleRestore() {
      await fetch('/api/analytics/restore', {
        method: 'POST',
        body: JSON.stringify({ version: this.restoreVersion })
      });
      this.showRestoreDialog = false;
    }
  },
  mounted() {
    this.fetchVersions();
  }
}
</script>

<style scoped>
.version-comparison {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.version-selectors {
  display: flex;
  gap: 1rem;
}
.diff-view {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 1rem;
}
</style>