import AnalyticsCacheService from './AnalyticsCacheService';
import TemplateAnalyticsService from './TemplateAnalyticsService';

export default class AIContentService {
    constructor() {
        this.cache = new AnalyticsCacheService();
        this.templateAnalytics = new TemplateAnalyticsService();
        this.endpoint = '/api/ai/generate';
    }

    async generateContent(prompt, template = null) {
        const cacheKey = `ai_generation_${template?.id || 'custom'}_${prompt.substring(0, 20)}`;
        
        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    prompt,
                    template_id: template?.id || null
                })
            });

            if (!response.ok) throw new Error('AI generation failed');

            const data = await response.json();

            // Track template usage if applicable
            if (template) {
                await this.templateAnalytics.track('generated', template);
            }

            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error('AI Content generation error:', error);
            throw error;
        }
    }

    async getGenerationStats(templateId = null) {
        const cacheKey = `ai_stats_${templateId || 'all'}`;
        const cached = this.cache.get(cacheKey);
        if (cached) return cached;

        try {
            const url = templateId 
                ? `${this.endpoint}/stats?template_id=${templateId}`
                : `${this.endpoint}/stats`;

            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to fetch AI stats');
            
            const data = await response.json();
            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error('Failed to get AI generation stats:', error);
            throw error;
        }
    }
}