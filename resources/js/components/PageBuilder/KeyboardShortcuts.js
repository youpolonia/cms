export default class KeyboardShortcuts {
  constructor(pageBuilder) {
    this.pageBuilder = pageBuilder;
    this.shortcuts = new Map();
    this.setupDefaultShortcuts();
    this.initialize();
  }

  setupDefaultShortcuts() {
    this.registerShortcut('ctrl+z', () => this.pageBuilder.undo());
    this.registerShortcut('ctrl+shift+z', () => this.pageBuilder.redo());
    this.registerShortcut('ctrl+d', () => this.pageBuilder.duplicateSelected());
    this.registerShortcut('delete', () => this.pageBuilder.deleteSelected());
    this.registerShortcut('ctrl+s', (e) => {
      e.preventDefault();
      this.pageBuilder.save();
    });
  }

  registerShortcut(combo, callback) {
    this.shortcuts.set(combo, callback);
  }

  initialize() {
    document.addEventListener('keydown', (e) => {
      const combo = this.getKeyCombo(e);
      const callback = this.shortcuts.get(combo);
      
      if (callback) {
        callback(e);
      }
    });
  }

  getKeyCombo(e) {
    const parts = [];
    
    if (e.ctrlKey || e.metaKey) parts.push('ctrl');
    if (e.shiftKey) parts.push('shift');
    if (e.altKey) parts.push('alt');
    
    // Handle special keys
    if (e.key === ' ') {
      parts.push('space');
    } else if (e.key.length === 1) {
      parts.push(e.key.toLowerCase());
    } else {
      parts.push(e.key.toLowerCase());
    }
    
    return parts.join('+');
  }

  destroy() {
    document.removeEventListener('keydown', this.handleKeyDown);
  }
}