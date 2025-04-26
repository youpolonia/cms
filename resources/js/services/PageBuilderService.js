export default class PageBuilderService {
    constructor() {
        this.baseUrl = '/api/page-builder';
    }

    async savePage(pageData) {
        try {
            const response = await fetch(`${this.baseUrl}/pages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify(pageData)
            });

            if (!response.ok) throw new Error('Failed to save page');
            return await response.json();
        } catch (error) {
            console.error('Page save error:', error);
            throw error;
        }
    }

    async loadPage(pageId) {
        try {
            const response = await fetch(`${this.baseUrl}/pages/${pageId}`);
            if (!response.ok) throw new Error('Failed to load page');
            return await response.json();
        } catch (error) {
            console.error('Page load error:', error);
            throw error;
        }
    }

    async getTemplates() {
        try {
            const response = await fetch(`${this.baseUrl}/templates`);
            if (!response.ok) throw new Error('Failed to load templates');
            return await response.json();
        } catch (error) {
            console.error('Templates load error:', error);
            throw error;
        }
    }
}