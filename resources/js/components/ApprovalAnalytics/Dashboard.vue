<template>
  <div class="approval-analytics-dashboard">
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Content Approval Analytics</h2>
      
      <!-- Stats Cards Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatsCard 
          v-if="statsData.totalApprovals"
          title="Total Approvals" 
          :value="statsData.totalApprovals.value" 
          :unit="statsData.totalApprovals.unit"
          :trend="statsData.totalApprovals.trend"
          :description="statsData.totalApprovals.description"
        />
        <StatsCard 
          v-if="statsData.avgApprovalTime"
          title="Avg. Approval Time" 
          :value="statsData.avgApprovalTime.value" 
          :unit="statsData.avgApprovalTime.unit"
          :trend="statsData.avgApprovalTime.trend"
          :description="statsData.avgApprovalTime.description"
        />
        <StatsCard 
          v-if="statsData.rejectionRate"
          title="Rejection Rate" 
          :value="statsData.rejectionRate.value" 
          :unit="statsData.rejectionRate.unit"
          :trend="statsData.rejectionRate.trend"
          :description="statsData.rejectionRate.description"
        />
        <StatsCard 
          v-if="statsData.pendingReviews"
          title="Pending Reviews" 
          :value="statsData.pendingReviews.value" 
          :unit="statsData.pendingReviews.unit"
          :description="statsData.pendingReviews.description"
        />
      </div>

      <!-- Loading State -->
      <div v-if="loading && !error" class="text-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading analytics data...</p>
      </div>

      <!-- Error State -->
      <div v-if="error" class="text-center py-8">
        <div class="mx-auto w-12 h-12 flex items-center justify-center rounded-full bg-red-100 text-red-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Failed to load analytics data</h3>
        <p class="mt-2 text-sm text-gray-600">{{ error.message }}</p>
        <button
          @click="retryRequests"
          class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Retry
        </button>
      </div>

      <!-- Charts Section -->
      <div v-if="!loading && !error" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="lg:col-span-2">
          <ErrorBoundary :title="'Timeline Chart Error'" :on-retry="retryRequests">
            <ApprovalTimelineChart 
              :data="timelineData"
            />
          </ErrorBoundary>
        </div>
        <ErrorBoundary :title="'Rejection Reasons Error'" :on-retry="retryRequests">
          <RejectionReasonsChart 
            :data="rejectionData"
          />
        </ErrorBoundary>
        <ErrorBoundary :title="'Completion Rates Error'" :on-retry="retryRequests">
          <CompletionRatesChart 
            :data="completionData"
          />
        </ErrorBoundary>
        <ErrorBoundary :title="'Approval Times Error'" :on-retry="retryRequests">
          <ApprovalTimesChart 
            :data="approvalTimesData"
          />
        </ErrorBoundary>
      </div>
    </div>

    <div class="flex justify-between mt-8">
      <CacheControls 
        @invalidate="handleInvalidate"
        @refresh="retryRequests"
      />
      <ExportControls />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import StatsCard from './StatsCard.vue'
import ErrorBoundary from './ErrorBoundary.vue'
import RejectionReasonsChart from './RejectionReasonsChart.vue'
import CompletionRatesChart from './CompletionRatesChart.vue'
import ApprovalTimesChart from './ApprovalTimesChart.vue'
import ApprovalTimelineChart from './ApprovalTimelineChart.vue'
import ExportControls from './ExportControls.vue'
import approvalAnalyticsApi from '@/api/approvalAnalytics';
import axios from 'axios';

const loading = ref(true);
const error = ref(null);
const retryCount = ref(0);
const maxRetries = 3;
const cacheStatus = ref({
  stats: null,
  timeline: null,
  rejection: null,
  completion: null,
  times: null
});
const statsData = ref({});
const timelineData = ref([]);
const rejectionData = ref([]);
const completionData = ref([]);
const approvalTimesData = ref([]);

// Create cancel tokens for each request
const cancelTokens = ref({
  stats: axios.CancelToken.source(),
  timeline: axios.CancelToken.source(),
  rejection: axios.CancelToken.source(),
  completion: axios.CancelToken.source(),
  times: axios.CancelToken.source()
});

// Watch for cache status changes
watch(cacheStatus, (newStatus) => {
  console.log('Cache status updated:', newStatus);
}, { deep: true });

// Handle server-sent invalidation events
const handleInvalidationEvent = (event) => {
  const { type, key } = event.data;
  if (type === 'invalidate') {
    console.log('Received invalidation event for key:', key);
    retryRequests();
  }
};

