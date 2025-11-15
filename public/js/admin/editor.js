class EditorToolbar {
  constructor(editor) {
    this.editor = editor;
    this.toolbar = document.createElement('div');
    this.toolbar.className = 'editor-toolbar';
    this.initButtons();
  }

  initButtons() {
    // Standard buttons
    this.addButton('bold', 'B', () => this.editor.execCommand('bold'));
    this.addButton('italic', 'I', () => this.editor.execCommand('italic'));
    
    // AI Suggestion button
    this.addButton('ai-suggest', 'AI', () => this.editor.showSuggestionPanel());
  }

  addButton(id, label, onClick) {
    const button = document.createElement('button');
    button.id = `toolbar-${id}`;
    button.className = 'toolbar-button';
    button.textContent = label;
    button.addEventListener('click', onClick);
    this.toolbar.appendChild(button);
  }

  getElement() {
    return this.toolbar;
  }
}

class Editor {
  constructor(elementId) {
    this.element = document.getElementById(elementId);
    this.toolbar = new EditorToolbar(this);
    this.element.parentNode.insertBefore(this.toolbar.getElement(), this.element);
  }

  execCommand(command) {
    document.execCommand(command, false, null);
  }

  showSuggestionPanel() {
    if (!this.suggestionPanel) {
      this.suggestionPanel = new SuggestionPanel(this);
    }
    this.suggestionPanel.toggle();
  }
}