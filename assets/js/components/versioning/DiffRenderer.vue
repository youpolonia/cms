<template>
  <div class="diff-renderer">
    <div class="diff-header">
      <div class="version-title">Version {{ leftVersion }}</div>
      <div class="version-title">Version {{ rightVersion }}</div>
    </div>
    <div class="diff-content">
      <div class="version-content left" ref="leftContent">
        <div 
          v-for="(diff, index) in diffs" 
          :key="`left-${index}`"
          :id="`diff-${index}`"
          :class="['diff-block', diff.type]"
        >
          {{ diff.left }}
        </div>
      </div>
      <div class="version-content right" ref="rightContent">
        <div 
          v-for="(diff, index) in diffs" 
          :key="`right-${index}`"
          :id="`diff-${index}`"
          :class="['diff-block', diff.type]"
        >
          {{ diff.right }}
        </div>
      </div>
    </div>
    <div class="diff-navigation">
      <button 
        v-for="(diff, index) in diffs" 
        :key="`nav-${index}`"
        @click="$emit('navigate', index)"
        :class="['nav-button', diff.type]"
      >
        Change {{ index + 1 }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiffRenderer',
  props: {
    leftContent: {
      type: String,
      default: ''
    },
    rightContent: {
      type: String,
      default: ''
    },
    diffs: {
      type: Array,
      default: () => []
    },
    leftVersion: {
      type: String,
      required: true
    },
    rightVersion: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
.diff-renderer {
  display: flex;
  flex-direction: column;
  height: 100%;
  gap: 0.5rem;
}

.diff-header {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem;
  background: #f5f5f5;
  font-weight: bold;
}

.diff-content {
  display: flex;
  flex: 1;
  overflow: auto;
  gap: 1rem;
}

.version-content {
  flex: 1;
  overflow-y: auto;
  padding: 0.5rem;
  border: 1px solid #ddd;
}

.diff-block {
  padding: 0.25rem;
  margin: 0.25rem 0;
  white-space: pre-wrap;
}

.diff-block.added {
  background-color: #e6ffed;
}

.diff-block.removed {
  background-color: #ffeef0;
  text-decoration: line-through;
}

.diff-block.changed {
  background-color: #fff8c5;
}

.diff-navigation {
  display: flex;
  gap: 0.5rem;
  padding: 0.5rem;
  overflow-x: auto;
  background: #f5f5f5;
}

.nav-button {
  padding: 0.25rem 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
  cursor: pointer;
  white-space: nowrap;
}

.nav-button.added {
  border-color: #2cbe4e;
}

.nav-button.removed {
  border-color: #cb2431;
}

.nav-button.changed {
  border-color: #e36209;
}
</style>