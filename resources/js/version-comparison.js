import { createApp } from 'vue';
import VersionComparison from './components/VersionComparison.vue';

document.addEventListener('DOMContentLoaded', () => {
    const versionComparisonElements = document.querySelectorAll('.version-comparison-container');
    
    versionComparisonElements.forEach(el => {
        const oldContent = el.dataset.oldContent || '';
        const newContent = el.dataset.newContent || '';
        const isOldAutosave = el.dataset.isOldAutosave === 'true';
        const isNewAutosave = el.dataset.isNewAutosave === 'true';
        
        // Initialize analytics if available
        if (window.VersionComparisonAnalytics) {
            const analytics = new VersionComparisonAnalytics();
            analytics.startTracking(
                el.dataset.versionId,
                el.dataset.comparedVersionId,
                isOldAutosave,
                isNewAutosave
            );
        }
        
        createApp(VersionComparison, {
            oldContent,
            newContent,
            isOldAutosave,
            isNewAutosave
        }).mount(el);
    });
});
