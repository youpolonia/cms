<template>
  <div class="version-comparison">
    <div class="version-selectors">
      <select v-model="fromVersion">
        <option v-for="version in versions" :value="version.id">
          Version #{{ version.id }} - {{ formatDate(version.created_at) }}
        </option>
      </select>
      <span class="compare-to">compared to</span>
      <select v-model="toVersion">
        <option v-for="version in versions" :value="version.id">
          Version #{{ version.id }} - {{ formatDate(version.created_at) }}
        </option>
      </select>
    </div>

    <div class="diff-viewer">
      <div class="diff-pane" v-html="diffHtml"></div>
    </div>

    <div class="version-actions" v-if="fromVersion && toVersion">
      <button 
        class="btn-restore"
        @click="restoreVersion"
        :disabled="!canRestore">
        Restore Selected Version
      </button>
      <div class="restore-warning" v-if="showRestoreWarning">
        This will overwrite current content. Continue?
        <button @click="confirmRestore">Confirm</button>
        <button @click="cancelRestore">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    contentId: {
      type: Number,
      required: true
    },
    versions: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      fromVersion: null,
      toVersion: null,
      diffHtml: '',
      showRestoreWarning: false,
      selectedForRestore: null
    }
  },
  computed: {
    canRestore() {
      return this.fromVersion && this.toVersion && 
             (this.fromVersion !== this.toVersion);
    }
  },
  methods: {
    formatDate(dateString) {
      return new Date(dateString).toLocaleString();
    },
    async fetchDiff() {
      try {
        const response = await fetch(
          `/api/content/compare-versions?version1_id=${this.fromVersion}&version2_id=${this.toVersion}`
        );
        const data = await response.json();
        this.diffHtml = this.formatDiff(data.diff);
      } catch (error) {
        console.error('Failed to fetch diff:', error);
      }
    },
    formatDiff(diff) {
      // Enhanced diff formatting with line numbers
      const lines = diff.split('\n');
      return lines.map((line, i) => {
        const lineNum = i + 1;
        if (line.startsWith('+')) {
          return `<div class="diff-line"><span class="line-num">${lineNum}</span><span class="added">${line}</span></div>`;
        } else if (line.startsWith('-')) {
          return `<div class="diff-line"><span class="line-num">${lineNum}</span><span class="removed">${line}</span></div>`;
        }
        return `<div class="diff-line"><span class="line-num">${lineNum}</span>${line}</div>`;
      }).join('');
    },
    restoreVersion() {
      this.selectedForRestore = this.fromVersion;
      this.showRestoreWarning = true;
    },
    async confirmRestore() {
      try {
        const response = await fetch(`/api/content/restore-version`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            content_id: this.contentId,
            version_id: this.selectedForRestore
          })
        });

        const result = await response.json();
        if (result.success) {
          this.$emit('version-restored');
          this.showRestoreWarning = false;
        } else {
          throw new Error(result.error || 'Restore failed');
        }
      } catch (error) {
        console.error('Version restore failed:', error);
        alert(`Restore failed: ${error.message}`);
      }
    },
    cancelRestore() {
      this.showRestoreWarning = false;
      }
  },
  watch: {
    fromVersion() {
      if (this.fromVersion && this.toVersion) {
        this.fetchDiff();
      }
    },
    toVersion() {
      if (this.fromVersion && this.toVersion) {
        this.fetchDiff();
      }
    }
  },
  mounted() {
    if (this.versions.length >= 2) {
      this.fromVersion = this.versions[1].id;
      this.toVersion = this.versions[0].id;
    }
  }
}
</script>

<style scoped>
.version-comparison {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.version-selectors {
  display: flex;
  align-items: center;
  gap: 10px;
}

.compare-to {
  color: #666;
  font-size: 0.9em;
}

.diff-viewer {
  border: 1px solid #eee;
  border-radius: 4px;
  padding: 15px;
  background: #f8f8f8;
  max-height: 500px;
  overflow-y: auto;
}

.diff-pane {
  font-family: monospace;
  white-space: pre-wrap;
}

.diff-line {
  display: flex;
  min-height: 1.2em;
  line-height: 1.2;
}

.line-num {
  color: #999;
  min-width: 30px;
  padding-right: 10px;
  text-align: right;
}

.added {
  background-color: #e6ffed;
  color: #22863a;
}

.removed {
  background-color: #ffeef0;
  color: #cb2431;
  text-decoration: line-through;
}

.version-actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
}

.btn-restore {
  padding: 8px 16px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-restore:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.restore-warning {
  padding: 10px;
  background-color: #fff3cd;
  border: 1px solid #ffeeba;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 10px;
}
</style>