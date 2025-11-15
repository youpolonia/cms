/**
 * Block Manager - Handles block rendering and reordering
 */
class BlockManager {
    constructor() {
        this.blocks = [];
        this.initDragDrop();
    }

    /**
     * Initialize drag and drop functionality
     */
    initDragDrop() {
        document.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('block')) {
                e.dataTransfer.setData('text/plain', e.target.dataset.blockId);
                e.target.classList.add('dragging');
            }
        });

        document.addEventListener('dragover', (e) => {
            if (e.target.classList.contains('block-container')) {
                e.preventDefault();
                const dragging = document.querySelector('.dragging');
                if (dragging) {
                    const afterElement = this.getDragAfterElement(e.target, e.clientY);
                    if (afterElement) {
                        e.target.insertBefore(dragging, afterElement);
                    } else {
                        e.target.appendChild(dragging);
                    }
                }
            }
        });

        document.addEventListener('dragend', (e) => {
            if (e.target.classList.contains('block')) {
                e.target.classList.remove('dragging');
                this.updateBlockPositions();
            }
        });
    }

    /**
     * Get element after which to place dragged block
     */
    getDragAfterElement(container, y) {
        const blocks = [...container.querySelectorAll('.block:not(.dragging)')];
        
        return blocks.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    /**
     * Update block positions after reordering
     */
    updateBlockPositions() {
        const blocks = document.querySelectorAll('.block');
        this.blocks = Array.from(blocks).map((block, index) => {
            const blockId = block.dataset.blockId;
            const blockData = this.getBlockData(blockId);
            blockData.meta.position = index;
            return blockData;
        });
    }

    /**
     * Get block data by ID
     */
    getBlockData(blockId) {
        // Implementation would fetch from server or local storage
        return this.blocks.find(b => b.meta.id === blockId);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.blockManager = new BlockManager();
});