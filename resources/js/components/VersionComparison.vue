<template>
  <div class="version-comparison">
    <div class="toolbar">
      <button @click="toggleViewMode">
        {{ viewMode === 'side-by-side' ? 'Unified View' : 'Side-by-Side View' }}
      </button>
      <button @click="toggleLineNumbers">
        {{ showLineNumbers ? 'Hide Line Numbers' : 'Show Line Numbers' }}
      </button>
      <button @click="toggleSemanticView" v-if="apiData?.semantic_groups">
        {{ useSemanticView ? 'Standard View' : 'Semantic View' }}
      </button>
    </div>

    <div v-if="viewMode === 'side-by-side'" class="diff-container side-by-side">
      <div class="version old">
        <h3>
          Old Version
          <span v-if="isOldAutosave" class="autosave-badge">Autosave</span>
        </h3>
        <div class="content" v-html="formattedOldContent"></div>
      </div>
      <div class="version new">
        <h3>
          New Version
          <span v-if="isNewAutosave" class="autosave-badge">Autosave</span>
        </h3>
        <button
          v-if="canRestore && viewMode === 'side-by-side'"
          @click="handleRestore"
          class="restore-button"
        >
          Restore This Version
        </button>
        <div class="content" v-html="formattedNewContent"></div>
      </div>
    </div>

    <div v-else class="diff-container unified">
      <div class="content" v-html="formattedUnifiedContent"></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    oldContent: {
      type: String,
      required: true
    },
    newContent: {
      type: String,
      required: true
    },
    isOldAutosave: {
      type: Boolean,
      default: false
    },
    isNewAutosave: {
      type: Boolean,
      default: false
    },
    canRestore: {
      type: Boolean,
      default: false
    },
    contentId: {
      type: Number,
      default: null
    },
    versionId: {
      type: Number,
      default: null
    }
  },

  data() {
    return {
      viewMode: 'side-by-side',
      showLineNumbers: true,
      useSemanticView: false,
      apiData: null,
      diffData: null
    }
  },

  created() {
    this.fetchDiffData();
  },

  computed: {
    formattedOldContent() {
      if (this.useSemanticView && this.apiData?.semantic_html) {
        return this.formatSemanticDiff(this.oldContent, 'old');
      }
      return this.formatDiff(this.oldContent, 'old');
    },
    formattedNewContent() {
      if (this.useSemanticView && this.apiData?.semantic_html) {
        return this.formatSemanticDiff(this.newContent, 'new');
      }
      return this.formatDiff(this.newContent, 'new');
    },
    formattedUnifiedContent() {
      if (this.useSemanticView && this.apiData?.semantic_html) {
        return this.apiData.semantic_html;
      }
      const diffs = this.dmp.diff_main(this.oldContent, this.newContent);
      this.dmp.diff_cleanupSemantic(diffs);
      return this.formatUnifiedDiff(diffs);
    }
  },

  methods: {
    async fetchDiffData() {
      try {
        const response = await fetch('/api/content/versions/compare', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            version_id: this.versionId,
            compared_version_id: this.contentId
          })
        });
        this.diffData = await response.json();
      } catch (error) {
        console.error('Failed to fetch diff data:', error);
      }
    },

    formatSemanticDiff(diff) {
      if (!this.diffData?.diff) return '';
      
      return Object.entries(this.diffData.diff).map(([key, change]) => {
        switch (change.type) {
          case 'added':
            return `<div class="added">${key}: ${change.value}</div>`;
          case 'removed':
            return `<div class="removed">${key}: ${change.value}</div>`;
          case 'changed':
            return `<div class="changed">
              <div class="old">${key}: ${change.old}</div>
              <div class="new">${key}: ${change.new}</div>
            </div>`;
          default:
            return '';
        }
      }).join('');
    },

    toggleSemanticView() {
      this.useSemanticView = !this.useSemanticView;
    },

    toggleViewMode() {
      this.viewMode = this.viewMode === 'side-by-side' ? 'unified' : 'side-by-side';
    },
    toggleLineNumbers() {
      this.showLineNumbers = !this.showLineNumbers;
    },
    formatDiff(content, version) {
      // Format content with line numbers and highlighting
      return content.split('\n')
        .map((line, i) => `<div class="line">${this.showLineNumbers ? `<span class="line-number">${i + 1}</span>` : ''}${line}</div>`)
        .join('');
    },
    formatUnifiedDiff(diffs) {
      // Format unified diff with color coding
      return diffs.map(diff => {
        const [type, text] = diff;
        const lines = text.split('\n');
        return lines.map(line => {
          if (!line) return '';
          const cls = type === -1 ? 'deleted' : type === 1 ? 'added' : '';
          return `<div class="line ${cls}">${line}</div>`;
        }).join('');
      }).join('');
    },

    formatSemanticDiff(content, version) {
      if (!this.diffData?.diff) return content;
      
      return Object.entries(this.diffData.diff).map(([key, change]) => {
        const lineNum = this.showLineNumbers ? `<span class="line-number">${key}</span>` : '';
        
        switch (change.type) {
          case 'added':
            return `<div class="line added">${lineNum}${change.value}</div>`;
          case 'removed':
            return `<div class="line removed">${lineNum}${change.value}</div>`;
          case 'changed':
            return `<div class="line changed">
              ${lineNum}
              <div class="old">${change.old}</div>
              <div class="new">${change.new}</div>
            </div>`;
          default:
            return `<div class="line">${lineNum}${content}</div>`;
        }
      }).join('');
    }
  },
  handleRestore() {
    if (confirm('Are you sure you want to restore this version?')) {
      this.$emit('restore-version', {
        contentId: this.contentId,
        versionId: this.versionId
      });
    }
  }
}
</script>

