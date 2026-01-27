/**
 * Page Builder Frontend Application
 * 
 * Handles the drag-and-drop interface and component management
 */
class BuilderApp {
    constructor(options = {}) {
        // Configuration
        this.container = document.querySelector(options.container || '#page-builder');
        this.apiUrl = options.apiUrl || '/api/page-builder';
        this.components = [];
        this.currentPage = null;
        
        // Initialize
        this.initEvents();
        this.loadComponents();
    }

    /**
     * Initialize event listeners
     */
    initEvents() {
        // Component drag start
        this.container.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('builder-component')) {
                e.dataTransfer.setData('text/plain', e.target.dataset.type);
            }
        });

        // Container drop handler
        this.container.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        });

        this.container.addEventListener('drop', (e) => {
            e.preventDefault();
            const componentType = e.dataTransfer.getData('text/plain');
            this.addComponent(componentType, e.clientX, e.clientY);
        });
    }

    /**
     * Load available components from server
     */
    async loadComponents() {
        try {
            const response = await fetch(`${this.apiUrl}/components`);
            this.components = await response.json();
            this.renderComponentPalette();
        } catch (error) {
            console.error('Failed to load components:', error);
        }
    }

    /**
     * Render the component palette
     */
    renderComponentPalette() {
        const palette = document.querySelector('.component-palette');
        if (!palette) return;

        palette.innerHTML = this.components.map(comp => `
            <div class="component-item" draggable="true" data-type="${comp.type}">
                <div class="component-icon">${comp.icon || '📦'}</div>
                <div class="component-name">${comp.name}</div>
            </div>
        `).join('');
    }

    /**
     * Add a new component to the page
     * @param {string} type Component type
     * @param {number} x X position
     * @param {number} y Y position
     */
    async addComponent(type, x, y) {
        const component = this.components.find(c => c.type === type);
        if (!component) return;

        const newComponent = {
            id: `comp_${Date.now()}`,
            type,
            position: { x, y },
            data: {}
        };

        // Add to DOM
        const element = document.createElement('div');
        element.className = 'builder-component';
        element.dataset.id = newComponent.id;
        element.style.left = `${x}px`;
        element.style.top = `${y}px`;
        element.innerHTML = `
            <div class="component-header">
                <span class="component-title">${component.name}</span>
                <button class="component-delete">×</button>
            </div>
            <div class="component-content"></div>
        `;
        this.container.appendChild(element);

        // Save to server
        await this.saveComponent(newComponent);
    }

    /**
     * Save component to server
     * @param {object} component 
     */
    async saveComponent(component) {
        try {
            await fetch(`${this.apiUrl}/components`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(component)
            });
        } catch (error) {
            console.error('Failed to save component:', error);
        }
    }

    /**
     * Load page content
     * @param {string} pageId 
     */
    async loadPage(pageId) {
        try {
            const response = await fetch(`${this.apiUrl}/pages/${pageId}`);
            this.currentPage = await response.json();
            this.renderPage();
        } catch (error) {
            console.error('Failed to load page:', error);
        }
    }

    /**
     * Render the current page
     */
    renderPage() {
        if (!this.currentPage) return;

        this.container.innerHTML = '';
        this.currentPage.components.forEach(comp => {
            this.addComponent(comp.type, comp.position.x, comp.position.y);
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.pageBuilder = new BuilderApp();
});