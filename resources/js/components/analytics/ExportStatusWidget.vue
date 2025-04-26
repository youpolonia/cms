<template>
  <div class="bg-white rounded-lg shadow p-4">
    <div class="flex items-center justify-between mb-2">
      <h3 class="font-medium text-gray-900">Export Status</h3>
      <span class="text-xs text-gray-500">Last updated: {{ lastUpdated }}</span>
    </div>

    <div class="space-y-3">
      <div v-for="exportRun in recentExports" :key="exportRun.id" class="flex items-center">
        <span class="w-3 h-3 rounded-full mr-2" 
              :class="{
                'bg-green-500': exportRun.status === 'completed',
                'bg-yellow-500': exportRun.status === 'processing',
                'bg-red-500': exportRun.status === 'failed'
              }"></span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 truncate">
            {{ exportRun.export.type }} export
          </p>
          <p class="text-xs text-gray-500 truncate">
            {{ formatDate(exportRun.created_at) }}
          </p>
        </div>
        <span class="text-xs text-gray-500 ml-2">
          {{ exportRun.status }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    recentExports: {
      type: Array,
      required: true,
      default: () => []
    }
  },

  computed: {
    lastUpdated() {
      return new Date().toLocaleTimeString()
    }
  },

  methods: {
    formatDate(date) {
      return new Date(date).toLocaleString()
    }
  }
}
</script>