onMounted(async () => {
  // Setup event listener for cache invalidation
  const eventSource = new EventSource('/api/content-approval-analytics/events');
  eventSource.addEventListener('invalidate', handleInvalidationEvent);
    try {
      loading.value = true;
      error.value = null;
      
      // Fetch all data in parallel with cancel tokens and retry support
      const [stats, timeline, rejection, completion, times] = await Promise.all([
        approvalAnalyticsApi.getStatsSummary(cancelTokens.value.stats.token)
          .catch(err => {
            if (axios.isCancel(err)) throw err;
            throw new Error(`Failed to load stats after retries: ${err.message}`);
          }),
        approvalAnalyticsApi.getTimelineData(cancelTokens.value.timeline.token)
          .catch(err => {
            if (axios.isCancel(err)) throw err;
            throw new Error(`Failed to load timeline data after retries: ${err.message}`);
          }),
        approvalAnalyticsApi.getRejectionReasons(cancelTokens.value.rejection.token)
          .catch(err => {
            if (axios.isCancel(err)) throw err;
            throw new Error(`Failed to load rejection reasons after retries: ${err.message}`);
          }),
        approvalAnalyticsApi.getCompletionRates(cancelTokens.value.completion.token)
          .catch(err => {
            if (axios.isCancel(err)) throw err;
            throw new Error(`Failed to load completion rates after retries: ${err.message}`);
          }),
        approvalAnalyticsApi.getApprovalTimes(cancelTokens.value.times.token)
          .catch(err => {
            if (axios.isCancel(err)) throw err;
            throw new Error(`Failed to load approval times after retries: ${err.message}`);
          })
      ]);

    // Transform stats data for StatsCard components
    statsData.value = {
      totalApprovals: {
        value: stats.data.total_approvals.toLocaleString(),
        unit: 'items',
        trend: {
          direction: stats.data.total_approvals_trend.direction,
          value: `${stats.data.total_approvals_trend.value}%`
        },
        description: 'Compared to last month'
      },
      avgApprovalTime: {
        value: stats.data.avg_approval_time.toFixed(1),
        unit: 'days',
        trend: {
          direction: stats.data.avg_approval_time_trend.direction,
          value: `${stats.data.avg_approval_time_trend.value}%`
        },
        description: 'Faster than last month'
      },
      rejectionRate: {
        value: stats.data.rejection_rate,
        unit: '%',
        trend: {
          direction: stats.data.rejection_rate_trend.direction,
          value: `${stats.data.rejection_rate_trend.value}%`
        },
        description: 'Lower than last month'
      },
      pendingReviews: {
        value: stats.data.pending_reviews.toLocaleString(),
        unit: 'items',
        description: 'Waiting for approval'
      }
    };

    // Set chart data
    timelineData.value = timeline.data;
    rejectionData.value = rejection.data;
    completionData.value = completion.data;
    approvalTimesData.value = times.data;

  } catch (err) {
    if (!axios.isCancel(err)) {
      error.value = err;
      console.error('Failed to load analytics data after retries:', err);
      // Log additional retry info if available
      if (err.config?.retryCount) {
        console.log(`Retried ${err.config.retryCount} times before failing`);
      }
    }
  } finally {
    loading.value = false;
  }
});

// Cancel all pending requests when component unmounts
onUnmounted(() => {
  Object.values(cancelTokens.value).forEach(source => {
    source.cancel('Component unmounted - request cancelled');
  });
  if (eventSource) {
    eventSource.close();
    eventSource.removeEventListener('invalidate', handleInvalidationEvent);
  }
});

// Handle manual cache invalidation
const handleInvalidate = async (keys) => {
  try {
    loading.value = true;
    await approvalAnalyticsApi.invalidate(keys);
    retryRequests();
  } catch (err) {
    error.value = err;
    console.error('Failed to invalidate cache:', err);
  } finally {
    loading.value = false;
  }
};

// Retry failed requests
const retryRequests = async () => {
  retryCount.value++;
  try {
    // Cancel any pending requests first
    Object.values(cancelTokens.value).forEach(source => {
      source.cancel('Request cancelled for retry');
    });

    // Create new cancel tokens for retry
    cancelTokens.value = {
      stats: axios.CancelToken.source(),
      timeline: axios.CancelToken.source(),
      rejection: axios.CancelToken.source(),
      completion: axios.CancelToken.source(),
      times: axios.CancelToken.source()
    };

    loading.value = true;
    error.value = null;
    
    // Fetch all data again
    const [stats, timeline, rejection, completion, times] = await Promise.all([
      approvalAnalyticsApi.getStatsSummary(cancelTokens.value.stats.token),
      approvalAnalyticsApi.getTimelineData(cancelTokens.value.timeline.token),
      approvalAnalyticsApi.getRejectionReasons(cancelTokens.value.rejection.token),
      approvalAnalyticsApi.getCompletionRates(cancelTokens.value.completion.token),
      approvalAnalyticsApi.getApprovalTimes(cancelTokens.value.times.token)
    ]);

    // Update data with new responses
    statsData.value = {
      totalApprovals: {
        value: stats.data.total_approvals.toLocaleString(),
        unit: 'items',
        trend: {
          direction: stats.data.total_approvals_trend.direction,
          value: `${stats.data.total_approvals_trend.value}%`
        },
        description: 'Compared to last month'
      },
      avgApprovalTime: {
        value: stats.data.avg_approval_time.toFixed(1),
        unit: 'days',
        trend: {
          direction: stats.data.avg_approval_time_trend.direction,
          value: `${stats.data.avg_approval_time_trend.value}%`
        },
        description: 'Faster than last month'
      },
      rejectionRate: {
        value: stats.data.rejection_rate,
        unit: '%',
        trend: {
          direction: stats.data.rejection_rate_trend.direction,
          value: `${stats.data.rejection_rate_trend.value}%`
        },
        description: 'Lower than last month'
      },
      pendingReviews: {
        value: stats.data.pending_reviews.toLocaleString(),
        unit: 'items',
        description: 'Waiting for approval'
      }
    };

    timelineData.value = timeline.data;
    rejectionData.value = rejection.data;
    completionData.value = completion.data;
    approvalTimesData.value = times.data;

  } catch (err) {
    if (!axios.isCancel(err)) {
      error.value = err;
      console.error('Failed to retry loading analytics data:', err);
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.approval-analytics-dashboard {
  @apply p-6;
}
</style>
