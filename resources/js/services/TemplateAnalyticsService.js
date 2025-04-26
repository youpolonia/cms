import AnalyticsCacheService from './AnalyticsCacheService';

export default class TemplateAnalyticsService {
    constructor() {
        this.cache = new AnalyticsCacheService();
        this.endpoint = '/api/template-analytics';
    }

    async track(action, template, metadata = {}) {
        const cacheKey = `template_${template.id}_${action}`;
        
        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    template_id: template.id,
                    action,
                    metadata
                })
            });

            if (!response.ok) throw new Error('Tracking failed');

            const data = await response.json();
            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error('Template analytics error:', error);
            throw error;
        }
    }

    async getStats(templateId = null) {
        const cacheKey = `template_stats_${templateId || 'all'}`;
        const cached = this.cache.get(cacheKey);
        if (cached) return cached;

        try {
            const url = templateId 
                ? `${this.endpoint}/stats?template_id=${templateId}`
                : `${this.endpoint}/stats`;

            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to fetch template stats');
            
            const data = await response.json();
            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error('Failed to get template analytics:', error);
            throw error;
        }
    }
}