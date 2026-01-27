/**
 * ContentEditor - UI for managing content blocks
 */
class ContentEditor {
  constructor(blockManager, container) {
    this.blockManager = blockManager;
    this.container = container;
    this.currentBlockType = null;
    this.initUI();
    this.setupEventListeners();
  }

  /**
   * Initialize editor UI elements
   */
  initUI() {
    // Create block type selector
    this.typeSelector = document.createElement('select');
    this.typeSelector.className = 'block-type-selector';
    
    // Create mode toggle
    this.modeToggle = document.createElement('div');
    this.modeToggle.className = 'mode-toggle';
    this.modeToggle.innerHTML = `
      <button class="mode-btn preview active" data-mode="preview">Preview</button>
      <button class="mode-btn edit" data-mode="edit">Edit</button>
    `;

    // Create editor controls container
    this.controls = document.createElement('div');
    this.controls.className = 'editor-controls';
    this.controls.appendChild(this.typeSelector);
    this.controls.appendChild(this.modeToggle);

    // Create blocks container
    this.blocksContainer = document.createElement('div');
    this.blocksContainer.className = 'blocks-container';

    // Assemble editor
    this.container.appendChild(this.controls);
    this.container.appendChild(this.blocksContainer);
  }

  /**
   * Setup event listeners
   */
  setupEventListeners() {
    // Mode toggle
    this.modeToggle.addEventListener('click', (e) => {
      if (e.target.classList.contains('mode-btn')) {
        const mode = e.target.dataset.mode;
        this.setMode(mode);
      }
    });

    // Block type selection
    this.typeSelector.addEventListener('change', (e) => {
      this.currentBlockType = e.target.value;
      this.addBlock();
    });

    // BlockManager events
    this.blockManager.on('blockRegistered', (e) => {
      this.updateTypeSelector();
    });

    this.blockManager.on('dragStart', (e) => {
      e.detail.block.element.classList.add('dragging');
    });

    this.blockManager.on('drop', (e) => {
      document.querySelectorAll('.block').forEach(el => {
        el.classList.remove('dragging', 'drag-over');
      });
    });

    this.blockManager.on('dragOver', (e) => {
      e.detail.block.element.classList.add('drag-over');
    });
  }

  /**
   * Update block type selector options
   */
  updateTypeSelector() {
    this.typeSelector.innerHTML = '<option value="">Select block type...</option>';
    this.blockManager.blocks.forEach((_, type) => {
      const option = document.createElement('option');
      option.value = type;
      option.textContent = type;
      this.typeSelector.appendChild(option);
    });
  }

  /**
   * Set editor mode
   * @param {string} mode - 'edit' or 'preview'
   */
  setMode(mode) {
    this.blockManager.setMode(mode);
    document.querySelectorAll('.mode-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.mode === mode);
    });
  }

  /**
   * Add new block of current type
   */
  addBlock() {
    if (!this.currentBlockType) return;
    this.blockManager.createBlock(this.currentBlockType, this.blocksContainer);
  }
}

// Export as global if not using modules
if (typeof window !== 'undefined') {
  window.ContentEditor = ContentEditor;
}