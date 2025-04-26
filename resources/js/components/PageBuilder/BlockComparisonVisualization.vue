<template>
  <div class="block-comparison">
    <div class="toolbar">
      <button @click="toggleSideBySide" :class="{ active: sideBySide }">
        {{ sideBySide ? 'Unified View' : 'Side-by-Side View' }}
      </button>
      <button @click="toggleSyntaxHighlighting" :class="{ active: syntaxHighlighting }">
        {{ syntaxHighlighting ? 'Plain Text' : 'Syntax Highlighting' }}
      </button>
    </div>

    <div v-if="sideBySide" class="side-by-side">
      <div class="version-a">
        <h3>Version {{ versionA.id }}</h3>
        <pre v-if="syntaxHighlighting" v-html="highlightedA"></pre>
        <pre v-else>{{ versionA.content }}</pre>
      </div>
      <div class="version-b">
        <h3>Version {{ versionB.id }}</h3>
        <pre v-if="syntaxHighlighting" v-html="highlightedB"></pre>
        <pre v-else>{{ versionB.content }}</pre>
      </div>
    </div>

    <div v-else class="unified">
      <pre v-if="syntaxHighlighting" v-html="highlightedDiff"></pre>
      <pre v-else>{{ diffText }}</pre>
    </div>

    <div class="stats">
      <div class="stat">
        <span class="label">Changes:</span>
        <span class="value">{{ stats.changes }}</span>
      </div>
      <div class="stat">
        <span class="label">Similarity:</span>
        <span class="value">{{ stats.similarity }}%</span>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import * as Diff from 'diff'

export default defineComponent({
  name: 'BlockComparisonVisualization',
  props: {
    versionA: { type: Object, required: true },
    versionB: { type: Object, required: true },
    block: { type: Object, required: true }
  },
  data() {
    return {
      sideBySide: false,
      syntaxHighlighting: true,
      stats: {
        changes: 0,
        similarity: 0
      }
    }
  },
  computed: {
    diffText() {
      return Diff.createPatch(
        `Block ${this.block.id}`,
        this.versionA.content,
        this.versionB.content
      )
    },
    highlightedDiff() {
      // Implementation would use highlight.js or similar
      return this.diffText
    },
    highlightedA() {
      return this.versionA.content
    },
    highlightedB() {
      return this.versionB.content
    }
  },
  mounted() {
    this.calculateStats()
  },
  methods: {
    toggleSideBySide() {
      this.sideBySide = !this.sideBySide
    },
    toggleSyntaxHighlighting() {
      this.syntaxHighlighting = !this.syntaxHighlighting
    },
    calculateStats() {
      const diff = Diff.diffLines(this.versionA.content, this.versionB.content)
      const changes = diff.filter(p => p.added || p.removed).length
      const total = diff.reduce((sum, p) => sum + p.count, 0)
      const similarity = Math.round(100 * (1 - changes / total))
      
      this.stats = {
        changes,
        similarity
      }
    }
  }
})
</script>

<style scoped>
.block-comparison {
  padding: 1rem;
  border: 1px solid #eee;
  border-radius: 4px;
  margin-top: 1rem;
}

.toolbar {
  margin-bottom: 1rem;
}

.toolbar button {
  margin-right: 0.5rem;
  padding: 0.25rem 0.5rem;
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 3px;
  cursor: pointer;
}

.toolbar button.active {
  background: #4CAF50;
  color: white;
}

.side-by-side {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.version-a, .version-b {
  padding: 1rem;
  background: #f9f9f9;
  border-radius: 4px;
}

.stats {
  margin-top: 1rem;
  display: flex;
  gap: 1rem;
}

.stat {
  display: flex;
  gap: 0.5rem;
}

.label {
  font-weight: bold;
}
</style>