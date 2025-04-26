export default class PageBuilderService {
    constructor(apiClient, blockSchema) {
        this.apiClient = apiClient;
        this.blockSchema = blockSchema;
        this.blocks = [];
        this.currentDraftId = null;
    }

    async initialize(draftId = null) {
        if (draftId) {
            this.currentDraftId = draftId;
            return this.loadDraft();
        }
        return [];
    }

    async loadDraft() {
        try {
            const { data } = await this.apiClient.get(`/drafts/${this.currentDraftId}`);
            this.blocks = data?.blocks || [];
            return this.blocks;
        } catch (error) {
            console.error('Failed loading draft:', error);
            throw error;
        }
    }

    async saveDraft(notifyCallback) {
        try {
            const { data } = await this.apiClient.post('/drafts', {
                draftId: this.currentDraftId,
                blocks: this.blocks
            });

            if (data.success) {
                this.currentDraftId = data.draftId;
                notifyCallback('success', 'Draft saved');
                return true;
            }
        } catch (error) {
            notifyCallback('error', 'Failed saving draft');
            throw error;
        }
    }

    async publish() {
        if (!this.currentDraftId) return false;
        
        try {
            await this.apiClient.post('/publish', {
                draftId: this.currentDraftId
            });
            return true;
        } catch (error) {
            console.error('Failed publishing:', error);
            throw error;
        }
    }

    updateBlock(index, content) {
        if (index >= 0 && index < this.blocks.length) {
            this.blocks[index] = this.validateBlock({
                ...this.blocks[index],
                content
            });
        }
    }

    validateBlock(block) {
        return this.blockSchema.validateSync(block, { 
            stripUnknown: true 
        });
    }

    addBlock(type) {
        const defaults = {
            html: '<p class="placeholder-text">New HTML content</p>',
            image: { url: '', caption: '' },
            text: 'New text content'
        };

        const newBlock = {
            type,
            content: defaults[type]
        };

        this.blocks.push(
            this.validateBlock(newBlock)
        );
    }

    removeBlock(index) {
        if (index >= 0 && index < this.blocks.length) {
            this.blocks.splice(index, 1);
        } 
    }

    moveBlock(fromIdx, toIdx) {
        if (fromIdx >= 0 && toIdx >= 0 && 
            fromIdx < this.blocks.length && 
            toIdx < this.blocks.length) {
            const block = this.blocks.splice(fromIdx, 1)[0];
            this.blocks.splice(toIdx, 0, block);
        }
    }
}