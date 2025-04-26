import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Dashboard from '@/components/ApprovalAnalytics/Dashboard.vue';
import approvalAnalyticsApi from '@/api/approvalAnalytics';

// Mock the API module
vi.mock('@/api/approvalAnalytics', () => ({
  default: {
    getStatsSummary: vi.fn(),
    getTimelineData: vi.fn(),
    getRejectionReasons: vi.fn(),
    getCompletionRates: vi.fn(),
    getApprovalTimes: vi.fn()
  }
}));

describe('Dashboard.vue', () => {
  const mockStatsData = {
    data: {
      total_approvals: 1250,
      total_approvals_trend: { direction: 'up', value: 12.5 },
      avg_approval_time: 3.2,
      avg_approval_time_trend: { direction: 'down', value: 5.3 },
      rejection_rate: 15.7,
      rejection_rate_trend: { direction: 'down', value: 2.1 },
      pending_reviews: 42
    }
  };

  const mockTimelineData = {
    data: [
      { date: '2025-04-01', approvals: 12, rejections: 2 },
      { date: '2025-04-02', approvals: 15, rejections: 3 }
    ]
  };

  const mockRejectionData = {
    data: [
      { reason: 'Quality', count: 12 },
      { reason: 'Policy', count: 8 }
    ]
  };

  const mockCompletionData = {
    data: [
      { step: 'Initial Review', completion_rate: 85 },
      { step: 'Final Approval', completion_rate: 92 }
    ]
  };

  const mockApprovalTimesData = {
    data: [
      { step: 'Initial Review', avg_time: 1.2 },
      { step: 'Final Approval', avg_time: 0.8 }
    ]
  };

  beforeEach(() => {
    // Reset all mocks before each test
    vi.resetAllMocks();
  });

  it('shows loading state while fetching data', async () => {
    // Setup delayed response to test loading state
    approvalAnalyticsApi.getStatsSummary.mockImplementation(() => 
      new Promise(() => {}) // Never resolves
    );

    const wrapper = mount(Dashboard);
    expect(wrapper.find('.animate-spin').exists()).toBe(true);
    expect(wrapper.text()).toContain('Loading analytics data...');
  });

  it('renders stats cards with correct data after successful fetch', async () => {
    // Mock successful API responses
    approvalAnalyticsApi.getStatsSummary.mockResolvedValue(mockStatsData);
    approvalAnalyticsApi.getTimelineData.mockResolvedValue(mockTimelineData);
    approvalAnalyticsApi.getRejectionReasons.mockResolvedValue(mockRejectionData);
    approvalAnalyticsApi.getCompletionRates.mockResolvedValue(mockCompletionData);
    approvalAnalyticsApi.getApprovalTimes.mockResolvedValue(mockApprovalTimesData);

    const wrapper = mount(Dashboard);
    
    // Wait for API calls to resolve
    await new Promise(resolve => setTimeout(resolve, 0));
    await wrapper.vm.$nextTick();

    // Check loading state is gone
    expect(wrapper.find('.animate-spin').exists()).toBe(false);

    // Check stats cards are rendered with correct data
    const statsCards = wrapper.findAllComponents({ name: 'StatsCard' });
    expect(statsCards.length).toBe(4);

    expect(statsCards[0].props('title')).toBe('Total Approvals');
    expect(statsCards[0].props('value')).toBe('1,250');
    expect(statsCards[0].props('trend').direction).toBe('up');

    expect(statsCards[1].props('title')).toBe('Avg. Approval Time');
    expect(statsCards[1].props('value')).toBe('3.2');
    expect(statsCards[1].props('trend').direction).toBe('down');

    expect(statsCards[2].props('title')).toBe('Rejection Rate');
    expect(statsCards[2].props('value')).toBe('15.7');

    expect(statsCards[3].props('title')).toBe('Pending Reviews');
    expect(statsCards[3].props('value')).toBe('42');
  });

  it('renders all chart components with data', async () => {
    // Mock successful API responses
    approvalAnalyticsApi.getStatsSummary.mockResolvedValue(mockStatsData);
    approvalAnalyticsApi.getTimelineData.mockResolvedValue(mockTimelineData);
    approvalAnalyticsApi.getRejectionReasons.mockResolvedValue(mockRejectionData);
    approvalAnalyticsApi.getCompletionRates.mockResolvedValue(mockCompletionData);
    approvalAnalyticsApi.getApprovalTimes.mockResolvedValue(mockApprovalTimesData);

    const wrapper = mount(Dashboard);
    await new Promise(resolve => setTimeout(resolve, 0));
    await wrapper.vm.$nextTick();

    // Check all chart components are rendered with data
    expect(wrapper.findComponent({ name: 'ApprovalTimelineChart' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'RejectionReasonsChart' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'CompletionRatesChart' }).exists()).toBe(true);
    expect(wrapper.findComponent({ name: 'ApprovalTimesChart' }).exists()).toBe(true);
  });

  it('handles API errors gracefully', async () => {
    // Mock a failed API call
    const error = new Error('API Error');
    approvalAnalyticsApi.getStatsSummary.mockRejectedValue(error);

    // Mock console.error to track if it's called
    const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    const wrapper = mount(Dashboard);
    await new Promise(resolve => setTimeout(resolve, 0));
    await wrapper.vm.$nextTick();

    // Check error was logged
    expect(consoleErrorSpy).toHaveBeenCalledWith('Failed to load analytics data:', error);
    
    // Check loading state is gone
    expect(wrapper.find('.animate-spin').exists()).toBe(false);

    // Clean up spy
    consoleErrorSpy.mockRestore();
  });

  it('transforms stats data correctly', async () => {
    approvalAnalyticsApi.getStatsSummary.mockResolvedValue(mockStatsData);
    approvalAnalyticsApi.getTimelineData.mockResolvedValue(mockTimelineData);
    approvalAnalyticsApi.getRejectionReasons.mockResolvedValue(mockRejectionData);
    approvalAnalyticsApi.getCompletionRates.mockResolvedValue(mockCompletionData);
    approvalAnalyticsApi.getApprovalTimes.mockResolvedValue(mockApprovalTimesData);

    const wrapper = mount(Dashboard);
    await new Promise(resolve => setTimeout(resolve, 0));
    await wrapper.vm.$nextTick();

    // Check transformed data structure
    const statsData = wrapper.vm.statsData;
    expect(statsData.totalApprovals.value).toBe('1,250');
    expect(statsData.totalApprovals.trend.value).toBe('12.5%');
    expect(statsData.avgApprovalTime.value).toBe('3.2');
    expect(statsData.rejectionRate.value).toBe('15.7');
    expect(statsData.pendingReviews.value).toBe('42');
  });
});
