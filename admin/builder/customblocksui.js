class CustomBlocksUI {
    constructor(editor) {
        this.editor = editor;
        this.initUI();
    }

    initUI() {
        // Create the "Save as Block" button
        const saveBtn = document.createElement('button');
        saveBtn.className = 'btn btn-primary';
        saveBtn.innerHTML = 'Save as Block';
        saveBtn.addEventListener('click', () => this.saveCurrentAsBlock());

        // Create the blocks dropdown
        this.blocksDropdown = document.createElement('select');
        this.blocksDropdown.className = 'form-control';
        this.blocksDropdown.innerHTML = '<option value="">Select a block</option>';
        this.blocksDropdown.addEventListener('change', (e) => this.insertBlock(e.target.value));

        // Create container
        const container = document.createElement('div');
        container.className = 'custom-blocks-ui';
        container.appendChild(saveBtn);
        container.appendChild(this.blocksDropdown);

        // Add to editor toolbar
        const toolbar = document.querySelector('.editor-toolbar');
        toolbar.appendChild(container);

        // Load available blocks
        this.loadBlocks();
    }

    async loadBlocks() {
        const response = await fetch('/api/blocks');
        const blocks = await response.json();
        
        this.blocksDropdown.innerHTML = '<option value="">Select a block</option>';
        blocks.forEach(block => {
            const option = document.createElement('option');
            option.value = block.name;
            option.textContent = block.metadata.name || block.name;
            this.blocksDropdown.appendChild(option);
        });
    }

    async saveCurrentAsBlock() {
        const blockName = prompt('Enter block name:');
        if (!blockName) return;

        const content = this.editor.getContent();
        const icon = prompt('Enter icon class (optional):');
        const description = prompt('Enter description (optional):');

        const response = await fetch('/api/blocks', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: blockName,
                content: content,
                metadata: {
                    icon: icon,
                    description: description
                }
            })
        });

        if (response.ok) {
            alert('Block saved successfully!');
            this.loadBlocks();
        } else {
            alert('Error saving block');
        }
    }

    async insertBlock(blockName) {
        if (!blockName) return;
        
        const response = await fetch(`/api/blocks/${blockName}`);
        const block = await response.json();
        
        if (block) {
            this.editor.insertContent(block.content);
        }
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CustomBlocksUI;
}