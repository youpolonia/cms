<template>
  <div class="analytics-dashboard">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <StatsCard 
        title="Total Views" 
        :value="stats.totalViews" 
        icon="eye"
      />
      <StatsCard 
        title="Unique Visitors" 
        :value="stats.uniqueVisitors" 
        icon="users"
      />
      <StatsCard 
        title="Avg. Time Spent" 
        :value="stats.avgTimeSpent" 
        icon="clock"
        suffix="min"
      />
    </div>

    <div class="mb-6">
      <ComparisonVisualization 
        v-if="comparisonData"
        :data="comparisonData"
      />
    </div>

    <div>
      <h3 class="text-lg font-medium mb-2">Recent Exports</h3>
      <RecentExportsList 
        :exports="recentExports"
        @refresh="fetchRecentExports"
      />
    </div>
  </div>
</template>

<script>
import StatsCard from './StatsCard.vue';
import ComparisonVisualization from './ComparisonVisualization.vue';
import RecentExportsList from './RecentExportsList.vue';
import { ref, onMounted } from 'vue';
import api from '@/services/api';

export default {
  components: {
    StatsCard,
    ComparisonVisualization,
    RecentExportsList
  },
  setup() {
    const stats = ref({
      totalViews: 0,
      uniqueVisitors: 0,
      avgTimeSpent: 0
    });
    const comparisonData = ref(null);
    const recentExports = ref([]);

    const fetchStats = async () => {
      try {
        const { data } = await api.get('/analytics/versions/1/stats');
        stats.value = data;
      } catch (error) {
        console.error('Error fetching stats:', error);
      }
    };

    const fetchComparison = async () => {
      try {
        const { data } = await api.get('/analytics/compare/1/2');
        comparisonData.value = data;
      } catch (error) {
        console.error('Error fetching comparison:', error);
      }
    };

    const fetchRecentExports = async () => {
      try {
        const { data } = await api.get('/analytics/exports/recent');
        recentExports.value = data.exports;
      } catch (error) {
        console.error('Error fetching recent exports:', error);
      }
    };

    onMounted(() => {
      fetchStats();
      fetchComparison();
      fetchRecentExports();
    });

    return {
      stats,
      comparisonData,
      recentExports,
      fetchRecentExports
    };
  }
};
</script>

<style scoped>
.analytics-dashboard {
  @apply p-4 bg-white rounded-lg shadow;
}
</style>