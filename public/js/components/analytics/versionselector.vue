<template>
  <div class="version-selector">
    <label>{{ label }}</label>
    <select v-model="selectedVersion" @change="handleChange">
      <option value="" disabled>Select version</option>
      <option 
        v-for="version in versions" 
        :key="version.id"
        :value="version.id"
      >
        {{ version.label }} ({{ version.date }})
      </option>
    </select>
  </div>
</template>

<script>
export default {
  props: {
    label: String,
    versions: Array,
    value: [String, Number]
  },
  data() {
    return {
      selectedVersion: this.value
    }
  },
  methods: {
    handleChange() {
      this.$emit('input', this.selectedVersion);
    }
  },
  watch: {
    value(newVal) {
      this.selectedVersion = newVal;
    }
  }
}
</script>

<style scoped>
.version-selector {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.version-selector select {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>