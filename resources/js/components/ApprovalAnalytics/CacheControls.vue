<template>
  <div class="cache-controls">
    <div class="flex items-center space-x-4">
      <button
        @click="$emit('refresh')"
        class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh Data
      </button>

      <div class="relative">
        <button
          @click="showInvalidateOptions = !showInvalidateOptions"
          class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Invalidate Cache
        </button>

        <div v-if="showInvalidateOptions" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
          <div class="py-1">
            <button
              @click="invalidateAll"
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
            >
              Invalidate All
            </button>
            <button
              @click="invalidateCurrent"
              class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
            >
              Invalidate Current Data
            </button>
          </div>
        </div>
      </div>

      <div class="text-sm text-gray-500">
        <span v-if="lastUpdated">Last updated: {{ lastUpdated }}</span>
        <span v-else>Loading...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const showInvalidateOptions = ref(false);
const lastUpdated = ref(new Date().toLocaleTimeString());

const invalidateAll = () => {
  showInvalidateOptions.value = false;
  emit('invalidate', 'all');
};

const invalidateCurrent = () => {
  showInvalidateOptions.value = false;
  emit('invalidate', ['stats-summary', 'timeline', 'rejection-reasons', 'completion-rates', 'approval-times']);
};
</script>

<style scoped>
.cache-controls {
  @apply flex items-center;
}
</style>
