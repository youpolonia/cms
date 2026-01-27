/**
 * Theme Builder 3.0 - AI Generate JavaScript
 * 
 * Provides AI text generation functionality for modules.
 * Extends TB object with AI generation methods.
 * 
 * @package ThemeBuilder
 * @version 3.0
 */

// AI Generation prompts by type
const TB_AI_PROMPTS = {
    text: 'Generate a professional paragraph of content for a website. Topic: {context}. Make it engaging and informative, 2-3 sentences.',
    heading: 'Generate a compelling headline for a website section. Topic: {context}. Make it attention-grabbing and concise, max 10 words.',
    button: 'Generate a call-to-action button text. Context: {context}. Make it action-oriented, 2-4 words max.',
    quote: 'Generate an inspirational or relevant quote. Topic: {context}. Include attribution if fictional.',
    testimonial: 'Generate a realistic customer testimonial. Product/Service: {context}. Include specific benefits mentioned, 2-3 sentences.',
    cta_title: 'Generate a compelling call-to-action title. Context: {context}. Make it urgent and benefit-focused, max 8 words.',
    cta_subtitle: 'Generate a supporting subtitle for a CTA section. Context: {context}. Explain the value proposition, 1-2 sentences.',
    cta_button: 'Generate a CTA button text. Context: {context}. Action-oriented, 2-4 words.',
    blurb_title: 'Generate a feature/service title. Context: {context}. Clear and descriptive, 3-6 words.',
    blurb_text: 'Generate a feature/service description. Context: {context}. Explain benefits, 2-3 sentences.',
    hero_title: 'Generate a powerful hero section headline. Business/Product: {context}. Make it memorable and impactful, max 10 words.',
    hero_subtitle: 'Generate a hero section subtitle. Context: {context}. Support the main message, 1-2 sentences.',
    toggle_title: 'Generate an FAQ question or accordion title. Topic: {context}. Clear and specific.',
    toggle_content: 'Generate an FAQ answer or accordion content. Question: {context}. Helpful and informative, 2-4 sentences.',
    pricing_features: 'Generate a list of 5-7 pricing plan features. Plan type: {context}. Format as bullet points with checkmarks.',
    bar_counters: 'Generate 4-5 skill/progress bar items with percentages. Context: {context}. Format: Skill Name|percentage (e.g., "JavaScript|85")'
};

// Extend TB object with AI Generation methods
Object.assign(TB, {
    async handleAIGenerate(type, field, sIdx, rIdx, cIdx, mIdx, button) {
        const mod = this.content.sections[sIdx]?.rows[rIdx]?.columns[cIdx]?.modules[mIdx];
        if (!mod) return;

        let context = '';
        if (mod.content) {
            context = mod.content.text || mod.content.title || mod.content.heading || mod.type || 'general website content';
        } else {
            context = mod.type || 'general website content';
        }

        const promptTemplate = TB_AI_PROMPTS[type] || TB_AI_PROMPTS.text;
        const prompt = promptTemplate.replace('{context}', context);

        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Generating...';
        button.disabled = true;

        try {
            const response = await fetch('/admin/api/ai-generate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    prompt: prompt,
                    type: type,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content || ''
                })
            });

            const data = await response.json();

            if (data.success && data.content) {
                if (!mod.content) mod.content = {};
                
                if (type === 'pricing_features') {
                    const features = data.content.split('\n').filter(f => f.trim());
                    mod.content[field] = features;
                } else if (type === 'bar_counters') {
                    const bars = data.content.split('\n').filter(b => b.trim()).map(b => {
                        const parts = b.split('|');
                        return { label: parts[0]?.trim() || 'Skill', percent: parseInt(parts[1]) || 50 };
                    });
                    mod.content.bars = bars;
                } else {
                    mod.content[field] = data.content;
                }

                this.isDirty = true;
                if (this.saveToHistory) this.saveToHistory();
                if (this.renderCanvas) this.renderCanvas();
                this.selectModule(sIdx, rIdx, cIdx, mIdx);
                if (this.showToast) this.showToast('Content generated!', 'success');
            } else {
                if (this.showToast) this.showToast(data.error || 'Generation failed', 'error');
            }
        } catch (err) {
            if (this.showToast) this.showToast('Error: ' + err.message, 'error');
        } finally {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    },

    renderAIGenerateButton(type, field, sIdx, rIdx, cIdx, mIdx, label) {
        label = label || '✨ AI Generate';
        return '<button type="button" class="tb-btn-ai" onclick="TB.handleAIGenerate(\'' + type + '\', \'' + field + '\', ' + sIdx + ',' + rIdx + ',' + cIdx + ',' + mIdx + ', this)">' + label + '</button>';
    }
});
