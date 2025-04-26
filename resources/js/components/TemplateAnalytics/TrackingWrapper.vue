<template>
  <div class="tracking-wrapper">
    <slot />
  </div>
</template>

<script>
import TemplateAnalyticsService from '../../services/TemplateAnalyticsService';

export default {
  props: {
    template: {
      type: Object,
      required: true
    },
    action: {
      type: String,
      required: true
    },
    metadata: {
      type: Object,
      default: () => ({})
    }
  },

  mounted() {
    this.trackEvent();
  },

  methods: {
    async trackEvent() {
      try {
        const analytics = new TemplateAnalyticsService();
        await analytics.track(this.action, this.template, this.metadata);
      } catch (error) {
        console.error('Tracking error:', error);
      }
    }
  }
};
</script>

<style scoped>
.tracking-wrapper {
  display: contents;
}
</style>