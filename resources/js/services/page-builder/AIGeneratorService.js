export default class AIGeneratorService {
    constructor(openaiService) {
        this.openaiService = openaiService;
    }

    async generateContent(template, prompt) {
        try {
            const result = await this.openaiService.generate(
                prompt,
                template,
                template === 'html_content' ? 'html' : 'text'
            );
            return {
                content: result.content,
                creditsUsed: result.cost
            };
        } catch (error) {
            console.error('AI Generation Failed:', error);
            throw error;
        }
    }

    getAvailableTemplates() {
        return [
            { value: 'text_content', label: 'Plain Text' },
            { value: 'html_content', label: 'HTML Content' },
            { value: 'seo_optimization', label: 'SEO Text' }
        ];
    }
}