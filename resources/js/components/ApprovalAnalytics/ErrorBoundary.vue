<template>
  <div class="error-boundary">
    <slot v-if="!hasError" />
    
    <div v-else class="error-boundary-fallback">
      <div class="error-icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <h3 class="error-title">{{ title || 'Component Error' }}</h3>
      <p class="error-message">{{ error.message || 'An unexpected error occurred' }}</p>
      <button
        v-if="showRetry"
        @click="handleRetry"
        class="retry-button"
        :disabled="retrying"
      >
        <span v-if="retrying">Retrying...</span>
        <span v-else>Retry</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onErrorCaptured } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  showRetry: {
    type: Boolean,
    default: true
  },
  onRetry: {
    type: Function,
    default: null
  }
});

const hasError = ref(false);
const error = ref(null);
const retrying = ref(false);

onErrorCaptured((err) => {
  error.value = err;
  hasError.value = true;
  
  // Prevent the error from propagating further
  return false;
});

const handleRetry = async () => {
  if (!props.onRetry) {
    hasError.value = false;
    error.value = null;
    return;
  }

  try {
    retrying.value = true;
    await props.onRetry();
    hasError.value = false;
    error.value = null;
  } catch (err) {
    error.value = err;
  } finally {
    retrying.value = false;
  }
};
</script>

<style scoped>
.error-boundary {
  @apply h-full;
}

.error-boundary-fallback {
  @apply p-4 text-center bg-red-50 rounded-lg h-full flex flex-col items-center justify-center;
}

.error-icon {
  @apply mx-auto w-12 h-12 flex items-center justify-center rounded-full bg-red-100 text-red-600 mb-4;
}

.error-title {
  @apply text-lg font-medium text-gray-900 mb-2;
}

.error-message {
  @apply text-sm text-gray-600 mb-4;
}

.retry-button {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed;
}
</style>
