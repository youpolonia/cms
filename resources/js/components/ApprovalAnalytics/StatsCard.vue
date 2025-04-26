<template>
  <div class="stats-card bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
      <div 
        class="flex items-center text-sm"
        :class="trend > 0 ? 'text-green-600' : 'text-red-600'"
      >
        <span>{{ trend > 0 ? '+' : '' }}{{ trend }}%</span>
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          class="h-4 w-4 ml-1"
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            :d="trend > 0 ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"
          />
        </svg>
      </div>
    </div>

    <div class="text-3xl font-bold text-gray-900 mb-2">
      {{ value }}
    </div>

    <div class="text-sm text-gray-500">
      {{ description }}
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: 'Metric'
  },
  value: {
    type: [String, Number],
    default: '0'
  },
  trend: {
    type: Number,
    default: 0
  },
  description: {
    type: String,
    default: ''
  }
})
</script>

<style scoped>
.stats-card {
  min-width: 200px;
}
</style>
