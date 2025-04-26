<template>
  <ErrorBoundary>
    <!-- Template content remains unchanged -->
  </ErrorBoundary>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import * as Diff from 'diff';
import ErrorBoundary from './ErrorBoundary.vue';

interface DiffPart {
  value: string;
  added?: boolean;
  removed?: boolean;
  charDiffs?: DiffPart[];
}

interface Version {
  id: string;
  content: string;
  created_at: string;
}

interface Content {
  id: string;
}

interface ComparisonStats {
  linesChanged: number;
  wordsChanged: number;
  similarity: number;
  significantChanges: number;
  changeRate: number;
  timeBetween: number;
}

export default defineComponent({
  name: 'ComparisonVisualization',
  components: {
    ErrorBoundary
  },
  props: {
    versionA: { type: Object as () => Version, required: true },
    versionB: { type: Object as () => Version, required: true },
    content: { type: Object as () => Content, required: true }
  },
  setup() {
    const comparisonTracking = inject('comparisonTracking');
    return { comparisonTracking };
  },
  data() {
    return {
      sideBySide: false,
      syntaxHighlighting: true,
      lineDiffs: [] as DiffPart[],
      stats: {
        linesChanged: 0,
        wordsChanged: 0,
        similarity: 0,
        significantChanges: 0,
        changeRate: 0,
        timeBetween: 0
      } as ComparisonStats
    };
  },
  computed: {
    significantChanges(): number {
      return this.lineDiffs.filter(d => d.added || d.removed).length;
    },
    changeRate(): number {
      return this.significantChanges / Math.max(this.lineDiffs.length, 1);
    },
    timeBetweenVersions(): number {
      return new Date(this.versionB.created_at).getTime() - 
             new Date(this.versionA.created_at).getTime();
    }
  },
  mounted() {
    this.calculateLineDiffs();
    this.trackComparison();
  },
  methods: {
    toggleSideBySide() {
      this.sideBySide = !this.sideBySide;
    },
    toggleSyntaxHighlighting() {
      this.syntaxHighlighting = !this.syntaxHighlighting;
    },
    showAnalytics() {
      this.$emit('toggle-analytics');
    },
    startRollback() {
      this.$emit('start-rollback', {
        contentId: this.content.id,
        versionId: this.versionB.id
      });
    },
    calculateLineDiffs() {
      const lineDiff = Diff.diffLines(
        this.versionA.content,
        this.versionB.content,
        { ignoreWhitespace: true }
      );
      
      this.lineDiffs = lineDiff.map(part => {
        if (part.added || part.removed) {
          const charDiffs = Diff.diffChars(
            part.value,
            part.value,
            { ignoreCase: true }
          );
          return {
            ...part,
            charDiffs
          };
        }
        return part;
      });
    },
    getLineClass(part: DiffPart) {
      return {
        'diff-added': part.added,
        'diff-removed': part.removed,
        'diff-unchanged': !part.added && !part.removed
      };
    },
    getCharClass(charPart: DiffPart) {
      return {
        'char-added': charPart.added,
        'char-removed': charPart.removed,
        'char-unchanged': !charPart.added && !charPart.removed
      };
    },
    trackComparison() {
      const stats = {
        linesChanged: this.stats.linesChanged,
        wordsChanged: this.stats.wordsChanged,
        similarity: this.stats.similarity,
        significantChanges: this.significantChanges,
        changeRate: this.changeRate,
        timeBetween: this.timeBetweenVersions
      };
      
      this.comparisonTracking?.recordComparison(
        this.$page.props.auth.user,
        this.content,
        this.versionA,
        this.versionB,
        stats
      ).catch((error: Error) => {
        console.error('Failed to track comparison:', error);
      });
    }
  }
});
</script>

<style scoped>
/* Style definitions remain unchanged */
</style>