<style scoped lang="scss">
.version-comparison {
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}

.toolbar {
  padding: 8px;
  background: #f5f5f5;
  border-bottom: 1px solid #ddd;
}

.diff-container {
  display: flex;
}

.side-by-side {
  display: flex;
}

.side-by-side .version {
  flex: 1;
  padding: 8px;
  overflow-x: auto;
}

.side-by-side .version + .version {
  border-left: 1px solid #ddd;
}

.unified .content {
  padding: 8px;
  width: 100%;
}

.line {
  font-family: monospace;
  white-space: pre;
  padding: 2px 4px;
}

.line-number {
  color: #999;
  margin-right: 8px;
  display: inline-block;
  width: 30px;
  text-align: right;
}

.restore-button {
  margin-left: 8px;
  background-color: #4CAF50;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  
  &:hover {
    background-color: #45a049;
  }
}

.added {
  background-color: #e6ffed;
  border-left: 3px solid #2ecc71;
  padding-left: 8px;
}

.deleted {
  background-color: #ffeef0;
  text-decoration: line-through;
  border-left: 3px solid #e74c3c;
  padding-left: 8px;
}

.changed {
  background-color: #fff9e6;
  border-left: 3px solid #f39c12;
  padding-left: 8px;
  margin: 4px 0;
}

.changed .old {
  color: #e74c3c;
  text-decoration: line-through;
}

.changed .new {
  color: #2ecc71;
  font-weight: 500;
}

.content-change {
  font-weight: bold;
}

.formatting-change {
  opacity: 0.7;
}

.semantic-group {
  border-radius: 2px;
  padding: 0 2px;
  margin: 0 -2px;
}

.semantic-group.added.content-change {
  background-color: #c6e7ff;
}

.semantic-group.deleted.content-change {
  background-color: #ffd6d6;
}

.semantic-group.added.formatting-change {
  background-color: #e6ffed;
}

.semantic-group.deleted.formatting-change {
  background-color: #ffeef0;
}

.line-number {
  color: #7f8c8d;
  font-weight: normal;
  user-select: none;
}

.autosave-badge {
  font-size: 0.8rem;
  background-color: #e0f2fe;
  color: #0369a1;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: 8px;
}
</style>