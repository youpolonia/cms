<template>
  <div class="version-selector">
    <div class="version-dropdown">
      <label>Left Version:</label>
      <select v-model="selectedLeft" @change="emitLeftSelection">
        <option value="">Select version...</option>
        <option 
          v-for="version in versions" 
          :key="`left-${version.id}`"
          :value="version.id"
          :disabled="version.id === rightVersion"
        >
          {{ version.label }} ({{ version.date }})
        </option>
      </select>
    </div>
    <div class="version-dropdown">
      <label>Right Version:</label>
      <select v-model="selectedRight" @change="emitRightSelection">
        <option value="">Select version...</option>
        <option 
          v-for="version in versions" 
          :key="`right-${version.id}`"
          :value="version.id"
          :disabled="version.id === leftVersion"
        >
          {{ version.label }} ({{ version.date }})
        </option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VersionSelector',
  props: {
    versions: {
      type: Array,
      required: true
    },
    initialLeft: {
      type: String,
      default: null
    },
    initialRight: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      selectedLeft: this.initialLeft,
      selectedRight: this.initialRight
    }
  },
  methods: {
    emitLeftSelection() {
      this.$emit('select-left', this.selectedLeft)
    },
    emitRightSelection() {
      this.$emit('select-right', this.selectedRight)
    }
  },
  watch: {
    initialLeft(newVal) {
      this.selectedLeft = newVal
    },
    initialRight(newVal) {
      this.selectedRight = newVal
    }
  }
}
</script>

<style scoped>
.version-selector {
  display: flex;
  gap: 1rem;
  padding: 0.5rem;
  background: #f5f5f5;
  border-radius: 4px;
}

.version-dropdown {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.version-dropdown label {
  font-weight: bold;
  font-size: 0.9rem;
}

.version-dropdown select {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
}
</style>