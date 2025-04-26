<template>
  <div class="modal-overlay" @click.self="close">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Version Comparison</h3>
        <button @click="close" class="close-button">×</button>
      </div>

      <div class="version-info">
        <div class="version">
          <h4>Version 1</h4>
          <div class="version-meta">
            <span>{{ formatDate(version1.created_at) }}</span>
            <span>{{ version1.user.name }}</span>
          </div>
        </div>
        <div class="version">
          <h4>Version 2</h4>
          <div class="version-meta">
            <span>{{ formatDate(version2.created_at) }}</span>
            <span>{{ version2.user.name }}</span>
          </div>
        </div>
      </div>

      <div class="diff-container">
        <div class="diff-section" v-for="(diff, field) in diffs" :key="field">
          <h5>{{ field }}</h5>
          <div class="diff-content">
            <div class="diff-old" v-if="diff.removed">
              <span class="diff-label">Removed:</span>
              <pre>{{ diff.removed }}</pre>
            </div>
            <div class="diff-new" v-if="diff.added">
              <span class="diff-label">Added:</span>
              <pre>{{ diff.added }}</pre>
            </div>
            <div class="diff-changed" v-if="diff.changed">
              <span class="diff-label">Changed:</span>
              <pre>{{ diff.changed }}</pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { format } from 'date-fns'

export default {
  props: {
    version1: {
      type: Object,
      required: true
    },
    version2: {
      type: Object,
      required: true
    }
  },
  emits: ['close'],
  setup(props) {
    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy h:mm a')
    }

    const diffs = computed(() => {
      // Simple diff implementation - could be enhanced with a proper diff library
      const diffResult = {}
      const v1 = props.version1.content
      const v2 = props.version2.content

      for (const key in v1) {
        if (!v2[key]) {
          diffResult[key] = { removed: v1[key] }
        } else if (JSON.stringify(v1[key]) !== JSON.stringify(v2[key])) {
          diffResult[key] = { 
            changed: `From: ${JSON.stringify(v1[key])}\nTo: ${JSON.stringify(v2[key])}`
          }
        }
      }

      for (const key in v2) {
        if (!v1[key]) {
          diffResult[key] = { added: v2[key] }
        }
      }

      return diffResult
    })

    const close = () => {
      this.$emit('close')
    }

    return {
      formatDate,
      diffs,
      close
    }
  }
}
</script>

<style scoped>
.modal-overlay {
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
  border-radius: 8px;
  width: 80%;
  max-width: 900px;
  max-height: 80vh;
  overflow: auto;
  padding: 20px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.close-button {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

.version-info {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.version {
  flex: 1;
  padding: 10px;
  border: 1px solid #eee;
  border-radius: 4px;
}

.version-meta {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  color: #666;
}

.diff-container {
  border-top: 1px solid #eee;
  padding-top: 20px;
}

.diff-section {
  margin-bottom: 20px;
}

.diff-content {
  margin-top: 10px;
}

.diff-label {
  font-weight: bold;
  display: block;
  margin-bottom: 5px;
}

pre {
  background: #f5f5f5;
  padding: 10px;
  border-radius: 4px;
  white-space: pre-wrap;
  font-family: monospace;
}

.diff-old pre {
  border-left: 3px solid #ff6b6b;
}

.diff-new pre {
  border-left: 3px solid #51cf66;
}

.diff-changed pre {
  border-left: 3px solid #fcc419;
}
</style>