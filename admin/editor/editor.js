// @ts-check
import * as Vue from 'vue';
import AIEditor from './AIEditor.vue';
import LivePreview from './preview.js';

/**
 * Main content editor class with live preview support
 */
class ContentEditor {
    constructor() {
        /** @type {Array} */
        this.blocks = [];
        /** @type {Object|null} */
        this.currentBlock = null;
        /** @type {Vue.App|null} */
        this.vueApp = null;
        /** @type {LivePreview|null} */
        this.preview = null;
        
        this.initEventListeners();
        this.loadBlockTypes();
        this.initVueApp();
        this.initPreview();
    }

    /**
     * Initialize the live preview system
     */
    initPreview() {
        this.preview = new LivePreview(this);
    }

    /**
     * Notify preview system about block changes
     * @param {Object} block - The changed block
     */
    notifyBlockChange(block) {
        if (this.preview) {
            this.preview.updatePreview();
        }
    }

    initEventListeners() {
        document.addEventListener('click', (e) => {
            const target = /** @type {HTMLElement} */ (e.target);
            if (target.classList.contains('add-block')) {
                this.addBlock(target.dataset.type || '');
            }
        });
    }

    async loadBlockTypes() {
        try {
            const response = await fetch('/api/blocks');
            const blockTypes = await response.json();
            this.renderBlockPalette(blockTypes);
        } catch (error) {
            console.error('Failed to load block types:', error);
        }
    }

    renderBlockPalette(blockTypes) {
        const palette = document.querySelector('.block-palette');
        if (!palette) return;

        // Initialize IntersectionObserver for lazy loading
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const block = /** @type {HTMLElement} */ (entry.target);
                    if (block.dataset.label) {
                        block.innerHTML = block.dataset.label;
                        observer.unobserve(block);
                    }
                }
            });
        }, { threshold: 0.1 });

        // Create placeholder buttons with data attributes
        palette.innerHTML = blockTypes.map(type => `
            <button class="add-block"
                    data-type="${type.id}"
                    data-label="${type.label}">
                <!-- Content will be loaded when visible -->
            </button>
        `).join('');

        // Observe all block buttons
        document.querySelectorAll('.add-block').forEach(btn => {
            observer.observe(btn);
        });
    }

    addBlock(type) {
        const newBlock = {
            id: Date.now(),
            type,
            data: {},
            aiMeta: {}
        };
        this.blocks.push(newBlock);
        this.renderBlock(newBlock);
    }

    renderBlock(block) {
        const editor = document.getElementById('editor');
        if (editor) {
            const blockEl = document.createElement('div');
            blockEl.className = 'content-block';
            blockEl.dataset.id = block.id;
            blockEl.innerHTML = `
                <div class="block-content"></div>
                <div class="block-toolbar">
                    <button class="edit-block">Edit</button>
                    <button class="ai-suggest">AI Suggest</button>
                </div>
            `;
            editor.appendChild(blockEl);
            
            // Watch for changes in this block
            this.watchBlockChanges(block, blockEl);
        }
    }

    watchBlockChanges(block, blockEl) {
        const observer = new MutationObserver(() => {
            this.notifyBlockChange(block);
        });
        
        observer.observe(blockEl, {
            childList: true,
            subtree: true,
            characterData: true,
            attributes: true
        });
    }

    initVueApp() {
        const container = document.getElementById('ai-editor-container');
        if (container) {
            this.vueApp = Vue.createApp({
                components: { AIEditor },
                template: '<AIEditor />'
            });
            this.vueApp.mount(container);
        }
    }

    getCurrentContent() {
        return this.currentBlock?.data?.content || '';
    }

    applyAIContent(content) {
        if (this.currentBlock) {
            this.currentBlock.data.content = content;
            this.renderBlock(this.currentBlock);
        }
    }
}

// Initialize editor when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // @ts-ignore
    window.editor = new ContentEditor();
});