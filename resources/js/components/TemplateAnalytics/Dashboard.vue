<template>
  <div class="template-analytics-dashboard">
    <div v-if="loading" class="loading-spinner"></div>
    <div v-else>
      <h2>Template Analytics: {{ template.name }}</h2>
      <div class="stats-grid">
        <stat-card title="Total Uses" :value="stats.total_uses" />
        <stat-card title="Unique Users" :value="stats.unique_users" />
        <stat-card title="Applied" :value="stats.applied_count" />
        <stat-card title="Created" :value="stats.created_count" />
      </div>
      <usage-timeline :data="stats.usage_timeline" />
    </div>
  </div>
</template>

<script>
import TemplateAnalyticsService from '@/services/TemplateAnalyticsService';

export default {
  props: {
    template: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      loading: true,
      stats: {
        total_uses: 0,
        unique_users: 0,
        applied_count: 0,
        created_count: 0,
        usage_timeline: []
      }
    };
  },

  async created() {
    await this.loadStats();
  },

  methods: {
    async loadStats() {
      this.loading = true;
      try {
        const analytics = new TemplateAnalyticsService();
        this.stats = await analytics.getStats(this.template.id);
      } catch (error) {
        console.error('Failed to load template stats:', error);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.template-analytics-dashboard {
  padding: 20px;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin: 20px 0;
}
.loading-spinner {
  /* Add loading spinner styles */
}
</style>