<template>
  <div class="diff-container">
    <div class="diff-header">
      <h3>Comparing Version {{ version1.version_number }} and Version {{ version2.version_number }}</h3>
      <div class="diff-stats">
        <span>Similarity: {{ similarity_score }}%</span>
        <span>Changed {{ granularity }}s: {{ stats.changed }} of {{ stats.total }} ({{ stats.change_percentage }}%)</span>
      </div>
    </div>

    <div class="diff-view" v-if="highlight_changes">
      <div class="diff-side-by-side">
        <div class="diff-pane">
          <h4>Version {{ version1.version_number }}</h4>
          <div class="diff-content" v-html="renderedOldContent"></div>
        </div>
        <div class="diff-pane">
          <h4>Version {{ version2.version_number }}</h4>
          <div class="diff-content" v-html="renderedNewContent"></div>
        </div>
      </div>
    </div>

    <div class="diff-changes" v-else>
      <div v-for="(change, index) in changes" :key="index" class="change-item" :class="change.type">
        <div class="change-meta">
          <span class="change-type">{{ change.type }}</span>
          <span class="change-position" v-if="granularity === 'line'">Line {{ change.line + 1 }}</span>
          <span class="change-position" v-else>Position {{ change.position }}</span>
        </div>
        <div class="change-content" v-if="change.type === 'modified'">
          <div class="old-content">{{ change.old }}</div>
          <div class="new-content">{{ change.new }}</div>
        </div>
        <div class="change-content" v-else>
          {{ change.content || change.word }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    comparisonData: {
      type: Object,
      required: true
    },
    highlight_changes: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    version1() {
      return this.comparisonData.metadata?.version1 || {};
    },
    version2() {
      return this.comparisonData.metadata?.version2 || {};
    },
    granularity() {
      return this.comparisonData.granularity;
    },
    similarity_score() {
      return this.comparisonData.similarity_score;
    },
    stats() {
      return this.comparisonData.stats;
    },
    changes() {
      return this.comparisonData.diff.changes;
    },
    renderedOldContent() {
      if (this.granularity === 'line') {
        return this.highlightLines(this.comparisonData.diff.old, 'removed');
      }
      return this.highlightWords(this.comparisonData.diff.old, 'removed');
    },
    renderedNewContent() {
      if (this.granularity === 'line') {
        return this.highlightLines(this.comparisonData.diff.new, 'added');
      }
      return this.highlightWords(this.comparisonData.diff.new, 'added');
    }
  },
  methods: {
    highlightLines(lines, changeType) {
      if (!lines) return '';
      return lines.map((line, i) => {
        const change = this.changes.find(c => c.line === i && c.type === changeType);
        if (change) {
          return `<div class="highlight-${changeType}">${line}</div>`;
        }
        return `<div>${line}</div>`;
      }).join('');
    },
    highlightWords(words, changeType) {
      if (!words) return '';
      return words.map((word, i) => {
        const change = this.changes.find(c => c.position === i && c.type === changeType);
        if (change) {
          return `<span class="highlight-${changeType}">${word}</span>`;
        }
        return word;
      }).join(' ');
    }
  }
}
</script>

<style scoped>
.diff-container {
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.diff-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.diff-side-by-side {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.diff-pane {
  border: 1px solid #e2e8f0;
  border-radius: 0.25rem;
  padding: 0.5rem;
}

.diff-content {
  white-space: pre-wrap;
}

.change-item {
  padding: 0.5rem;
  margin-bottom: 0.5rem;
  border-radius: 0.25rem;
}

.change-item.added {
  background-color: #f0fff4;
  border-left: 4px solid #48bb78;
}

.change-item.removed {
  background-color: #fff5f5;
  border-left: 4px solid #f56565;
}

.change-item.modified {
  background-color: #ebf8ff;
  border-left: 4px solid #4299e1;
}

.change-meta {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
  color: #718096;
}

.change-type {
  text-transform: capitalize;
  font-weight: 600;
}

.highlight-added {
  background-color: #c6f6d5;
}

.highlight-removed {
  background-color: #fed7d7;
  text-decoration: line-through;
}
</style>