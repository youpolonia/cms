<template>
  <div class="error-boundary">
    <slot v-if="!hasError" />
    <div v-else class="error-fallback">
      <h3>Something went wrong</h3>
      <p>{{ error }}</p>
      <button @click="resetError">Try again</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const hasError = ref(false);
const error = ref(null);

const resetError = () => {
  hasError.value = false;
  error.value = null;
};

const errorHandler = (err) => {
  hasError.value = true;
  error.value = err.message;
  console.error('Error caught by boundary:', err);
};

defineExpose({
  errorHandler
});
</script>

<style scoped>
.error-boundary {
  position: relative;
}

.error-fallback {
  padding: 20px;
  background: #f8d7da;
  border: 1px solid #f5c6cb;
  border-radius: 4px;
  color: #721c24;
}

.error-fallback h3 {
  margin-top: 0;
}

.error-fallback button {
  margin-top: 10px;
  padding: 5px 10px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>