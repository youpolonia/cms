class VersionComparisonAnalytics {
    constructor() {
        this.trackingActive = false;
        this.currentVersionId = null;
        this.comparedVersionId = null;
        this.lastChangeTime = null;
    }

    /**
     * Start tracking analytics for a version comparison
     * @param {string} versionId - The base version ID
     * @param {string} comparedVersionId - The version being compared against
     */
    startTracking(versionId, comparedVersionId, isAutosave = false, isComparedAutosave = false) {
        this.trackingActive = true;
        this.currentVersionId = versionId;
        this.comparedVersionId = comparedVersionId;
        this.lastChangeTime = new Date().toISOString();
        this.isAutosave = isAutosave;
        this.isComparedAutosave = isComparedAutosave;

        this.sendAnalytics({
            event: 'comparison_started',
            version_id: versionId,
            compared_version_id: comparedVersionId,
            is_autosave: isAutosave,
            is_compared_autosave: isComparedAutosave,
            timestamp: this.lastChangeTime
        });
    }

    /**
     * Stop tracking analytics for the current comparison
     */
    stopTracking() {
        if (!this.trackingActive) return;

        const endTime = new Date().toISOString();
        this.sendAnalytics({
            event: 'comparison_ended',
            version_id: this.currentVersionId,
            compared_version_id: this.comparedVersionId,
            is_autosave: this.isAutosave,
            is_compared_autosave: this.isComparedAutosave,
            start_time: this.lastChangeTime,
            end_time: endTime
        });

        this.trackingActive = false;
        this.currentVersionId = null;
        this.comparedVersionId = null;
        this.lastChangeTime = null;
    }

    /**
     * Track when user toggles between views (e.g. side-by-side vs unified)
     * @param {string} viewType - The view type being switched to
     */
    trackViewToggle(viewType) {
        if (!this.trackingActive) return;

        const timestamp = new Date().toISOString();
        this.sendAnalytics({
            event: 'view_toggled',
            version_id: this.currentVersionId,
            compared_version_id: this.comparedVersionId,
            view_type: viewType,
            is_autosave: this.isAutosave,
            is_compared_autosave: this.isComparedAutosave,
            is_autosave: this.isAutosave,
            is_compared_autosave: this.isComparedAutosave,
            timestamp: timestamp
        });
    }

    /**
     * Track when user views a specific change in the comparison
     */
    trackChangeViewed() {
        if (!this.trackingActive) return;

        const timestamp = new Date().toISOString();
        this.sendAnalytics({
            event: 'change_viewed',
            version_id: this.currentVersionId,
            compared_version_id: this.comparedVersionId,
            timestamp: timestamp
        });
    }

    /**
     * Send analytics data to the backend API
     * @param {object} data - The analytics data to send
     */
    sendAnalytics(data) {
        fetch('/api/version-comparison-analytics', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        }).catch(error => {
            console.error('Analytics error:', error);
        });
    }

    /**
     * Export version comparison analytics data
     * @param {string} contentId - The content ID being analyzed
     * @param {string} format - Export format (csv, json, pdf)
     * @returns {Promise} Resolves with export status
     */
    exportAnalytics(contentId, format = 'csv') {
        return fetch(`/api/version-comparison/${contentId}/export`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ format })
        })
        .then(response => response.json())
        .catch(error => {
            console.error('Export error:', error);
            throw error;
        });
    }

    /**
     * Check export status
     * @param {string} exportId - The export job ID
     * @returns {Promise} Resolves with export status
     */
    checkExportStatus(exportId) {
        return fetch(`/api/analytics-exports/${exportId}/status`)
            .then(response => response.json())
            .catch(error => {
                console.error('Status check error:', error);
                throw error;
            });
    }
}

// Export as module if using ES modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VersionComparisonAnalytics;
} else {
    // Make available globally if not using modules
    window.VersionComparisonAnalytics = VersionComparisonAnalytics;
}
