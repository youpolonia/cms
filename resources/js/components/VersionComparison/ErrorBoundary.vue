<template>
  <slot v-if="!hasError" />
  <div v-else class="error-boundary">
    <h3>Something went wrong</h3>
    <p>{{ errorMessage }}</p>
    <button @click="resetError">Try again</button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'ErrorBoundary',
  data() {
    return {
      hasError: false,
      errorMessage: ''
    };
  },
  methods: {
    resetError() {
      this.hasError = false;
      this.errorMessage = '';
    }
  },
  errorCaptured(err: unknown) {
    this.hasError = true;
    this.errorMessage = err instanceof Error ? err.message : String(err);
    return false;
  }
});
</script>

<style scoped>
.error-boundary {
  padding: 1rem;
  border: 1px solid #f87171;
  background-color: #fee2e2;
  color: #b91c1c;
  border-radius: 0.25rem;
  margin: 1rem 0;
}

.error-boundary button {
  margin-top: 0.5rem;
  padding: 0.25rem 0.5rem;
  background-color: #b91c1c;
  color: white;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}
</style>