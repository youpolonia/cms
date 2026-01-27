class SuggestionPanel {
  constructor(editor) {
    this.editor = editor;
    this.panel = document.createElement('div');
    this.panel.className = 'suggestion-panel hidden';
    this.panel.innerHTML = `
      <div class="panel-header">
        <h3>AI Content Suggestions</h3>
        <button class="close-btn">&times;</button>
      </div>
      <div class="panel-content">
        <div class="loading">Loading suggestions...</div>
        <ul class="suggestions-list"></ul>
      </div>
      <div class="panel-footer">
        <button class="refresh-btn">Refresh Suggestions</button>
      </div>
    `;
    
    this.initEvents();
    document.body.appendChild(this.panel);
  }

  initEvents() {
    this.panel.querySelector('.close-btn').addEventListener('click', () => this.hide());
    this.panel.querySelector('.refresh-btn').addEventListener('click', () => this.fetchSuggestions());
  }

  toggle() {
    this.panel.classList.toggle('hidden');
    if (!this.panel.classList.contains('hidden')) {
      this.fetchSuggestions();
    }
  }

  show() {
    this.panel.classList.remove('hidden');
    this.fetchSuggestions();
  }

  hide() {
    this.panel.classList.add('hidden');
  }

  async fetchSuggestions() {
    const loading = this.panel.querySelector('.loading');
    const list = this.panel.querySelector('.suggestions-list');
    
    loading.style.display = 'block';
    list.innerHTML = '';

    try {
      const response = await fetch('/api/suggestions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          content: this.editor.element.value,
          context: window.location.pathname
        })
      });

      const suggestions = await response.json();
      loading.style.display = 'none';

      suggestions.forEach(suggestion => {
        const li = document.createElement('li');
        li.textContent = suggestion.text;
        li.addEventListener('click', () => {
          this.insertSuggestion(suggestion.text);
        });
        list.appendChild(li);
      });
    } catch (error) {
      loading.textContent = 'Error loading suggestions';
      console.error('Suggestion fetch error:', error);
    }
  }

  insertSuggestion(text) {
    const selectionStart = this.editor.element.selectionStart;
    const selectionEnd = this.editor.element.selectionEnd;
    const currentValue = this.editor.element.value;

    this.editor.element.value = 
      currentValue.substring(0, selectionStart) + 
      text + 
      currentValue.substring(selectionEnd);

    this.editor.element.focus();
    this.editor.element.selectionStart = selectionStart + text.length;
    this.editor.element.selectionEnd = selectionStart + text.length;
  }
}