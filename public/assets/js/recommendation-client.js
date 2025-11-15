/**
 * Recommendation Client
 * 
 * A lightweight client for interacting with the recommendation API
 */

class RecommendationClient {
    /**
     * Initialize the recommendation client
     * 
     * @param {Object} options Configuration options
     * @param {string} options.apiBase Base URL for API requests (default: '/api')
     * @param {number} options.defaultLimit Default number of recommendations to fetch (default: 5)
     */
    constructor(options = {}) {
        this.apiBase = options.apiBase || '/api';
        this.defaultLimit = options.defaultLimit || 5;
        this.endpoints = {
            recommendations: `${this.apiBase}/recommendations`,
            feedback: `${this.apiBase}/recommendations/feedback`,
            contextual: `${this.apiBase}/recommendations/contextual`,
            abTest: {
                variant: `${this.apiBase}/ab-test/variant`,
                conversion: `${this.apiBase}/ab-test/conversion`
            }
        };
    }

    /**
     * Fetch recommendations for the current user
     * 
     * @param {Object} options Options for the request
     * @param {number} options.limit Number of recommendations to fetch
     * @param {string} options.algorithm Algorithm to use (collaborative, content_based, hybrid)
     * @param {Function} options.onSuccess Success callback
     * @param {Function} options.onError Error callback
     */
    getRecommendations(options = {}) {
        const limit = options.limit || this.defaultLimit;
        const algorithm = options.algorithm || 'hybrid';
        const onSuccess = options.onSuccess || console.log;
        const onError = options.onError || console.error;

        const url = `${this.endpoints.recommendations}?limit=${limit}&algorithm=${algorithm}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    onSuccess(data.data);
                } else {
                    onError(data.message || 'Failed to fetch recommendations');
                }
            })
            .catch(error => {
                onError(error);
            });
    }

    /**
     * Record feedback on a recommendation
     * 
     * @param {Object} options Options for the request
     * @param {number} options.contentId Content ID
     * @param {string} options.feedbackType Type of feedback (click, dismiss, etc.)
     * @param {Function} options.onSuccess Success callback
     * @param {Function} options.onError Error callback
     */
    recordFeedback(options = {}) {
        if (!options.contentId || !options.feedbackType) {
            console.error('Content ID and feedback type are required');
            return;
        }

        const onSuccess = options.onSuccess || (() => {});
        const onError = options.onError || console.error;

        fetch(this.endpoints.feedback, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                content_id: options.contentId,
                feedback_type: options.feedbackType
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    onSuccess(data);
                } else {
                    onError(data.message || 'Failed to record feedback');
                }
            })
            .catch(error => {
                onError(error);
            });
    }

    /**
     * Get contextual recommendations based on current content
     * 
     * @param {Object} options Options for the request
     * @param {number} options.currentContentId Current content ID
     * @param {Object} options.context Additional context data
     * @param {number} options.limit Number of recommendations to fetch
     * @param {Function} options.onSuccess Success callback
     * @param {Function} options.onError Error callback
     */
    getContextualRecommendations(options = {}) {
        if (!options.currentContentId) {
            console.error('Current content ID is required');
            return;
        }

        const onSuccess = options.onSuccess || console.log;
        const onError = options.onError || console.error;
        const limit = options.limit || this.defaultLimit;
        const context = options.context || {};

        fetch(this.endpoints.contextual, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                current_content_id: options.currentContentId,
                context: context,
                limit: limit
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    onSuccess(data.data);
                } else {
                    onError(data.message || 'Failed to fetch contextual recommendations');
                }
            })
            .catch(error => {
                onError(error);
            });
    }

    /**
     * Get A/B test variant for the current user
     * 
     * @param {Object} options Options for the request
     * @param {string} options.testId Test ID
     * @param {Function} options.onSuccess Success callback
     * @param {Function} options.onError Error callback
     */
    getABTestVariant(options = {}) {
        if (!options.testId) {
            console.error('Test ID is required');
            return;
        }

        const onSuccess = options.onSuccess || console.log;
        const onError = options.onError || console.error;

        fetch(`${this.endpoints.abTest.variant}?test_id=${options.testId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    onSuccess(data.data);
                } else {
                    onError(data.message || 'Failed to get A/B test variant');
                }
            })
            .catch(error => {
                onError(error);
            });
    }

    /**
     * Track a conversion for an A/B test
     * 
     * @param {Object} options Options for the request
     * @param {string} options.testId Test ID
     * @param {string} options.conversionType Type of conversion
     * @param {Object} options.metadata Additional metadata
     * @param {Function} options.onSuccess Success callback
     * @param {Function} options.onError Error callback
     */
    trackABTestConversion(options = {}) {
        if (!options.testId || !options.conversionType) {
            console.error('Test ID and conversion type are required');
            return;
        }

        const onSuccess = options.onSuccess || (() => {});
        const onError = options.onError || console.error;
        const metadata = options.metadata || {};

        fetch(this.endpoints.abTest.conversion, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                test_id: options.testId,
                conversion_type: options.conversionType,
                metadata: metadata
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    onSuccess(data);
                } else {
                    onError(data.message || 'Failed to track conversion');
                }
            })
            .catch(error => {
                onError(error);
            });
    }

    /**
     * Render recommendations into a container element
     * 
     * @param {Object} options Options for rendering
     * @param {string|HTMLElement} options.container Container element or selector
     * @param {Array} options.recommendations Recommendation data
     * @param {Function} options.template Template function for rendering each item
     * @param {boolean} options.trackClicks Whether to track clicks (default: true)
     */
    renderRecommendations(options = {}) {
        if (!options.container || !options.recommendations) {
            console.error('Container and recommendations are required');
            return;
        }

        const container = typeof options.container === 'string'
            ? document.querySelector(options.container)
            : options.container;

        if (!container) {
            console.error('Container element not found');
            return;
        }

        const trackClicks = options.trackClicks !== false;
        const template = options.template || this._defaultTemplate;

        // Clear container
        container.innerHTML = '';

        // Render recommendations
        options.recommendations.forEach(item => {
            const element = document.createElement('div');
            element.className = 'recommendation-item';
            element.innerHTML = template(item);

            if (trackClicks) {
                const links = element.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', () => {
                        this.recordFeedback({
                            contentId: item.id,
                            feedbackType: 'click'
                        });
                    });
                });
            }

            container.appendChild(element);
        });
    }

    /**
     * Default template for rendering recommendations
     * 
     * @param {Object} item Recommendation item
     * @returns {string} HTML string
     * @private
     */
    _defaultTemplate(item) {
        return `
            <div class="recommendation">
                <h3><a href="/content/${item.id}">${item.title}</a></h3>
                <p>${item.type}</p>
            </div>
        `;
    }
}

// Export for CommonJS/ES modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RecommendationClient;
} else if (typeof define === 'function' && define.amd) {
    define([], function() {
        return RecommendationClient;
    });
} else {
    window.RecommendationClient = RecommendationClient;
}