import axios from 'axios';
import axiosRetry from 'axios-retry';
import AnalyticsCacheService from '../services/AnalyticsCacheService';

const cacheService = new AnalyticsCacheService();
const eventSource = new EventSource('/api/content-approval-analytics/events');
const CACHE_KEYS = {
    STATS_SUMMARY: 'stats-summary',
    TIMELINE: 'timeline',
    COMPLETION_RATES: 'completion-rates',
    APPROVAL_TIMES: 'approval-times',
    REJECTION_REASONS: 'rejection-reasons'
};

const API_BASE = '/api/content-approval-analytics';

// Configure axios-retry with exponential backoff
axiosRetry(axios, {
  retries: 3,
  retryDelay: (retryCount) => {
    const delay = 1000 * Math.pow(2, retryCount);
    return delay;
  },
  retryCondition: (error) => {
    // Only retry on server errors or network issues
    return !axios.isCancel(error) && 
           (!error.response || error.response.status >= 500);
  }
});

// Listen for server-sent events to invalidate cache
eventSource.addEventListener('invalidate', (event) => {
    const { keys, pattern } = JSON.parse(event.data);
    if (keys) {
        cacheService.clear(keys);
    } else if (pattern) {
        cacheService.invalidateMatching(new RegExp(pattern));
    }
});

export default {
    /**
     * Invalidate specific cache keys
     * @param {string|Array} keys - Key or array of keys to invalidate
     */
    invalidate(keys) {
        cacheService.clear(keys);
    },

    /**
     * Invalidate cache entries matching a pattern
     * @param {RegExp} pattern - Regex pattern to match keys against
     */
    invalidateMatching(pattern) {
        cacheService.invalidateMatching(pattern);
    },

    /**
     * Invalidate all analytics cache entries
     */
    invalidateAll() {
        cacheService.invalidateAll();
    },

    /**
     * Get approval statistics summary
     * @param {Object} cancelToken - Axios cancel token
     * @returns {Promise} Axios promise with automatic retry
     * @description Requests will automatically retry up to 3 times with exponential backoff
     * (1000ms, 2000ms, 4000ms delays) for server errors (500+) or network issues.
     * Canceled requests will not be retried.
     */
    getStatsSummary(cancelToken) {
        const cacheKey = CACHE_KEYS.STATS_SUMMARY;
        const cachedData = cacheService.get(cacheKey);
        if (cachedData) {
            return Promise.resolve({ data: cachedData });
        }
        
        return axios.get(`${API_BASE}/stats-summary`, { cancelToken })
            .then(response => {
                cacheService.set(cacheKey, response.data);
                return response;
            });
    },

    /**
     * Get approval timeline data
     * @param {Object} cancelToken - Axios cancel token
     * @returns {Promise} Axios promise with automatic retry
     * @description Requests will automatically retry up to 3 times with exponential backoff
     * (1000ms, 2000ms, 4000ms delays) for server errors (500+) or network issues.
     * Canceled requests will not be retried.
     */
    getTimelineData(cancelToken) {
        const cacheKey = CACHE_KEYS.TIMELINE;
        const cachedData = cacheService.get(cacheKey);
        if (cachedData) {
            return Promise.resolve({ data: cachedData });
        }
        
        return axios.get(`${API_BASE}/timeline`, { cancelToken })
            .then(response => {
                cacheService.set(cacheKey, response.data);
                return response;
            });
    },

    /**
     * Export analytics data
     * @param {string} format - Export format (csv, json, etc)
     * @param {Object} cancelToken - Axios cancel token
     * @returns {Promise} Axios promise with automatic retry
     * @description Requests will automatically retry up to 3 times with exponential backoff
     * (1000ms, 2000ms, 4000ms delays) for server errors (500+) or network issues.
     * Canceled requests will not be retried.
     */
    exportData(format, cancelToken) {
        return axios.post(`${API_BASE}/export`, { format }, { cancelToken });
    },

    /**
     * Get completion rates data
     * @param {Object} cancelToken - Axios cancel token
     * @returns {Promise} Axios promise with automatic retry
     * @description Requests will automatically retry up to 3 times with exponential backoff
     * (1000ms, 2000ms, 4000ms delays) for server errors (500+) or network issues.
     * Canceled requests will not be retried.
     */
    getCompletionRates(cancelToken) {
        const cacheKey = CACHE_KEYS.COMPLETION_RATES;
        const cachedData = cacheService.get(cacheKey);
        if (cachedData) {
            return Promise.resolve({ data: cachedData });
        }
        
        return axios.get(`${API_BASE}/completion-rates`, { cancelToken })
            .then(response => {
                cacheService.set(cacheKey, response.data);
                return response;
            });
    },

    /**
     * Get approval times data
     * @param {Object} cancelToken - Axios cancel token
     * @returns {Promise} Axios promise with automatic retry
     * @description Requests will automatically retry up to 3 times with exponential backoff
     * (1000ms, 2000ms, 4000ms delays) for server errors (500+) or network issues.
     * Canceled requests will not be retried.
     */
    getApprovalTimes(cancelToken) {
        const cacheKey = CACHE_KEYS.APPROVAL_TIMES;
        const cachedData = cacheService.get(cacheKey);
        if (cachedData) {
            return Promise.resolve({ data: cachedData });
        }
        
        return axios.get(`${API_BASE}/approval-times`, { cancelToken })
            .then(response => {
                cacheService.set(cacheKey, response.data);
                return response;
            });
    },

    /**
     * Get rejection reasons data
     * @param {Object} cancelToken - Axios cancel token
     * @returns {Promise} Axios promise with automatic retry
     * @description Requests will automatically retry up to 3 times with exponential backoff
     * (1000ms, 2000ms, 4000ms delays) for server errors (500+) or network issues.
     * Canceled requests will not be retried.
     */
    getRejectionReasons(cancelToken) {
        const cacheKey = CACHE_KEYS.REJECTION_REASONS;
        const cachedData = cacheService.get(cacheKey);
        if (cachedData) {
            return Promise.resolve({ data: cachedData });
        }
        
        return axios.get(`${API_BASE}/rejection-reasons`, { cancelToken })
            .then(response => {
                cacheService.set(cacheKey, response.data);
                return response;
            });
    }
};
