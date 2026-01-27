/**
 * CMS Analytics Tracker
 * Lightweight page view and event tracking
 */
(function() {
    'use strict';

    const CMS_ANALYTICS = {
        endpoint: '/api/analytics/track.php',
        viewId: null,
        startTime: Date.now(),
        initialized: false,

        /**
         * Initialize the tracker
         */
        init: function(options) {
            if (this.initialized) return;

            this.endpoint = options?.endpoint || this.endpoint;
            this.tenantId = options?.tenantId || null;

            // Track initial page view
            this.trackPageView();

            // Track duration when user leaves
            this.setupDurationTracking();

            this.initialized = true;
        },

        /**
         * Track a page view
         */
        trackPageView: function(url, title) {
            const data = {
                action: 'page_view',
                page_url: url || window.location.href,
                page_title: title || document.title,
                tenant_id: this.tenantId
            };

            this.send(data, (response) => {
                if (response && response.view_id) {
                    this.viewId = response.view_id;
                }
            });
        },

        /**
         * Track a custom event
         */
        trackEvent: function(eventType, eventName, eventData) {
            const data = {
                action: 'event',
                event_type: eventType,
                event_name: eventName,
                event_data: eventData || {},
                tenant_id: this.tenantId
            };

            this.send(data);
        },

        /**
         * Track click event
         */
        trackClick: function(element, eventName) {
            this.trackEvent('click', eventName || 'element_click', {
                tag: element.tagName,
                id: element.id || null,
                class: element.className || null,
                text: (element.textContent || '').substring(0, 50)
            });
        },

        /**
         * Track form submission
         */
        trackForm: function(formId, eventName) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', () => {
                this.trackEvent('form', eventName || 'form_submit', {
                    form_id: formId,
                    form_action: form.action
                });
            });
        },

        /**
         * Track outbound link clicks
         */
        trackOutboundLinks: function() {
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a[href]');
                if (!link) return;

                const href = link.getAttribute('href');
                if (href && href.startsWith('http') && !href.includes(window.location.hostname)) {
                    this.trackEvent('outbound', 'outbound_link', {
                        url: href,
                        text: (link.textContent || '').substring(0, 50)
                    });
                }
            });
        },

        /**
         * Track scroll depth
         */
        trackScrollDepth: function() {
            let maxScroll = 0;
            const thresholds = [25, 50, 75, 100];
            const triggered = {};

            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset;
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrollPercent = Math.round((scrollTop / docHeight) * 100);

                if (scrollPercent > maxScroll) {
                    maxScroll = scrollPercent;

                    thresholds.forEach(threshold => {
                        if (scrollPercent >= threshold && !triggered[threshold]) {
                            triggered[threshold] = true;
                            this.trackEvent('scroll', 'scroll_depth', {
                                depth: threshold
                            });
                        }
                    });
                }
            });
        },

        /**
         * Setup duration tracking on page unload
         */
        setupDurationTracking: function() {
            const sendDuration = () => {
                if (!this.viewId) return;

                const duration = Math.round((Date.now() - this.startTime) / 1000);

                // Use sendBeacon for reliability on page unload
                if (navigator.sendBeacon) {
                    navigator.sendBeacon(this.endpoint, JSON.stringify({
                        action: 'duration',
                        view_id: this.viewId,
                        duration: duration
                    }));
                } else {
                    // Fallback for older browsers
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', this.endpoint, false);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.send(JSON.stringify({
                        action: 'duration',
                        view_id: this.viewId,
                        duration: duration
                    }));
                }
            };

            // Track on visibility change (more reliable than unload)
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') {
                    sendDuration();
                }
            });

            // Fallback for page unload
            window.addEventListener('beforeunload', sendDuration);
        },

        /**
         * Send data to the tracking endpoint
         */
        send: function(data, callback) {
            fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (callback) callback(result);
            })
            .catch(err => {
                console.warn('Analytics tracking failed:', err);
            });
        }
    };

    // Expose globally
    window.CMS_ANALYTICS = CMS_ANALYTICS;

    // Auto-initialize if data attribute is present
    document.addEventListener('DOMContentLoaded', function() {
        const script = document.querySelector('script[data-cms-analytics]');
        if (script) {
            CMS_ANALYTICS.init({
                endpoint: script.getAttribute('data-endpoint'),
                tenantId: script.getAttribute('data-tenant-id')
            });

            // Enable optional features
            if (script.hasAttribute('data-track-outbound')) {
                CMS_ANALYTICS.trackOutboundLinks();
            }
            if (script.hasAttribute('data-track-scroll')) {
                CMS_ANALYTICS.trackScrollDepth();
            }
        }
    });
})();
