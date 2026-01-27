<template>
  <div class="diff-visualizer">
    <div class="view-mode-selector">
      <button 
        @click="mode = 'side-by-side'"
        :class="{ active: mode === 'side-by-side' }"
      >
        Side-by-side
      </button>
      <button 
        @click="mode = 'inline'"
        :class="{ active: mode === 'inline' }"
      >
        Inline
      </button>
    </div>

    <div v-if="mode === 'side-by-side'" class="side-by-side-view">
      <div class="old-version">
        <h3>Old Version</h3>
        <div class="diff-content">
          <div 
            v-for="(line, index) in diffLines" 
            :key="'old-' + index"
            class="diff-line"
            :class="getLineClass(line.type)"
          >
            {{ line.oldLine || ' ' }}
          </div>
        </div>
      </div>
      <div class="new-version">
        <h3>New Version</h3>
        <div class="diff-content">
          <div 
            v-for="(line, index) in diffLines" 
            :key="'new-' + index"
            class="diff-line"
            :class="getLineClass(line.type)"
          >
            {{ line.newLine || ' ' }}
          </div>
        </div>
      </div>
    </div>

    <div v-else class="inline-view">
      <div 
        v-for="(line, index) in diffLines" 
        :key="'inline-' + index"
        class="diff-line"
        :class="getLineClass(line.type)"
      >
        <span v-if="line.type === 'added'" class="change-marker">+</span>
        <span v-else-if="line.type === 'removed'" class="change-marker">-</span>
        <span v-else class="change-marker">&nbsp;</span>
        {{ line.newLine || line.oldLine }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    oldText: String,
    newText: String,
    mode: {
      type: String,
      default: 'side-by-side'
    }
  },
  computed: {
    diffLines() {
      // Process diff using DiffEngine API
      // This would be replaced with actual API call
      return this.simulateDiff(this.oldText, this.newText);
    }
  },
  methods: {
    getLineClass(type) {
      return {
        'added': type === 'added',
        'removed': type === 'removed',
        'modified': type === 'modified',
        'unchanged': type === 'unchanged'
      };
    },
    simulateDiff(oldText, newText) {
      // Simulate diff output for demo purposes
      // In production, this would come from DiffEngine API
      const oldLines = oldText.split('\n');
      const newLines = newText.split('\n');
      
      return [
        { type: 'unchanged', oldLine: oldLines[0], newLine: newLines[0] },
        { type: 'modified', oldLine: oldLines[1], newLine: newLines[1] },
        { type: 'removed', oldLine: oldLines[2], newLine: null },
        { type: 'added', oldLine: null, newLine: newLines[3] }
      ];
    }
  }
}
</script>

<style scoped>
.diff-visualizer {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.view-mode-selector {
  display: flex;
  gap: 0.5rem;
}
.view-mode-selector button {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  background: #f5f5f5;
  cursor: pointer;
}
.view-mode-selector button.active {
  background: #ddd;
}
.side-by-side-view {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.diff-content {
  font-family: monospace;
  white-space: pre;
}
.diff-line {
  padding: 0.25rem;
}
.added {
  background-color: #e6ffed;
}
.removed {
  background-color: #ffeef0;
}
.modified {
  background-color: #fff8c5;
}
.change-marker {
  display: inline-block;
  width: 1rem;
  text-align: center;
  margin-right: 0.5rem;
}
</style>