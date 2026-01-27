/**
 * BlockManager - Core class for managing content blocks
 */
class BlockManager {
  constructor() {
    this.blocks = new Map();
    this.events = new EventTarget();
    this.currentMode = 'preview';
    this.activeBlocks = new Set();
  }

  /**
   * Register a new block type
   * @param {string} type - Block type identifier
   * @param {Object} handler - Block handler with required methods
   */
  registerBlock(type, handler) {
    if (!handler.create || !handler.serialize || !handler.deserialize) {
      throw new Error('Block handler must implement create, serialize and deserialize methods');
    }
    this.blocks.set(type, handler);
    this.dispatchEvent('blockRegistered', { type });
  }

  /**
   * Create a new block instance
   * @param {string} type - Block type identifier
   * @param {HTMLElement} container - DOM element to render into
   * @param {Object} data - Initial block data
   * @returns {Object} - Created block instance
   */
  createBlock(type, container, data = {}) {
    if (!this.blocks.has(type)) {
      throw new Error(`Block type ${type} not registered`);
    }

    const block = this.blocks.get(type).create(container, data);
    this.activeBlocks.add(block);
    this.setupBlockEvents(block, type);
    this.dispatchEvent('blockCreated', { block, type });

    return block;
  }

  /**
   * Setup event listeners for a block
   * @private
   */
  setupBlockEvents(block, type) {
    block.element.addEventListener('click', () => {
      this.dispatchEvent('blockSelected', { block, type });
    });

    if (this.currentMode === 'edit') {
      this.enableEditing(block);
    }
  }

  /**
   * Toggle between edit and preview modes
   * @param {string} mode - 'edit' or 'preview'
   */
  setMode(mode) {
    if (mode !== 'edit' && mode !== 'preview') {
      throw new Error('Mode must be either "edit" or "preview"');
    }

    this.currentMode = mode;
    this.activeBlocks.forEach(block => {
      mode === 'edit' ? this.enableEditing(block) : this.disableEditing(block);
    });
    this.dispatchEvent('modeChanged', { mode });
  }

  /**
   * Enable editing for a block
   * @private
   */
  enableEditing(block) {
    block.element.classList.add('editable');
    block.element.setAttribute('draggable', 'true');
    this.setupDragEvents(block);
  }

  /**
   * Disable editing for a block
   * @private
   */
  disableEditing(block) {
    block.element.classList.remove('editable');
    block.element.setAttribute('draggable', 'false');
  }

  /**
   * Setup drag and drop events for a block
   * @private
   */
  setupDragEvents(block) {
    block.element.addEventListener('dragstart', (e) => {
      e.dataTransfer.setData('text/plain', block.id);
      this.dispatchEvent('dragStart', { block, event: e });
    });

    block.element.addEventListener('dragover', (e) => {
      e.preventDefault();
      this.dispatchEvent('dragOver', { block, event: e });
    });

    block.element.addEventListener('drop', (e) => {
      e.preventDefault();
      const sourceId = e.dataTransfer.getData('text/plain');
      this.dispatchEvent('drop', { sourceId, targetBlock: block, event: e });
    });
  }

  /**
   * Serialize all blocks to JSON
   * @returns {Array} - Array of serialized block data
   */
  serializeAll() {
    return Array.from(this.activeBlocks).map(block => {
      const type = this.getBlockType(block);
      return {
        type,
        data: this.blocks.get(type).serialize(block),
        id: block.id
      };
    });
  }

  /**
   * Deserialize blocks from JSON
   * @param {Array} blocksData - Array of serialized block data
   * @param {HTMLElement} container - DOM element to render into
   */
  deserializeAll(blocksData, container) {
    this.clearAll();
    blocksData.forEach(blockData => {
      this.createBlock(blockData.type, container, blockData.data);
    });
  }

  /**
   * Clear all blocks
   */
  clearAll() {
    this.activeBlocks.forEach(block => {
      block.element.remove();
    });
    this.activeBlocks.clear();
  }

  /**
   * Get block type by instance
   * @private
   */
  getBlockType(block) {
    for (const [type, handler] of this.blocks.entries()) {
      if (handler.isInstance && handler.isInstance(block)) {
        return type;
      }
    }
    return null;
  }

  /**
   * Dispatch custom event
   * @private
   */
  dispatchEvent(name, detail) {
    this.events.dispatchEvent(new CustomEvent(name, { detail }));
  }

  /**
   * Add event listener
   * @param {string} eventName - Event name to listen for
   * @param {Function} callback - Callback function
   */
  on(eventName, callback) {
    this.events.addEventListener(eventName, callback);
  }

  /**
   * Remove event listener
   * @param {string} eventName - Event name to remove
   * @param {Function} callback - Callback function
   */
  off(eventName, callback) {
    this.events.removeEventListener(eventName, callback);
  }
}

// Export as global if not using modules
if (typeof window !== 'undefined') {
  window.BlockManager = BlockManager;
}