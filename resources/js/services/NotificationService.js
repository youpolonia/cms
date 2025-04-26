import AnalyticsCacheService from './AnalyticsCacheService';
import TemplateAnalyticsService from './TemplateAnalyticsService';

export default class NotificationService {
    constructor() {
        this.cache = new AnalyticsCacheService();
        this.templateAnalytics = new TemplateAnalyticsService();
        this.endpoint = '/api/notifications';
    }

    async sendTemplateNotification(template, recipients, options = {}) {
        const cacheKey = `notification_${template.id}_${recipients.join('_')}`;
        
        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    template_id: template.id,
                    recipients,
                    options
                })
            });

            if (!response.ok) throw new Error('Notification failed');

            const data = await response.json();

            // Track template usage
            await this.templateAnalytics.track('notified', template, {
                recipient_count: recipients.length,
                notification_type: options.type || 'email'
            });

            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error('Notification error:', error);
            throw error;
        }
    }

    async getNotificationStats(templateId = null) {
        const cacheKey = `notification_stats_${templateId || 'all'}`;
        const cached = this.cache.get(cacheKey);
        if (cached) return cached;

        try {
            const url = templateId 
                ? `${this.endpoint}/stats?template_id=${templateId}`
                : `${this.endpoint}/stats`;

            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to fetch notification stats');
            
            const data = await response.json();
            this.cache.set(cacheKey, data);
            return data;
        } catch (error) {
            console.error('Failed to get notification stats:', error);
            throw error;
        }
    }
}