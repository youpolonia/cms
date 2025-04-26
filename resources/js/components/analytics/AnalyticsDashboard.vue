<template>
  <div class="analytics-dashboard">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Version Comparison Stats</h2>
        <div v-if="loading" class="text-center py-8">
          <LoadingSpinner />
        </div>
        <div v-else>
          <div class="grid grid-cols-3 gap-4 mb-6">
            <StatCard 
              title="Total Changes" 
              :value="stats.change_count" 
              icon="document-text"
            />
            <StatCard 
              title="Additions" 
              :value="stats.additions" 
              icon="plus-circle"
            />
            <StatCard 
              title="Deletions" 
              :value="stats.deletions" 
              icon="minus-circle"
            />
          </div>
          <SimilarityChart :similarity="stats.similarity" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">View Statistics</h2>
        <ViewsChart 
          :version1Views="stats.version1_views"
          :version2Views="stats.version2_views"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import StatCard from '@/components/StatCard.vue'
import SimilarityChart from '@/components/analytics/SimilarityChart.vue'
import ViewsChart from '@/components/analytics/ViewsChart.vue'
import { getVersionComparisonStats } from '@/services/analyticsService'

export default {
  components: {
    LoadingSpinner,
    StatCard,
    SimilarityChart,
    ViewsChart
  },
  setup() {
    const route = useRoute()
    const loading = ref(true)
    const stats = ref({
      change_count: 0,
      additions: 0,
      deletions: 0,
      similarity: 0,
      version1_views: 0,
      version2_views: 0
    })

    onMounted(async () => {
      try {
        const { version1Id, version2Id } = route.params
        stats.value = await getVersionComparisonStats(version1Id, version2Id)
      } catch (error) {
        console.error('Failed to load analytics:', error)
      } finally {
        loading.value = false
      }
    })

    return {
      loading,
      stats
    }
  }
}
</script>

<style scoped>
.analytics-dashboard {
  @apply p-4;
}
</style>